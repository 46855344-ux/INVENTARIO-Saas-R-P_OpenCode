import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { resolveApiError } from '../../services/errors';
import {
  createConversion,
  listConversions,
  listUnits,
  simulateConversion,
  updateConversion,
  updateConversionStatus,
} from '../../services/settings';
import type { ConversionFilters, ConversionInput } from '../../services/settings';
import type {
  CatalogStatus,
  PaginatedMeta,
  SimulationResult,
  Unit,
  UnitConversion,
} from '../../types';
import { ConversionForm } from './ConversionForm';
import type { ConversionFormValues } from './ConversionForm';
import { dimensionLabel } from './constants';

const PER_PAGE = 10;

const EMPTY_META: PaginatedMeta = {
  current_page: 1,
  last_page: 1,
  per_page: PER_PAGE,
  total: 0,
};

const RULE_LABELS: Record<string, string> = {
  direct: 'Factor directo',
  inverse: 'Factor inverso',
  same_unit: 'Misma unidad',
};

interface ConversionsSectionProps {
  canManage: boolean;
}

export function ConversionsSection({ canManage }: ConversionsSectionProps) {
  const [conversions, setConversions] = useState<UnitConversion[]>([]);
  const [units, setUnits] = useState<Unit[]>([]);
  const [meta, setMeta] = useState<PaginatedMeta>(EMPTY_META);
  const [query, setQuery] = useState<ConversionFilters>({ page: 1, per_page: PER_PAGE });
  const [reloadToken, setReloadToken] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [actionError, setActionError] = useState<string | null>(null);

  const [statusInput, setStatusInput] = useState<CatalogStatus | ''>('');

  const [formMode, setFormMode] = useState<'create' | 'edit' | null>(null);
  const [editing, setEditing] = useState<UnitConversion | null>(null);
  const [formError, setFormError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  const [simFrom, setSimFrom] = useState<number>(0);
  const [simTo, setSimTo] = useState<number>(0);
  const [simQuantity, setSimQuantity] = useState('1');
  const [simResult, setSimResult] = useState<SimulationResult | null>(null);
  const [simError, setSimError] = useState<string | null>(null);
  const [simulating, setSimulating] = useState(false);

  useEffect(() => {
    let active = true;

    listUnits({ per_page: 100 })
      .then((result) => {
        if (active) {
          setUnits(result.data);
          setSimFrom((current) => current || result.data[0]?.id || 0);
          setSimTo((current) => current || result.data[1]?.id || result.data[0]?.id || 0);
        }
      })
      .catch(() => {
        if (active) {
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
        const result = await listConversions(query);

        if (active) {
          setConversions(result.data);
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
    setQuery({ page: 1, per_page: PER_PAGE, status: statusInput });
  };

  const openCreate = () => {
    setEditing(null);
    setFormError(null);
    setFormMode('create');
  };

  const openEdit = (conversion: UnitConversion) => {
    setEditing(conversion);
    setFormError(null);
    setFormMode('edit');
  };

  const closeForm = () => {
    setFormMode(null);
    setEditing(null);
    setFormError(null);
  };

  const handleSubmit = async (values: ConversionFormValues) => {
    setSubmitting(true);
    setFormError(null);

    try {
      if (formMode === 'create') {
        const input: ConversionInput = {
          from_unit_id: values.from_unit_id,
          to_unit_id: values.to_unit_id,
          factor: values.factor,
        };
        await createConversion(input);
      } else if (editing !== null) {
        await updateConversion(editing.id, values.factor);
      }

      closeForm();
      setReloadToken((token) => token + 1);
    } catch (submitError) {
      setFormError(resolveApiError(submitError).message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleToggleStatus = async (conversion: UnitConversion) => {
    setActionError(null);

    try {
      await updateConversionStatus(
        conversion.id,
        conversion.status === 'active' ? 'inactive' : 'active',
      );
      setReloadToken((token) => token + 1);
    } catch (statusError) {
      setActionError(resolveApiError(statusError).message);
    }
  };

  const handleSimulate = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setSimulating(true);
    setSimError(null);
    setSimResult(null);

    try {
      const result = await simulateConversion({
        from_unit_id: simFrom,
        to_unit_id: simTo,
        quantity: simQuantity,
      });
      setSimResult(result);
    } catch (simulateError) {
      setSimError(resolveApiError(simulateError).message);
    } finally {
      setSimulating(false);
    }
  };

  return (
    <div className="catalog-section">
      <div className="catalog-section-header">
        <div>
          <h2>Conversiones</h2>
          <p className="hint">
            Solo se convierten unidades de la misma dimensión; se aplica el factor
            inverso cuando existe.
          </p>
        </div>
        {canManage && (
          <button type="button" className="button-primary" onClick={openCreate}>
            Nueva conversión
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
        <ConversionForm
          key={`${formMode}-${editing?.id ?? 'new'}`}
          mode={formMode}
          conversion={editing}
          units={units}
          submitting={submitting}
          error={formError}
          onSubmit={handleSubmit}
          onCancel={closeForm}
        />
      )}

      <form className="user-form" onSubmit={handleSimulate}>
        <h2>Simulador de conversión</h2>

        {simError && (
          <p className="form-error" role="alert">
            {simError}
          </p>
        )}

        <div className="user-form-grid">
          <label className="field">
            <span>Desde</span>
            <select
              name="from_unit_id"
              value={simFrom}
              onChange={(event) => setSimFrom(Number(event.target.value))}
              required
            >
              {units.map((unit) => (
                <option key={unit.id} value={unit.id}>
                  {unit.code} — {unit.name} ({dimensionLabel(unit.dimension)})
                </option>
              ))}
            </select>
          </label>

          <label className="field">
            <span>Hacia</span>
            <select
              name="to_unit_id"
              value={simTo}
              onChange={(event) => setSimTo(Number(event.target.value))}
              required
            >
              {units.map((unit) => (
                <option key={unit.id} value={unit.id}>
                  {unit.code} — {unit.name} ({dimensionLabel(unit.dimension)})
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
              min="0"
              value={simQuantity}
              onChange={(event) => setSimQuantity(event.target.value)}
              required
            />
          </label>
        </div>

        <div className="user-form-actions">
          <button type="submit" className="button-secondary" disabled={simulating}>
            {simulating ? 'Calculando…' : 'Simular'}
          </button>
        </div>

        {simResult && (
          <div className="simulator-result" role="status">
            <strong>
              {simResult.quantity} {simResult.from_unit.code} = {simResult.result}{' '}
              {simResult.to_unit.code}
            </strong>
            <span>
              Factor {simResult.factor} · {RULE_LABELS[simResult.rule] ?? simResult.rule} ·
              redondeo {simResult.rounding} a {simResult.scale} decimales
            </span>
          </div>
        )}
      </form>

      {loading && <p className="loading">Cargando conversiones…</p>}

      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

      {!loading && !error && conversions.length === 0 && (
        <p className="empty-state">No hay conversiones para esta empresa.</p>
      )}

      {!loading && conversions.length > 0 && (
        <table className="users-table">
          <thead>
            <tr>
              <th>Origen</th>
              <th>Destino</th>
              <th>Factor</th>
              <th>Estado</th>
              {canManage && <th>Acciones</th>}
            </tr>
          </thead>
          <tbody>
            {conversions.map((conversion) => (
              <tr key={conversion.id}>
                <td>{conversion.from_unit?.code ?? '—'}</td>
                <td>{conversion.to_unit?.code ?? '—'}</td>
                <td>{conversion.factor}</td>
                <td>
                  <span className={`status-badge status-${conversion.status}`}>
                    {conversion.status === 'active' ? 'Activo' : 'Inactivo'}
                  </span>
                </td>
                {canManage && (
                  <td className="users-actions">
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => openEdit(conversion)}
                    >
                      Editar
                    </button>
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => {
                        void handleToggleStatus(conversion);
                      }}
                    >
                      {conversion.status === 'active' ? 'Desactivar' : 'Activar'}
                    </button>
                  </td>
                )}
              </tr>
            ))}
          </tbody>
        </table>
      )}

      <nav className="pagination" aria-label="Paginación de conversiones">
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
          Página {meta.current_page} de {meta.last_page} · {meta.total} conversiones
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
