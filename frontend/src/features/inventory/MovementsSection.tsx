import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { resolveApiError } from '../../services/errors';
import { Alert } from '../../components/ui/Alert';
import { Button } from '../../components/ui/Button';
import { ReasonDialog } from '../../components/ui/Dialog';
import {
  createAdjustment,
  createEntry,
  createExit,
  createReturn,
  listMovements,
  reverseMovement,
} from '../../services/inventory';
import type {
  AdjustmentInput,
  MovementFilters,
  MovementInput,
  ReturnInput,
} from '../../services/inventory';
import { listAllVariants } from '../../services/catalog';
import { listLocations, listUnits } from '../../services/settings';
import type {
  Location,
  PaginatedMeta,
  ProductVariant,
  StockMovement,
  StockMovementType,
  Unit,
} from '../../types';

const PER_PAGE = 10;

const EMPTY_META: PaginatedMeta = {
  current_page: 1,
  last_page: 1,
  per_page: PER_PAGE,
  total: 0,
};

const MOVEMENT_LABELS: Record<StockMovementType, string> = {
  entry: 'Entrada',
  exit: 'Salida',
  return: 'Devolución',
  adjustment: 'Ajuste',
  transfer_in: 'Entrada por traslado',
  transfer_out: 'Salida por traslado',
  reversal: 'Reversión',
};

interface MovementsSectionProps {
  canMove: boolean;
  canAdjust: boolean;
  canApprove: boolean;
}

