import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { resolveApiError } from '../../services/errors';
import {
  createLocation,
  listLocations,
  updateLocation,
  updateLocationStatus,
} from '../../services/settings';
import type { LocationFilters, LocationInput } from '../../services/settings';
import type {
  CatalogStatus,
  Location,
  LocationType,
  PaginatedMeta,
} from '../../types';
import { LocationForm } from './LocationForm';
import type { LocationFormValues } from './LocationForm';
import { LOCATION_TYPES, locationTypeLabel } from './constants';

const PER_PAGE = 10;

const EMPTY_META: PaginatedMeta = {
  current_page: 1,
  last_page: 1,
  per_page: PER_PAGE,
  total: 0,
};

interface LocationsSectionProps {
  canManage: boolean;
}

export function LocationsSection({ canManage }: LocationsSectionProps) {
  const [locations, setLocations] = useState<Location[]>([]);
  const [options, setOptions] = useState<Location[]>([]);
  const [meta, setMeta] = useState<PaginatedMeta>(EMPTY_META);
  const [query, setQuery] = useState<LocationFilters>({ page: 1, per_page: PER_PAGE });
  const [reloadToken, setReloadToken] = useState(0);
  const [optionsToken, setOptionsToken] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [actionError, setActionError] = useState<string | null>(null);

  const [searchInput, setSearchInput] = useState('');
  const [statusInput, setStatusInput] = useState<CatalogStatus | ''>('');
  const [typeInput, setTypeInput] = useState<LocationType | ''>('');

  const [formMode, setFormMode] = useState<'create' | 'edit' | null>(null);
  const [editing, setEditing] = useState<Location | null>(null);
  const [formError, setFormError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    let active = true;

    listLocations({ per_page: 100 })
      .then((result) => {
        if (active) {
          setOptions(result.data);
        }
      })
      .catch(() => {
        if (active) {
          setOptions([]);
        }
      });

    return () => {
      active = false;
    };
  }, [optionsToken]);

  useEffect(() => {
    let active = true;

    const load = async () => {
      setLoading(true);
      setError(null);

      try {
        const result = await listLocations(query);

        if (active) {
          setLocations(result.data);
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
      type: typeInput,
    });
  };

  const openCreate = () => {
    setEditing(null);
    setFormError(null);
    setFormMode('create');
  };

  const openEdit = (location: Location) => {
    setEditing(location);
    setFormError(null);
    setFormMode('edit');
  };

  const closeForm = () => {
    setFormMode(null);
    setEditing(null);
    setFormError(null);
  };

  const refresh = () => {
    setReloadToken((token) => token + 1);
    setOptionsToken((token) => token + 1);
  };

  const handleSubmit = async (values: LocationFormValues) => {
    setSubmitting(true);
    setFormError(null);

    const input: LocationInput = {
      code: values.code,
      name: values.name,
      type: values.type,
      parent_id: values.parent_id,
    };

    try {
      if (formMode === 'create') {
        await createLocation(input);
      } else if (editing !== null) {
        await updateLocation(editing.id, input);
      }

      closeForm();
      refresh();
    } catch (submitError) {
      setFormError(resolveApiError(submitError).message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleToggleStatus = async (location: Location) => {
    setActionError(null);

    try {
      await updateLocationStatus(
        location.id,
        location.status === 'active' ? 'inactive' : 'active',
      );
      refresh();
    } catch (statusError) {
      setActionError(resolveApiError(statusError).message);
    }
  };

  return (
    <div className="catalog-section">
      <div className="catalog-section-header">
        <div>
          <h2>Ubicaciones</h2>
          <p className="hint">
            Un almacén es una ubicación de tipo «Almacén»; las demás pueden colgar de
            un padre.
          </p>
        </div>
        {canManage && (
          <button type="button" className="button-primary" onClick={openCreate}>
            Nueva ubicación
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
          <span>Tipo</span>
          <select
            name="type"
            value={typeInput}
            onChange={(event) => setTypeInput(event.target.value as LocationType | '')}
          >
            <option value="">Todos</option>
            {LOCATION_TYPES.map((option) => (
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
        <LocationForm
          key={`${formMode}-${editing?.id ?? 'new'}`}
          mode={formMode}
          location={editing}
          options={options}
          submitting={submitting}
          error={formError}
          onSubmit={handleSubmit}
          onCancel={closeForm}
        />
      )}

      {loading && <p className="loading">Cargando ubicaciones…</p>}

      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

      {!loading && !error && locations.length === 0 && (
        <p className="empty-state">No hay ubicaciones para esta empresa.</p>
      )}

      {!loading && locations.length > 0 && (
        <table className="users-table">
          <thead>
            <tr>
              <th>Código</th>
              <th>Nombre</th>
              <th>Tipo</th>
              <th>Padre</th>
              <th>Estado</th>
              {canManage && <th>Acciones</th>}
            </tr>
          </thead>
          <tbody>
            {locations.map((location) => (
              <tr key={location.id}>
                <td>{location.code}</td>
                <td>{location.name}</td>
                <td>{locationTypeLabel(location.type)}</td>
                <td>{location.parent?.code ?? '—'}</td>
                <td>
                  <span className={`status-badge status-${location.status}`}>
                    {location.status === 'active' ? 'Activo' : 'Inactivo'}
                  </span>
                </td>
                {canManage && (
                  <td className="users-actions">
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => openEdit(location)}
                    >
                      Editar
                    </button>
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => {
                        void handleToggleStatus(location);
                      }}
                    >
                      {location.status === 'active' ? 'Desactivar' : 'Activar'}
                    </button>
                  </td>
                )}
              </tr>
            ))}
          </tbody>
        </table>
      )}

      <nav className="pagination" aria-label="Paginación de ubicaciones">
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
          Página {meta.current_page} de {meta.last_page} · {meta.total} ubicaciones
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
