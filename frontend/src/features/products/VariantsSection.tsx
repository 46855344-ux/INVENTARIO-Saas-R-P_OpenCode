import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { resolveApiError } from '../../services/errors';
import {
  createVariant,
  listVariants,
  updateVariant,
  updateVariantStatus,
} from '../../services/catalog';
import type { VariantFilters, VariantInput } from '../../services/catalog';
import type { CatalogStatus, PaginatedMeta, Product, ProductVariant } from '../../types';
import { ProductVariantForm } from './ProductVariantForm';
import type { VariantFormValues } from './ProductVariantForm';

const PER_PAGE = 10;

const EMPTY_META: PaginatedMeta = {
  current_page: 1,
  last_page: 1,
  per_page: PER_PAGE,
  total: 0,
};

interface VariantsSectionProps {
  product: Product;
  canManage: boolean;
  onBack: () => void;
}

export function VariantsSection({ product, canManage, onBack }: VariantsSectionProps) {
  const [variants, setVariants] = useState<ProductVariant[]>([]);
  const [meta, setMeta] = useState<PaginatedMeta>(EMPTY_META);
  const [query, setQuery] = useState<VariantFilters>({ page: 1, per_page: PER_PAGE });
  const [reloadToken, setReloadToken] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [actionError, setActionError] = useState<string | null>(null);

  const [searchInput, setSearchInput] = useState('');
  const [statusInput, setStatusInput] = useState<CatalogStatus | ''>('');

  const [formMode, setFormMode] = useState<'create' | 'edit' | null>(null);
  const [editing, setEditing] = useState<ProductVariant | null>(null);
  const [formError, setFormError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    let active = true;

    const load = async () => {
      setLoading(true);
      setError(null);

      try {
        const result = await listVariants(product.id, query);

        if (active) {
          setVariants(result.data);
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
  }, [product.id, query, reloadToken]);

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
    setFormError(null);
    setFormMode('create');
  };

  const openEdit = (variant: ProductVariant) => {
    setEditing(variant);
    setFormError(null);
    setFormMode('edit');
  };

  const closeForm = () => {
    setFormMode(null);
    setEditing(null);
    setFormError(null);
  };

  const handleSubmit = async (values: VariantFormValues) => {
    setSubmitting(true);
    setFormError(null);

    const input: VariantInput = {
      name: values.name,
      sku: values.sku,
      barcode: values.barcode,
      cost: values.cost,
      price: values.price,
    };

    try {
      if (formMode === 'create') {
        await createVariant(product.id, input);
      } else if (editing !== null) {
        await updateVariant(editing.id, input);
      }

      closeForm();
      setReloadToken((token) => token + 1);
    } catch (submitError) {
      setFormError(resolveApiError(submitError).message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleToggleStatus = async (variant: ProductVariant) => {
    setActionError(null);

    try {
      await updateVariantStatus(
        variant.id,
        variant.status === 'active' ? 'inactive' : 'active',
      );
      setReloadToken((token) => token + 1);
    } catch (statusError) {
      setActionError(resolveApiError(statusError).message);
    }
  };

  return (
    <div className="catalog-section">
      <div className="catalog-section-header">
        <div>
          <h2>Variantes de {product.name}</h2>
          <p className="hint">SKU y código de barras únicos por empresa.</p>
        </div>
        <div className="user-form-actions">
          <button type="button" className="button-secondary" onClick={onBack}>
            Volver a productos
          </button>
          {canManage && (
            <button type="button" className="button-primary" onClick={openCreate}>
              Nueva variante
            </button>
          )}
        </div>
      </div>

      {actionError && (
        <p className="form-error" role="alert">
          {actionError}
        </p>
      )}

      <form className="users-filters" onSubmit={handleFilter}>
        <label className="field">
          <span>Buscar</span>
          <input
            name="search"
            value={searchInput}
            onChange={(event) => setSearchInput(event.target.value)}
            placeholder="Nombre, SKU o código"
          />
        </label>

        <label className="field">
          <span>Estado</span>
          <select
            name="status"
            value={statusInput}
            onChange={(event) =>
              setStatusInput(event.target.value as CatalogStatus | '')
            }
          >
            <option value="">Todos</option>
            <option value="active">Activo</option>
            <option value="inactive">Inactivo</option>
          </select>
        </label>

        <button type="submit" className="button-secondary">
          Filtrar
        </button>
      </form>

      {formMode && (
        <ProductVariantForm
          key={`${formMode}-${editing?.id ?? 'new'}`}
          mode={formMode}
          variant={editing}
          submitting={submitting}
          error={formError}
          onSubmit={handleSubmit}
          onCancel={closeForm}
        />
      )}

      {loading && <p className="loading">Cargando variantes…</p>}

      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

      {!loading && !error && variants.length === 0 && (
        <p className="empty-state">No hay variantes para este producto.</p>
      )}

      {!loading && variants.length > 0 && (
        <table className="users-table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>SKU</th>
              <th>Código de barras</th>
              <th>Costo</th>
              <th>Precio</th>
              <th>Estado</th>
              {canManage && <th>Acciones</th>}
            </tr>
          </thead>
          <tbody>
            {variants.map((variant) => (
              <tr key={variant.id}>
                <td>{variant.name}</td>
                <td>{variant.sku}</td>
                <td>{variant.barcode ?? '—'}</td>
                <td>{variant.cost}</td>
                <td>{variant.price}</td>
                <td>
                  <span className={`status-badge status-${variant.status}`}>
                    {variant.status === 'active' ? 'Activo' : 'Inactivo'}
                  </span>
                </td>
                {canManage && (
                  <td className="users-actions">
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => openEdit(variant)}
                    >
                      Editar
                    </button>
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => {
                        void handleToggleStatus(variant);
                      }}
                    >
                      {variant.status === 'active' ? 'Desactivar' : 'Activar'}
                    </button>
                  </td>
                )}
              </tr>
            ))}
          </tbody>
        </table>
      )}

      <nav className="pagination" aria-label="Paginación de variantes">
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
          Página {meta.current_page} de {meta.last_page} · {meta.total} variantes
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
