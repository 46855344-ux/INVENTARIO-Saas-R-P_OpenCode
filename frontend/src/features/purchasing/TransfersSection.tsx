import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { listAllVariants } from '../../services/catalog';
import { resolveApiError } from '../../services/errors';
import { createTransfer, listTransfers } from '../../services/purchasing';
import type { TransferFilters } from '../../services/purchasing';
import { listLocations } from '../../services/settings';
import type { Location, PaginatedMeta, ProductVariant, Transfer } from '../../types';

const PER_PAGE = 10;

const EMPTY_META: PaginatedMeta = {
  current_page: 1,
  last_page: 1,
  per_page: PER_PAGE,
  total: 0,
};

let transferLineCounter = 0;

interface FormLine {
  key: string;
  variant_id: number;
  quantity: string;
}

function makeLine(variantId = 0, quantity = '1'): FormLine {
  transferLineCounter += 1;

  return {
    key: `transfer-line-${transferLineCounter}`,
    variant_id: variantId,
    quantity,
  };
}

interface TransfersSectionProps {
  canManage: boolean;
}

export function TransfersSection({ canManage }: TransfersSectionProps) {
  const [transfers, setTransfers] = useState<Transfer[]>([]);
  const [variants, setVariants] = useState<ProductVariant[]>([]);
  const [locations, setLocations] = useState<Location[]>([]);
  const [meta, setMeta] = useState<PaginatedMeta>(EMPTY_META);
  const [query, setQuery] = useState<TransferFilters>({ page: 1, per_page: PER_PAGE });
  const [reloadToken, setReloadToken] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);

  const [fromInput, setFromInput] = useState<number | ''>('');
  const [toInput, setToInput] = useState<number | ''>('');

  const [formOpen, setFormOpen] = useState(false);
  const [fromLocationId, setFromLocationId] = useState(0);
  const [toLocationId, setToLocationId] = useState(0);
  const [notes, setNotes] = useState('');
  const [lines, setLines] = useState<FormLine[]>([]);
  const [formError, setFormError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    let active = true;

    Promise.all([
      listAllVariants({ per_page: 100 }),
      listLocations({ per_page: 100 }),
    ])
      .then(([variantPage, locationPage]) => {
        if (active) {
          setVariants(variantPage.data);
          setLocations(locationPage.data);
        }
      })
      .catch(() => {
        if (active) {
          setVariants([]);
          setLocations([]);
        }
      });

    return () => {
      active = false;
    };
  }, [reloadToken]);

  useEffect(() => {
    let active = true;

    const load = async () => {
      setLoading(true);
      setError(null);

      try {
        const result = await listTransfers(query);

        if (active) {
          setTransfers(result.data);
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
    setQuery({
      page: 1,
      per_page: PER_PAGE,
      from_location_id: fromInput,
      to_location_id: toInput,
    });
  };

  const openForm = () => {
    const first = locations[0]?.id ?? 0;
    const second = locations[1]?.id ?? 0;

    setFromLocationId(first);
    setToLocationId(second);
    setNotes('');
    setLines([makeLine(variants[0]?.id ?? 0)]);
    setFormError(null);
    setSuccess(null);
    setFormOpen(true);
  };

  const closeForm = () => {
    setFormOpen(false);
    setFormError(null);
  };

  const updateLine = (key: string, changes: Partial<FormLine>) => {
    setLines((current) =>
      current.map((line) => (line.key === key ? { ...line, ...changes } : line)),
    );
  };

  const removeLine = (key: string) => {
    setLines((current) =>
      current.length > 1 ? current.filter((line) => line.key !== key) : current,
    );
  };

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setFormError(null);
    setSuccess(null);

    if (fromLocationId <= 0 || toLocationId <= 0) {
      setFormError('Seleccione la ubicación de origen y la de destino.');
      return;
    }

    if (fromLocationId === toLocationId) {
      setFormError('El origen y el destino deben ser distintos.');
      return;
    }

    if (lines.some((line) => line.variant_id <= 0)) {
      setFormError('Cada línea necesita una variante.');
      return;
    }

    const confirmed = window.confirm(
      '¿Confirmar el traslado? El stock saldrá del origen y entrará al destino en una sola operación.',
    );

    if (!confirmed) {
      return;
    }

    setSubmitting(true);

    try {
      await createTransfer({
        from_location_id: fromLocationId,
        to_location_id: toLocationId,
        notes: notes.trim() === '' ? null : notes.trim(),
        items: lines.map((line) => ({
          variant_id: line.variant_id,
          quantity: line.quantity,
        })),
      });

      closeForm();
      setSuccess('Traslado confirmado. El stock global no cambió.');
      setReloadToken((token) => token + 1);
    } catch (submitError) {
      setFormError(resolveApiError(submitError).message);
    } finally {
      setSubmitting(false);
    }
  };

  const canTransfer = locations.length >= 2 && variants.length > 0;

  return (
    <div className="catalog-section">
      <div className="catalog-section-header">
        <div>
          <h2>Traslados</h2>
          <p className="hint">
            Movimiento atómico entre ubicaciones: el total de existencias no cambia.
          </p>
        </div>
        {canManage && (
          <button
            type="button"
            className="button-primary"
            onClick={openForm}
            disabled={!canTransfer}
          >
            Nuevo traslado
          </button>
        )}
      </div>

      {!canTransfer && (
        <p className="hint">
          Se necesitan al menos dos ubicaciones y una variante para registrar traslados.
        </p>
      )}

      {success && (
        <p className="simulator-result" role="status">
          {success}
        </p>
      )}

      <form className="users-filters" onSubmit={handleFilter}>
        <label className="field">
          <span>Origen</span>
          <select
            name="from_location_id"
            value={fromInput}
            onChange={(event) =>
              setFromInput(event.target.value === '' ? '' : Number(event.target.value))
            }
          >
            <option value="">Todas</option>
            {locations.map((location) => (
              <option key={location.id} value={location.id}>
                {location.code} — {location.name}
              </option>
            ))}
          </select>
        </label>
        <label className="field">
          <span>Destino</span>
          <select
            name="to_location_id"
            value={toInput}
            onChange={(event) =>
              setToInput(event.target.value === '' ? '' : Number(event.target.value))
            }
          >
            <option value="">Todas</option>
            {locations.map((location) => (
              <option key={location.id} value={location.id}>
                {location.code} — {location.name}
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
          <h2>Nuevo traslado</h2>

          {formError && (
            <p className="form-error" role="alert">
              {formError}
            </p>
          )}

          <div className="user-form-grid">
            <label className="field">
              <span>Origen</span>
              <select
                name="from_location_id"
                value={fromLocationId}
                onChange={(event) => setFromLocationId(Number(event.target.value))}
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
              <span>Destino</span>
              <select
                name="to_location_id"
                value={toLocationId}
                onChange={(event) => setToLocationId(Number(event.target.value))}
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
              <span>Notas</span>
              <input
                name="notes"
                value={notes}
                onChange={(event) => setNotes(event.target.value)}
                maxLength={255}
              />
            </label>
          </div>

          <h3>Detalle</h3>
          <table className="users-table">
            <thead>
              <tr>
                <th>Variante</th>
                <th>Cantidad</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              {lines.map((line) => (
                <tr key={line.key}>
                  <td>
                    <select
                      name={`variant-${line.key}`}
                      value={line.variant_id}
                      onChange={(event) =>
                        updateLine(line.key, { variant_id: Number(event.target.value) })
                      }
                      required
                    >
                      {variants.map((variant) => (
                        <option key={variant.id} value={variant.id}>
                          {variant.sku} — {variant.name}
                        </option>
                      ))}
                    </select>
                  </td>
                  <td>
                    <input
                      name={`quantity-${line.key}`}
                      type="number"
                      step="0.0001"
                      min="0.0001"
                      value={line.quantity}
                      onChange={(event) =>
                        updateLine(line.key, { quantity: event.target.value })
                      }
                      required
                    />
                  </td>
                  <td>
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => removeLine(line.key)}
                      disabled={lines.length === 1}
                    >
                      Quitar
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          <div className="user-form-actions">
            <button
              type="button"
              className="button-secondary"
              onClick={() =>
                setLines((current) => [...current, makeLine(variants[0]?.id ?? 0)])
              }
            >
              Agregar línea
            </button>
            <button type="submit" className="button-primary" disabled={submitting}>
              {submitting ? 'Trasladando…' : 'Confirmar traslado'}
            </button>
            <button
              type="button"
              className="button-secondary"
              onClick={closeForm}
              disabled={submitting}
            >
              Cancelar
            </button>
          </div>
        </form>
      )}

      {loading && <p className="loading">Cargando traslados…</p>}

      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

      {!loading && !error && transfers.length === 0 && (
        <p className="empty-state">No hay traslados registrados para esta empresa.</p>
      )}

      {!loading && transfers.length > 0 && (
        <table className="users-table">
          <thead>
            <tr>
              <th>Origen</th>
              <th>Destino</th>
              <th>Líneas</th>
              <th>Fecha</th>
              <th>Usuario</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            {transfers.map((transfer) => (
              <tr key={transfer.id}>
                <td>
                  {transfer.from_location?.code ?? '—'}
                  <br />
                  <span className="hint">{transfer.from_location?.name ?? ''}</span>
                </td>
                <td>
                  {transfer.to_location?.code ?? '—'}
                  <br />
                  <span className="hint">{transfer.to_location?.name ?? ''}</span>
                </td>
                <td>
                  {transfer.items.map((item) => (
                    <div key={item.id}>
                      {item.variant?.sku ?? '—'} × {item.quantity}
                    </div>
                  ))}
                </td>
                <td>
                  {transfer.confirmed_at
                    ? new Date(transfer.confirmed_at).toLocaleString('es-AR')
                    : '—'}
                </td>
                <td>{transfer.user?.name ?? '—'}</td>
                <td>{transfer.status === 'confirmed' ? 'Confirmado' : transfer.status}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      <nav className="pagination" aria-label="Paginación de traslados">
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
          Página {meta.current_page} de {meta.last_page} · {meta.total} traslados
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
    </div>
  );
}
