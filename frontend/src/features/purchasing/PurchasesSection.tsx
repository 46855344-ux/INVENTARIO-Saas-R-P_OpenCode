import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { listAllVariants } from '../../services/catalog';
import { resolveApiError } from '../../services/errors';
import {
  createPurchase,
  listPurchases,
  listSuppliers,
  receivePurchase,
  updatePurchase,
} from '../../services/purchasing';
import type { PurchaseFilters } from '../../services/purchasing';
import { listLocations } from '../../services/settings';
import type {
  Location,
  PaginatedMeta,
  ProductVariant,
  Purchase,
  PurchaseStatus,
  Supplier,
} from '../../types';

const PER_PAGE = 10;
const TODAY = new Date().toISOString().slice(0, 10);

const EMPTY_META: PaginatedMeta = {
  current_page: 1,
  last_page: 1,
  per_page: PER_PAGE,
  total: 0,
};

let lineCounter = 0;

interface FormLine {
  key: string;
  variant_id: number;
  location_id: number;
  quantity: string;
  unit_cost: string;
}

function makeLine(variantId = 0, locationId = 0, quantity = '1', unitCost = '0'): FormLine {
  lineCounter += 1;

  return {
    key: `line-${lineCounter}`,
    variant_id: variantId,
    location_id: locationId,
    quantity,
    unit_cost: unitCost,
  };
}

interface PurchasesSectionProps {
  canManage: boolean;
  canReceive: boolean;
}

