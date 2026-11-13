import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { resolveApiError } from '../../services/errors';
import {
  createUnit,
  listUnits,
  updateUnit,
  updateUnitStatus,
} from '../../services/settings';
import type { UnitFilters, UnitInput } from '../../services/settings';
import type { CatalogStatus, PaginatedMeta, Unit, UnitDimension } from '../../types';
import { UnitForm } from './UnitForm';
import type { UnitFormValues } from './UnitForm';
import { UNIT_DIMENSIONS, dimensionLabel } from './constants';

const PER_PAGE = 10;

const EMPTY_META: PaginatedMeta = {
  current_page: 1,
  last_page: 1,
  per_page: PER_PAGE,
  total: 0,
};

interface UnitsSectionProps {
  canManage: boolean;
}

export function UnitsSection({ canManage }: UnitsSectionProps) {
  const [units, setUnits] = useState<Unit[]>([]);
  const [meta, setMeta] = useState<PaginatedMeta>(EMPTY_META);
  const [query, setQuery] = useState<UnitFilters>({ page: 1, per_page: PER_PAGE });
  const [reloadToken, setReloadToken] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [actionError, setActionError] = useState<string | null>(null);

  const [searchInput, setSearchInput] = useState('');
  const [statusInput, setStatusInput] = useState<CatalogStatus | ''>('');
  const [dimensionInput, setDimensionInput] = useState<UnitDimension | ''>('');

  const [formMode, setFormMode] = useState<'create' | 'edit' | null>(null);
  const [editing, setEditing] = useState<Unit | null>(null);
  const [formError, setFormError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    let active = true;

    const load = async () => {
      setLoading(true);
      setError(null);

      try {
        const result = await listUnits(query);

        if (active) {
          setUnits(result.data);
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
      dimension: dimensionInput,
    });
  };

  const openCreate = () => {
    setEditing(null);
    setFormError(null);
    setFormMode('create');
  };

  const openEdit = (unit: Unit) => {
    setEditing(unit);
    setFormError(null);
    setFormMode('edit');
  };

  const closeForm = () => {
    setFormMode(null);
    setEditing(null);
    setFormError(null);
  };

  const handleSubmit = async (values: UnitFormValues) => {
    setSubmitting(true);
    setFormError(null);

    const input: UnitInput = {
      code: values.code,
      name: values.name,
      dimension: values.dimension,
      precision: values.precision,
    };

    try {
      if (formMode === 'create') {
        await createUnit(input);
      } else if (editing !== null) {
        await updateUnit(editing.id, input);
      }

      closeForm();
      setReloadToken((token) => token + 1);
    } catch (submitError) {
      setFormError(resolveApiError(submitError).message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleToggleStatus = async (unit: Unit) => {
    setActionError(null);

    try {
      await updateUnitStatus(unit.id, unit.status === 'active' ? 'inactive' : 'active');
      setReloadToken((token) => token + 1);
    } catch (statusError) {
      setActionError(resolveApiError(statusError).message);
    }
  };

  return (
    <div className="catalog-section">
      <div className="catalog-section-header">
        <h2>Unidades</h2>
        {canManage && (
          <button type="button" className="button-primary" onClick={openCreate}>
            Nueva unidad
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
            placeholder="Código o nombre"
          />
        </label>

        <label className="field">
          <span>Dimensión</span>
          <select
            name="dimension"
            value={dimensionInput}
            onChange={(event) =>
              setDimensionInput(event.target.value as UnitDimension | '')
            }
          >
            <option value="">Todas</option>
            {UNIT_DIMENSIONS.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
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
        <UnitForm
          key={`${formMode}-${editing?.id ?? 'new'}`}
          mode={formMode}
          unit={editing}
          submitting={submitting}
          error={formError}
          onSubmit={handleSubmit}
          onCancel={closeForm}
        />
      )}

      {loading && <p className="loading">Cargando unidades…</p>}

      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

      {!loading && !error && units.length === 0 && (
        <p className="empty-state">No hay unidades para esta empresa.</p>
      )}

      {!loading && units.length > 0 && (
        <table className="users-table">
          <thead>
            <tr>
              <th>Código</th>
              <th>Nombre</th>
              <th>Dimensión</th>
              <th>Precisión</th>
              <th>Estado</th>
              {canManage && <th>Acciones</th>}
            </tr>
          </thead>
          <tbody>
            {units.map((unit) => (
              <tr key={unit.id}>
                <td>{unit.code}</td>
                <td>{unit.name}</td>
                <td>{dimensionLabel(unit.dimension)}</td>
                <td>{unit.precision}</td>
                <td>
                  <span className={`status-badge status-${unit.status}`}>
                    {unit.status === 'active' ? 'Activo' : 'Inactivo'}
                  </span>
                </td>
                {canManage && (
                  <td className="users-actions">
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => openEdit(unit)}
                    >
                      Editar
                    </button>
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => {
                        void handleToggleStatus(unit);
                      }}
                    >
                      {unit.status === 'active' ? 'Desactivar' : 'Activar'}
                    </button>
                  </td>
                )}
              </tr>
            ))}
          </tbody>
        </table>
      )}

      <nav className="pagination" aria-label="Paginación de unidades">
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
          Página {meta.current_page} de {meta.last_page} · {meta.total} unidades
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
