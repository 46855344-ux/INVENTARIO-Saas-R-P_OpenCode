import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { resolveApiError } from '../../services/errors';
import type { CatalogFilters } from '../../services/catalog';
import type { CatalogItem, CatalogStatus, PaginatedMeta } from '../../types';
import { CatalogForm } from './CatalogForm';

export interface CatalogResource {
  title: string;
  singular: string;
  list: (filters: CatalogFilters) => Promise<{ data: CatalogItem[]; meta: PaginatedMeta }>;
  create: (name: string) => Promise<CatalogItem>;
  update: (id: number, name: string) => Promise<CatalogItem>;
  changeStatus: (id: number, status: CatalogStatus) => Promise<CatalogItem>;
}

interface CatalogSectionProps {
  resource: CatalogResource;
  canManage: boolean;
}

const PER_PAGE = 10;

const EMPTY_META: PaginatedMeta = {
  current_page: 1,
  last_page: 1,
  per_page: PER_PAGE,
  total: 0,
};

export function CatalogSection({ resource, canManage }: CatalogSectionProps) {
  const [items, setItems] = useState<CatalogItem[]>([]);
  const [meta, setMeta] = useState<PaginatedMeta>(EMPTY_META);
  const [query, setQuery] = useState<CatalogFilters>({ page: 1, per_page: PER_PAGE });
  const [reloadToken, setReloadToken] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [actionError, setActionError] = useState<string | null>(null);

  const [searchInput, setSearchInput] = useState('');
  const [statusInput, setStatusInput] = useState<CatalogStatus | ''>('');

  const [formMode, setFormMode] = useState<'create' | 'edit' | null>(null);
  const [editing, setEditing] = useState<CatalogItem | null>(null);
  const [formError, setFormError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    let active = true;

    const load = async () => {
      setLoading(true);
      setError(null);

      try {
        const result = await resource.list(query);

        if (active) {
          setItems(result.data);
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
  }, [resource, query, reloadToken]);

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

  const openEdit = (item: CatalogItem) => {
    setEditing(item);
    setFormError(null);
    setFormMode('edit');
  };

  const closeForm = () => {
    setFormMode(null);
    setEditing(null);
    setFormError(null);
  };

  const handleSubmit = async (name: string) => {
    setSubmitting(true);
    setFormError(null);

    try {
      if (formMode === 'create') {
        await resource.create(name);
      } else if (editing !== null) {
        await resource.update(editing.id, name);
      }

      closeForm();
      setReloadToken((token) => token + 1);
    } catch (submitError) {
      setFormError(resolveApiError(submitError).message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleToggleStatus = async (item: CatalogItem) => {
    setActionError(null);

    try {
      await resource.changeStatus(item.id, item.status === 'active' ? 'inactive' : 'active');
      setReloadToken((token) => token + 1);
    } catch (statusError) {
      setActionError(resolveApiError(statusError).message);
    }
  };

  return (
    <div className="catalog-section">
      <div className="catalog-section-header">
        <h2>{resource.title}</h2>
        {canManage && (
          <button type="button" className="button-primary" onClick={openCreate}>
            Nueva {resource.singular}
          </button>
        )}
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
            placeholder="Nombre"
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
        <CatalogForm
          key={`${formMode}-${editing?.id ?? 'new'}`}
          mode={formMode}
          item={editing}
          singular={resource.singular}
          submitting={submitting}
          error={formError}
          onSubmit={handleSubmit}
          onCancel={closeForm}
        />
      )}

      {loading && <p className="loading">Cargando {resource.title.toLowerCase()}…</p>}

      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

      {!loading && !error && items.length === 0 && (
        <p className="empty-state">No hay {resource.title.toLowerCase()} para esta empresa.</p>
      )}

      {!loading && items.length > 0 && (
        <table className="users-table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Estado</th>
              {canManage && <th>Acciones</th>}
            </tr>
          </thead>
          <tbody>
            {items.map((item) => (
              <tr key={item.id}>
                <td>{item.name}</td>
                <td>
                  <span className={`status-badge status-${item.status}`}>
                    {item.status === 'active' ? 'Activo' : 'Inactivo'}
                  </span>
                </td>
                {canManage && (
                  <td className="users-actions">
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => openEdit(item)}
                    >
                      Editar
                    </button>
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => {
                        void handleToggleStatus(item);
                      }}
                    >
                      {item.status === 'active' ? 'Desactivar' : 'Activar'}
                    </button>
                  </td>
                )}
              </tr>
            ))}
          </tbody>
        </table>
      )}

      <nav className="pagination" aria-label={`Paginación de ${resource.title.toLowerCase()}`}>
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
          Página {meta.current_page} de {meta.last_page} · {meta.total} registros
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