export function MovementsSection({ canMove, canAdjust, canApprove }: MovementsSectionProps) {
  const [movements, setMovements] = useState<StockMovement[]>([]);
  const [variants, setVariants] = useState<ProductVariant[]>([]);
  const [locations, setLocations] = useState<Location[]>([]);
  const [units, setUnits] = useState<Unit[]>([]);
  const [meta, setMeta] = useState<PaginatedMeta>(EMPTY_META);
  const [query, setQuery] = useState<MovementFilters>({ page: 1, per_page: PER_PAGE });
  const [reloadToken, setReloadToken] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [actionError, setActionError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [reverseTarget, setReverseTarget] = useState<StockMovement | null>(null);
  const [reversing, setReversing] = useState(false);

  const [typeFilter, setTypeFilter] = useState<StockMovementType | ''>('');
  const [formOpen, setFormOpen] = useState(false);
  const [type, setType] = useState<StockMovementType>('entry');
  const [variantId, setVariantId] = useState<number>(0);
  const [locationId, setLocationId] = useState<number>(0);
  const [unitId, setUnitId] = useState<number | ''>('');
  const [quantity, setQuantity] = useState('1');
  const [reason, setReason] = useState('');
  const [documentReference, setDocumentReference] = useState('');
  const [direction, setDirection] = useState<'in' | 'out'>('out');
  const [originalId, setOriginalId] = useState<number | ''>('');
  const [formError, setFormError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  const typeOptions: Array<{ value: StockMovementType; label: string }> = [];
  if (canMove) {
    typeOptions.push({ value: 'entry', label: 'Entrada' });
    typeOptions.push({ value: 'exit', label: 'Salida' });
    typeOptions.push({ value: 'return', label: 'Devolución' });
  }
  if (canAdjust) {
    typeOptions.push({ value: 'adjustment', label: 'Ajuste' });
  }

  useEffect(() => {
    let active = true;

    Promise.all([
      listAllVariants({ per_page: 100 }),
      listLocations({ per_page: 100 }),
      listUnits({ per_page: 100 }),
    ])
      .then(([variantPage, locationPage, unitPage]) => {
        if (active) {
          setVariants(variantPage.data);
          setLocations(locationPage.data);
          setUnits(unitPage.data);
        }
      })
      .catch(() => {
        if (active) {
          setVariants([]);
          setLocations([]);
          setUnits([]);
        }
      });

    return () => {
      active = false;
    };
  }, []);

  useEffect(() => {
    let active = true;

    const load = async () => {
      setLoading(true);
      setError(null);

      try {
        const result = await listMovements(query);

        if (active) {
          setMovements(result.data);
          setMeta(result.meta);
        }
      } catch (requestError) {
        if (active) {
          setError(resolveApiError(requestError).message);
        }
      } finally {
        if (active) {
          setLoading(false);
        }
      }
    };

    void load();

    return () => {
      active = false;
    };
  }, [query, reloadToken]);

  const handleFilter = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setQuery({ page: 1, per_page: PER_PAGE, type: typeFilter });
  };

  const openForm = () => {
    setType(typeOptions[0]?.value ?? 'entry');
    setVariantId(variants[0]?.id ?? 0);
    setLocationId(locations[0]?.id ?? 0);
    setUnitId('');
    setQuantity('1');
    setReason('');
    setDocumentReference('');
    setDirection('out');
    setOriginalId('');
    setFormError(null);
    setSuccess(null);
    setFormOpen(true);
  };

  const closeForm = () => {
    setFormOpen(false);
    setFormError(null);
  };

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setFormError(null);
    setSuccess(null);

    if (variantId <= 0 || locationId <= 0) {
      setFormError('Seleccione una variante y una ubicación.');
      return;
    }

    const base: MovementInput = {
      variant_id: variantId,
      location_id: locationId,
      unit_id: unitId === '' ? null : unitId,
      quantity,
      reason,
      document_reference: documentReference.trim() === '' ? null : documentReference.trim(),
    };

    setSubmitting(true);

    try {
      if (type === 'entry') {
        await createEntry(base);
      } else if (type === 'exit') {
        await createExit(base);
      } else if (type === 'return') {
        const input: ReturnInput = {
          ...base,
          original_movement_id: originalId === '' ? null : originalId,
        };
        await createReturn(input);
      } else {
        const input: AdjustmentInput = { ...base, direction };
        await createAdjustment(input);
      }

      closeForm();
      setSuccess('Movimiento registrado correctamente.');
      setReloadToken((token) => token + 1);
    } catch (submitError) {
      setFormError(resolveApiError(submitError).message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleReverse = (movement: StockMovement) => {
    setActionError(null);
    setSuccess(null);
    setReverseTarget(movement);
  };

  const confirmReverse = async (reason: string) => {
    if (reverseTarget === null) {
      return;
    }

    setReversing(true);

    try {
      await reverseMovement(reverseTarget.id, reason);
      setSuccess('Movimiento revertido correctamente.');
      setReverseTarget(null);
      setReloadToken((token) => token + 1);
    } catch (reverseError) {
      setActionError(resolveApiError(reverseError).message);
      setReverseTarget(null);
    } finally {
      setReversing(false);
    }
  };

  return (
    <div className="catalog-section">
      <div className="catalog-section-header">
        <div>
          <h2>Movimientos</h2>
          <p className="hint">
            Historial inmutable de entradas, salidas, devoluciones, ajustes y
            reversiones.
          </p>
        </div>
        {typeOptions.length > 0 && (
          <button type="button" className="button-primary" onClick={openForm}>
            Registrar movimiento
          </button>
        )}
      </div>

      {actionError && (
        <p className="form-error" role="alert">
          {actionError}
        </p>
      )}
      {success && (
        <p className="simulator-result" role="status">
          {success}
        </p>
      )}

      <form className="users-filters" onSubmit={handleFilter}>
        <label className="field">
          <span>Tipo</span>
          <select
            name="type"
            value={typeFilter}
            onChange={(event) =>
              setTypeFilter(event.target.value as StockMovementType | '')
            }
          >
            <option value="">Todos</option>
            {Object.entries(MOVEMENT_LABELS).map(([value, label]) => (
              <option key={value} value={value}>
                {label}
              </option>
            ))}
          </select>
        </label>
        <button type="submit" className="button-secondary">
          Filtrar
        </button>
      </form>

      {formOpen && (
        <form className="user-form" onSubmit={handleSubmit}>
          <h2>Registrar movimiento</h2>

          {formError && <Alert variant="error">{formError}</Alert>}

          <div className="user-form-grid">
            <label className="field">
              <span>Tipo</span>
              <select
                name="type"
                value={type}
                onChange={(event) => setType(event.target.value as StockMovementType)}
                required
              >
                {typeOptions.map((option) => (
                  <option key={option.value} value={option.value}>
                    {option.label}
                  </option>
                ))}
              </select>
            </label>

            <label className="field">
              <span>Variante</span>
              <select
                name="variant_id"
                value={variantId}
                onChange={(event) => setVariantId(Number(event.target.value))}
                required
              >
                {variants.map((variant) => (
                  <option key={variant.id} value={variant.id}>
                    {variant.sku} — {variant.name}
                  </option>
                ))}
              </select>
            </label>

            <label className="field">
              <span>Ubicación</span>
              <select
                name="location_id"
                value={locationId}
                onChange={(event) => setLocationId(Number(event.target.value))}
                required
              >
                {locations.map((location) => (
                  <option key={location.id} value={location.id}>
                    {location.code} — {location.name}
                  </option>
                ))}
              </select>
            </label>

            <label className="field">
              <span>Unidad (opcional)</span>
              <select
                name="unit_id"
                value={unitId}
                onChange={(event) =>
                  setUnitId(event.target.value === '' ? '' : Number(event.target.value))
                }
              >
                <option value="">Sin unidad</option>
                {units.map((unit) => (
                  <option key={unit.id} value={unit.id}>
                    {unit.code} — {unit.name}
                  </option>
                ))}
              </select>
            </label>

            <label className="field">
              <span>Cantidad</span>
              <input
                name="quantity"
                type="number"
                step="0.0001"
                min="0.0001"
                value={quantity}
                onChange={(event) => setQuantity(event.target.value)}
                required
              />
            </label>

            <label className="field">
              <span>Motivo</span>
              <input
                name="reason"
                value={reason}
                onChange={(event) => setReason(event.target.value)}
                maxLength={255}
                required
              />
            </label>

            <label className="field">
              <span>Documento de referencia</span>
              <input
                name="document_reference"
                value={documentReference}
                onChange={(event) => setDocumentReference(event.target.value)}
                maxLength={100}
              />
            </label>

            {type === 'adjustment' && (
              <label className="field">
                <span>Sentido del ajuste</span>
                <select
                  name="direction"
                  value={direction}
                  onChange={(event) => setDirection(event.target.value as 'in' | 'out')}
                  required
                >
                  <option value="in">Ingreso (+)</option>
                  <option value="out">Egreso (−)</option>
                </select>
              </label>
            )}

            {type === 'return' && (
              <label className="field">
                <span>Movimiento de origen (opcional)</span>
                <input
                  name="original_movement_id"
                  type="number"
                  min="1"
                  value={originalId}
                  onChange={(event) =>
                    setOriginalId(event.target.value === '' ? '' : Number(event.target.value))
                  }
                />
              </label>
            )}
          </div>

          <div className="user-form-actions">
            <Button type="submit" loading={submitting}>
              {submitting ? 'Registrando…' : 'Confirmar'}
            </Button>
            <Button variant="secondary" onClick={closeForm} disabled={submitting}>
              Cancelar
            </Button>
          </div>
        </form>
      )}

      {loading && <p className="loading">Cargando movimientos…</p>}

      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

      {!loading && !error && movements.length === 0 && (
        <p className="empty-state">No hay movimientos registrados para esta empresa.</p>
      )}

      {!loading && movements.length > 0 && (
        <table className="users-table">
          <thead>
            <tr>
              <th>Tipo</th>
              <th>Fecha</th>
              <th>Usuario</th>
              <th>Motivo</th>
              <th>Líneas</th>
              {canApprove && <th>Acciones</th>}
            </tr>
          </thead>
          <tbody>
            {movements.map((movement) => (
              <tr key={movement.id}>
                <td>
                  {MOVEMENT_LABELS[movement.type]}
                  {movement.reversal_of_movement_id
                    ? ` #${movement.reversal_of_movement_id}`
                    : ''}
                </td>
                <td>
                  {movement.confirmed_at
                    ? new Date(movement.confirmed_at).toLocaleString('es-AR')
                    : '—'}
                </td>
                <td>{movement.user?.name ?? '—'}</td>
                <td>{movement.reason}</td>
                <td>
                  {movement.lines.map((line) => (
                    <div key={line.id}>
                      {line.variant?.sku ?? '—'}{' '}
                      {line.direction === 'in' ? '+' : '−'}
                      {line.quantity}
                    </div>
                  ))}
                </td>
                {canApprove && (
                  <td className="users-actions">
                    {movement.type !== 'reversal' && movement.reversal === null && (
                      <button
                        type="button"
                        className="button-link"
                        onClick={() => {
                          void handleReverse(movement);
                        }}
                      >
                        Revertir
                      </button>
                    )}
                  </td>
                )}
              </tr>
            ))}
          </tbody>
        </table>
      )}

      <nav className="pagination" aria-label="Paginación de movimientos">
        <button
          type="button"
          className="button-secondary"
          disabled={meta.current_page <= 1}
          onClick={() =>
            setQuery((current) => ({ ...current, page: Math.max(1, (current.page ?? 1) - 1) }))
          }
        >
          Anterior
        </button>
        <span>
          Página {meta.current_page} de {meta.last_page} · {meta.total} movimientos
        </span>
        <button
          type="button"
          className="button-secondary"
          disabled={meta.current_page >= meta.last_page}
          onClick={() =>
            setQuery((current) => ({ ...current, page: (current.page ?? 1) + 1 }))
          }
        >
          Siguiente
        </button>
      </nav>

      <ReasonDialog
        open={reverseTarget !== null}
        title={`Revertir movimiento #${reverseTarget?.id ?? ''}`}
        label="Motivo de la reversión"
        confirmLabel="Revertir"
        loading={reversing}
        danger
        onConfirm={(reason) => {
          void confirmReverse(reason);
        }}
        onClose={() => setReverseTarget(null)}
      />
    </div>
  );
}