export function PurchasesSection({ canManage, canReceive }: PurchasesSectionProps) {
  const [purchases, setPurchases] = useState<Purchase[]>([]);
  const [suppliers, setSuppliers] = useState<Supplier[]>([]);
  const [variants, setVariants] = useState<ProductVariant[]>([]);
  const [locations, setLocations] = useState<Location[]>([]);
  const [meta, setMeta] = useState<PaginatedMeta>(EMPTY_META);
  const [query, setQuery] = useState<PurchaseFilters>({ page: 1, per_page: PER_PAGE });
  const [reloadToken, setReloadToken] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [actionError, setActionError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);

  const [searchInput, setSearchInput] = useState('');
  const [statusInput, setStatusInput] = useState<PurchaseStatus | ''>('');

  const [formOpen, setFormOpen] = useState(false);
  const [editing, setEditing] = useState<Purchase | null>(null);
  const [supplierId, setSupplierId] = useState(0);
  const [orderedAt, setOrderedAt] = useState(TODAY);
  const [documentNumber, setDocumentNumber] = useState('');
  const [notes, setNotes] = useState('');
  const [lines, setLines] = useState<FormLine[]>([]);
  const [formError, setFormError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    let active = true;

    Promise.all([
      listSuppliers({ per_page: 100 }),
      listAllVariants({ per_page: 100 }),
      listLocations({ per_page: 100 }),
    ])
      .then(([supplierPage, variantPage, locationPage]) => {
        if (active) {
          setSuppliers(supplierPage.data);
          setVariants(variantPage.data);
          setLocations(locationPage.data);
        }
      })
      .catch(() => {
        if (active) {
          setSuppliers([]);
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
        const result = await listPurchases(query);

        if (active) {
          setPurchases(result.data);
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
      search: searchInput.trim(),
      status: statusInput,
    });
  };

  const openCreate = () => {
    setEditing(null);
    setSupplierId(suppliers[0]?.id ?? 0);
    setOrderedAt(TODAY);
    setDocumentNumber('');
    setNotes('');
    setLines([makeLine(variants[0]?.id ?? 0, locations[0]?.id ?? 0)]);
    setFormError(null);
    setSuccess(null);
    setFormOpen(true);
  };

  const openEdit = (purchase: Purchase) => {
    setEditing(purchase);
    setSupplierId(purchase.supplier?.id ?? 0);
    setOrderedAt(purchase.ordered_at ?? TODAY);
    setDocumentNumber(purchase.document_number ?? '');
    setNotes(purchase.notes ?? '');
    setLines(
      purchase.items.map((item) =>
        makeLine(item.variant_id, item.location_id, item.quantity, item.unit_cost),
      ),
    );
    setFormError(null);
    setSuccess(null);
    setFormOpen(true);
  };

  const closeForm = () => {
    setFormOpen(false);
    setEditing(null);
    setFormError(null);
  };

  const updateLine = (key: string, changes: Partial<FormLine>) => {
    setLines((current) =>
      current.map((line) => (line.key === key ? { ...line, ...changes } : line)),
    );
  };

  const removeLine = (key: string) => {
    setLines((current) => (current.length > 1 ? current.filter((line) => line.key !== key) : current));
  };

  const previewTotal = lines.reduce((total, line) => {
    const quantity = Number(line.quantity);
    const unitCost = Number(line.unit_cost);

    if (!Number.isFinite(quantity) || !Number.isFinite(unitCost)) {
      return total;
    }

    return total + quantity * unitCost;
  }, 0);

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setFormError(null);
    setSuccess(null);

    if (supplierId <= 0) {
      setFormError('Seleccione un proveedor.');
      return;
    }

    if (lines.some((line) => line.variant_id <= 0 || line.location_id <= 0)) {
      setFormError('Cada línea necesita una variante y una ubicación de destino.');
      return;
    }

    setSubmitting(true);

    const input = {
      supplier_id: supplierId,
      ordered_at: orderedAt,
      document_number: documentNumber.trim() === '' ? null : documentNumber.trim(),
      notes: notes.trim() === '' ? null : notes.trim(),
      items: lines.map((line) => ({
        variant_id: line.variant_id,
        location_id: line.location_id,
        quantity: line.quantity,
        unit_cost: line.unit_cost,
      })),
    };

    try {
      if (editing !== null) {
        await updatePurchase(editing.id, input);
        setSuccess('Compra actualizada.');
      } else {
        await createPurchase(input);
        setSuccess('Compra registrada en borrador.');
      }

      closeForm();
      setReloadToken((token) => token + 1);
    } catch (submitError) {
      setFormError(resolveApiError(submitError).message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleReceive = async (purchase: Purchase) => {
    setActionError(null);
    setSuccess(null);

    const confirmed = window.confirm(
      `¿Confirmar la recepción de la compra #${purchase.id}? El stock se actualizará una sola vez.`,
    );

    if (!confirmed) {
      return;
    }

    try {
      await receivePurchase(purchase.id);
      setSuccess('Recepción confirmada. El stock fue actualizado.');
      setReloadToken((token) => token + 1);
    } catch (receiveError) {
      setActionError(resolveApiError(receiveError).message);
    }
  };

  const noOptions = suppliers.length === 0 || variants.length === 0 || locations.length === 0;

  return (
    <div className="catalog-section">
      <div className="catalog-section-header">
        <div>
          <h2>Compras</h2>
          <p className="hint">
            La compra nace en borrador y no altera existencias hasta confirmar la recepción.
          </p>
        </div>
        {canManage && (
          <button
            type="button"
            className="button-primary"
            onClick={openCreate}
            disabled={noOptions}
          >
            Nueva compra
          </button>
        )}
      </div>

      {noOptions && (
        <p className="hint">
          Para registrar compras se necesitan proveedores, variantes y ubicaciones activas.
        </p>
      )}

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
          <span>Buscar</span>
          <input
            name="search"
            value={searchInput}
            onChange={(event) => setSearchInput(event.target.value)}
            placeholder="Documento o proveedor"
          />
        </label>
        <label className="field">
          <span>Estado</span>
          <select
            name="status"
            value={statusInput}
            onChange={(event) => setStatusInput(event.target.value as PurchaseStatus | '')}
          >
            <option value="">Todos</option>
            <option value="draft">Borrador</option>
            <option value="received">Recibida</option>
          </select>
        </label>
        <button type="submit" className="button-secondary">
          Filtrar
        </button>
      </form>

      {formOpen && (
        <form className="user-form" onSubmit={handleSubmit}>
          <h2>{editing ? `Editar compra #${editing.id}` : 'Nueva compra'}</h2>

          {formError && (
            <p className="form-error" role="alert">
              {formError}
            </p>
          )}

          <div className="user-form-grid">
            <label className="field">
              <span>Proveedor</span>
              <select
                name="supplier_id"
                value={supplierId}
                onChange={(event) => setSupplierId(Number(event.target.value))}
                required
              >
                {suppliers.map((supplier) => (
                  <option key={supplier.id} value={supplier.id}>
                    {supplier.name}
                  </option>
                ))}
              </select>
            </label>
            <label className="field">
              <span>Fecha</span>
              <input
                name="ordered_at"
                type="date"
                value={orderedAt}
                onChange={(event) => setOrderedAt(event.target.value)}
                required
              />
            </label>
            <label className="field">
              <span>Documento</span>
              <input
                name="document_number"
                value={documentNumber}
                onChange={(event) => setDocumentNumber(event.target.value)}
                maxLength={50}
              />
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
                <th>Ubicación destino</th>
                <th>Cantidad</th>
                <th>Costo unitario</th>
                <th>Subtotal</th>
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
                    <select
                      name={`location-${line.key}`}
                      value={line.location_id}
                      onChange={(event) =>
                        updateLine(line.key, { location_id: Number(event.target.value) })
                      }
                      required
                    >
                      {locations.map((location) => (
                        <option key={location.id} value={location.id}>
                          {location.code} — {location.name}
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
                    <input
                      name={`unit-cost-${line.key}`}
                      type="number"
                      step="0.0001"
                      min="0"
                      value={line.unit_cost}
                      onChange={(event) =>
                        updateLine(line.key, { unit_cost: event.target.value })
                      }
                      required
                    />
                  </td>
                  <td>
                    {(Number(line.quantity || 0) * Number(line.unit_cost || 0)).toFixed(2)}
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

          <p className="hint">Total estimado: {previewTotal.toFixed(2)}</p>

          <div className="user-form-actions">
            <button
              type="button"
              className="button-secondary"
              onClick={() =>
                setLines((current) => [
                  ...current,
                  makeLine(variants[0]?.id ?? 0, locations[0]?.id ?? 0),
                ])
              }
            >
              Agregar línea
            </button>
            <button type="submit" className="button-primary" disabled={submitting}>
              {submitting ? 'Guardando…' : 'Guardar borrador'}
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

      {loading && <p className="loading">Cargando compras…</p>}

      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

      {!loading && !error && purchases.length === 0 && (
        <p className="empty-state">No hay compras registradas para esta empresa.</p>
      )}

      {!loading && purchases.length > 0 && (
        <table className="users-table">
          <thead>
            <tr>
              <th>Documento</th>
              <th>Proveedor</th>
              <th>Fecha</th>
              <th>Líneas</th>
              <th>Total</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {purchases.map((purchase) => (
              <tr key={purchase.id}>
                <td>
                  {purchase.document_number ?? `Compra #${purchase.id}`}
                  <br />
                  <span className="hint">#{purchase.id}</span>
                </td>
                <td>{purchase.supplier?.name ?? '—'}</td>
                <td>
                  {purchase.ordered_at
                    ? new Date(`${purchase.ordered_at}T00:00:00`).toLocaleDateString('es-AR')
                    : '—'}
                </td>
                <td>
                  {purchase.items.map((item) => (
                    <div key={item.id}>
                      {item.variant?.sku ?? '—'} × {item.quantity}
                    </div>
                  ))}
                </td>
                <td>{purchase.total}</td>
                <td>{purchase.status === 'received' ? 'Recibida' : 'Borrador'}</td>
                <td className="users-actions">
                  {canManage && purchase.status === 'draft' && (
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => openEdit(purchase)}
                    >
                      Editar
                    </button>
                  )}
                  {canReceive && purchase.status === 'draft' && (
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => {
                        void handleReceive(purchase);
                      }}
                    >
                      Recibir
                    </button>
                  )}
                  {purchase.status === 'received' && (
                    <span className="hint">
                      {purchase.received_by?.name ?? 'Recibida'}
                    </span>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      <nav className="pagination" aria-label="Paginación de compras">
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
          Página {meta.current_page} de {meta.last_page} · {meta.total} compras
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
