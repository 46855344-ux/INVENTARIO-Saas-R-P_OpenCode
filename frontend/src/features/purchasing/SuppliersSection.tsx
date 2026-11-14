import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { resolveApiError } from '../../services/errors';
import {
  createSupplier,
  listSuppliers,
  updateSupplier,
  updateSupplierStatus,
} from '../../services/purchasing';
import type { SupplierFilters } from '../../services/purchasing';
import type { CatalogStatus, PaginatedMeta, Supplier } from '../../types';

const PER_PAGE = 10;

const EMPTY_META: PaginatedMeta = {
  current_page: 1,
  last_page: 1,
  per_page: PER_PAGE,
  total: 0,
};

interface SuppliersSectionProps {
  canManage: boolean;
}

interface SupplierFormValues {
  name: string;
  tax_id: string;
  email: string;
  phone: string;
}

const EMPTY_FORM: SupplierFormValues = {
  name: '',
  tax_id: '',
  email: '',
  phone: '',
};

export function SuppliersSection({ canManage }: SuppliersSectionProps) {
  const [suppliers, setSuppliers] = useState<Supplier[]>([]);
  const [meta, setMeta] = useState<PaginatedMeta>(EMPTY_META);
  const [query, setQuery] = useState<SupplierFilters>({ page: 1, per_page: PER_PAGE });
  const [reloadToken, setReloadToken] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [actionError, setActionError] = useState<string | null>(null);

  const [searchInput, setSearchInput] = useState('');
  const [statusInput, setStatusInput] = useState<CatalogStatus | ''>('');

  const [formOpen, setFormOpen] = useState(false);
  const [editing, setEditing] = useState<Supplier | null>(null);
  const [values, setValues] = useState<SupplierFormValues>(EMPTY_FORM);
  const [formError, setFormError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    let active = true;

    const load = async () => {
      setLoading(true);
      setError(null);

      try {
        const result = await listSuppliers(query);

        if (active) {
          setSuppliers(result.data);
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
    setValues(EMPTY_FORM);
    setFormError(null);
    setFormOpen(true);
  };

  const openEdit = (supplier: Supplier) => {
    setEditing(supplier);
    setValues({
      name: supplier.name,
      tax_id: supplier.tax_id ?? '',
      email: supplier.email ?? '',
      phone: supplier.phone ?? '',
    });
    setFormError(null);
    setFormOpen(true);
  };

  const closeForm = () => {
    setFormOpen(false);
    setEditing(null);
    setFormError(null);
  };

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setFormError(null);
    setSubmitting(true);

    const input = {
      name: values.name.trim(),
      tax_id: values.tax_id.trim() === '' ? null : values.tax_id.trim(),
      email: values.email.trim() === '' ? null : values.email.trim(),
      phone: values.phone.trim() === '' ? null : values.phone.trim(),
    };

    try {
      if (editing !== null) {
        await updateSupplier(editing.id, input);
      } else {
        await createSupplier(input);
      }

      closeForm();
      setReloadToken((token) => token + 1);
    } catch (submitError) {
      setFormError(resolveApiError(submitError).message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleToggleStatus = async (supplier: Supplier) => {
    setActionError(null);

    try {
      await updateSupplierStatus(
        supplier.id,
        supplier.status === 'active' ? 'inactive' : 'active',
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
          <h2>Proveedores</h2>
          <p className="hint">
            Desactivar un proveedor no elimina sus compras históricas.
          </p>
        </div>
        {canManage && (
          <button type="button" className="button-primary" onClick={openCreate}>
            Nuevo proveedor
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
            placeholder="Nombre, documento o correo"
          />
        </label>
        <label className="field">
          <span>Estado</span>
          <select
            name="status"
            value={statusInput}
            onChange={(event) => setStatusInput(event.target.value as CatalogStatus | '')}
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

      {formOpen && (
        <form className="user-form" onSubmit={handleSubmit}>
          <h2>{editing ? 'Editar proveedor' : 'Nuevo proveedor'}</h2>

          {formError && (
            <p className="form-error" role="alert">
              {formError}
            </p>
          )}

          <div className="user-form-grid">
            <label className="field">
              <span>Nombre</span>
              <input
                name="name"
                value={values.name}
                onChange={(event) => setValues({ ...values, name: event.target.value })}
                maxLength={150}
                required
              />
            </label>
            <label className="field">
              <span>Documento / CUIT</span>
              <input
                name="tax_id"
                value={values.tax_id}
                onChange={(event) => setValues({ ...values, tax_id: event.target.value })}
                maxLength={30}
              />
            </label>
            <label className="field">
              <span>Correo</span>
              <input
                name="email"
                type="email"
                value={values.email}
                onChange={(event) => setValues({ ...values, email: event.target.value })}
                maxLength={150}
              />
            </label>
            <label className="field">
              <span>Teléfono</span>
              <input
                name="phone"
                value={values.phone}
                onChange={(event) => setValues({ ...values, phone: event.target.value })}
                maxLength={50}
              />
            </label>
          </div>

          <div className="user-form-actions">
            <button type="submit" className="button-primary" disabled={submitting}>
              {submitting ? 'Guardando…' : 'Guardar'}
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

      {loading && <p className="loading">Cargando proveedores…</p>}

      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

      {!loading && !error && suppliers.length === 0 && (
        <p className="empty-state">No hay proveedores registrados para esta empresa.</p>
      )}

      {!loading && suppliers.length > 0 && (
        <table className="users-table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Documento</th>
              <th>Contacto</th>
              <th>Estado</th>
              {canManage && <th>Acciones</th>}
            </tr>
          </thead>
          <tbody>
            {suppliers.map((supplier) => (
              <tr key={supplier.id}>
                <td>{supplier.name}</td>
                <td>{supplier.tax_id ?? '—'}</td>
                <td>
                  {supplier.email ?? '—'}
                  <br />
                  <span className="hint">{supplier.phone ?? ''}</span>
                </td>
                <td>{supplier.status === 'active' ? 'Activo' : 'Inactivo'}</td>
                {canManage && (
                  <td className="users-actions">
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => openEdit(supplier)}
                    >
                      Editar
                    </button>
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => {
                        void handleToggleStatus(supplier);
                      }}
                    >
                      {supplier.status === 'active' ? 'Desactivar' : 'Activar'}
                    </button>
                  </td>
                )}
              </tr>
            ))}
          </tbody>
        </table>
      )}

      <nav className="pagination" aria-label="Paginación de proveedores">
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
          Página {meta.current_page} de {meta.last_page} · {meta.total} proveedores
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
