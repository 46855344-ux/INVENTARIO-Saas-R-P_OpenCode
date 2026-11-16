import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { listAllVariants } from '../../services/catalog';
import { resolveApiError } from '../../services/errors';
import {
  createAlertRule,
  listAlertRules,
  listAlerts,
  updateAlertRule,
  updateAlertStatus,
} from '../../services/metrics';
import { listLocations } from '../../services/settings';
import type {
  AlertRule,
  AlertStatus,
  AlertType,
  Location,
  PaginatedMeta,
  ProductVariant,
  StockAlert,
} from '../../types';

const PER_PAGE = 10;

const EMPTY_META: PaginatedMeta = {
  current_page: 1,
  last_page: 1,
  per_page: PER_PAGE,
  total: 0,
};

export function AlertsSection({ canManage }: { canManage: boolean }) {
  const [alerts, setAlerts] = useState<StockAlert[]>([]);
  const [rules, setRules] = useState<AlertRule[]>([]);
  const [variants, setVariants] = useState<ProductVariant[]>([]);
  const [locations, setLocations] = useState<Location[]>([]);
  const [meta, setMeta] = useState<PaginatedMeta>(EMPTY_META);
  const [statusInput, setStatusInput] = useState<AlertStatus | ''>('open');
  const [typeInput, setTypeInput] = useState<AlertType | ''>('');
  const [reloadToken, setReloadToken] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [actionError, setActionError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);

  const [formOpen, setFormOpen] = useState(false);
  const [editing, setEditing] = useState<AlertRule | null>(null);
  const [formType, setFormType] = useState<AlertType>('min_stock');
  const [variantId, setVariantId] = useState(0);
  const [locationId, setLocationId] = useState(0);
  const [threshold, setThreshold] = useState('5');
  const [inactivityDays, setInactivityDays] = useState('30');
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

    setLoading(true);
    setError(null);

    Promise.all([
      listAlerts({ page: 1, per_page: PER_PAGE, status: statusInput, type: typeInput }),
      listAlertRules({ page: 1, per_page: 100 }),
    ])
      .then(([alertPage, rulePage]) => {
        if (active) {
          setAlerts(alertPage.data);
          setMeta(alertPage.meta);
          setRules(rulePage.data);
        }
      })
      .catch((requestError) => {
        if (active) {
          setError(resolveApiError(requestError).message);
        }
      })
      .finally(() => {
        if (active) {
          setLoading(false);
        }
      });

    return () => {
      active = false;
    };
  }, [statusInput, typeInput, reloadToken]);

  const openCreate = () => {
    setEditing(null);
    setFormType('min_stock');
    setVariantId(variants[0]?.id ?? 0);
    setLocationId(locations[0]?.id ?? 0);
    setThreshold('5');
    setInactivityDays('30');
    setFormError(null);
    setFormOpen(true);
  };

  const openEdit = (rule: AlertRule) => {
    setEditing(rule);
    setFormType(rule.type);
    setVariantId(rule.variant?.id ?? 0);
    setLocationId(rule.location?.id ?? 0);
    setThreshold(rule.threshold ?? '5');
    setInactivityDays(String(rule.inactivity_days ?? 30));
    setFormError(null);
    setFormOpen(true);
  };

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setFormError(null);

    if (variantId <= 0 || locationId <= 0) {
      setFormError('Seleccione una variante y una ubicación.');
      return;
    }

    setSubmitting(true);

    const input = {
      type: formType,
      variant_id: variantId,
      location_id: locationId,
      threshold: formType === 'min_stock' ? threshold : null,
      inactivity_days: formType === 'inactivity' ? Number(inactivityDays) : null,
    };

    try {
      if (editing) {
        await updateAlertRule(editing.id, input);
        setSuccess('Regla actualizada.');
      } else {
        await createAlertRule(input);
        setSuccess('Regla creada.');
      }

      setFormOpen(false);
      setEditing(null);
      setReloadToken((token) => token + 1);
    } catch (submitError) {
      setFormError(resolveApiError(submitError).message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleStatus = async (alert: StockAlert) => {
    setActionError(null);
    setSuccess(null);

    const nextStatus: AlertStatus = alert.status === 'open' ? 'resolved' : 'open';
    const confirmed = window.confirm(
      nextStatus === 'resolved'
        ? '¿Marcar la alerta como resuelta?'
        : '¿Reabrir la alerta?',
    );

    if (!confirmed) {
      return;
    }

    try {
      await updateAlertStatus(alert.id, nextStatus);
      setSuccess(nextStatus === 'resolved' ? 'Alerta resuelta.' : 'Alerta reabierta.');
      setReloadToken((token) => token + 1);
    } catch (statusError) {
      setActionError(resolveApiError(statusError).message);
    }
  };

  return (
    <div className="catalog-section">
      <div className="catalog-section-header">
        <div>
          <h2>Alertas</h2>
          <p className="hint">
            Reglas por variante y ubicación; las notificaciones son solo dentro de la app.
          </p>
        </div>
        {canManage && (
          <button type="button" className="button-primary" onClick={openCreate}>
            Nueva regla
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

      <div className="users-filters">
        <label className="field">
          <span>Estado</span>
          <select
            value={statusInput}
            onChange={(event) => setStatusInput(event.target.value as AlertStatus | '')}
          >
            <option value="">Todos</option>
            <option value="open">Abiertas</option>
            <option value="resolved">Resueltas</option>
          </select>
        </label>
        <label className="field">
          <span>Tipo</span>
          <select
            value={typeInput}
            onChange={(event) => setTypeInput(event.target.value as AlertType | '')}
          >
            <option value="">Todos</option>
            <option value="min_stock">Stock mínimo</option>
            <option value="inactivity">Inactividad</option>
          </select>
        </label>
      </div>

      {formOpen && (
        <form className="user-form" onSubmit={handleSubmit}>
          <h2>{editing ? 'Editar regla' : 'Nueva regla'}</h2>

          {formError && (
            <p className="form-error" role="alert">
              {formError}
            </p>
          )}

          <div className="user-form-grid">
            <label className="field">
              <span>Tipo</span>
              <select
                value={formType}
                onChange={(event) => setFormType(event.target.value as AlertType)}
              >
                <option value="min_stock">Stock mínimo</option>
                <option value="inactivity">Inactividad</option>
              </select>
            </label>
            <label className="field">
              <span>Variante</span>
              <select
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
            {formType === 'min_stock' ? (
              <label className="field">
                <span>Umbral mínimo</span>
                <input
                  type="number"
                  step="0.0001"
                  min="0"
                  value={threshold}
                  onChange={(event) => setThreshold(event.target.value)}
                  required
                />
              </label>
            ) : (
              <label className="field">
                <span>Días sin movimiento</span>
                <input
                  type="number"
                  min="1"
                  max="365"
                  value={inactivityDays}
                  onChange={(event) => setInactivityDays(event.target.value)}
                  required
                />
              </label>
            )}
          </div>

          <div className="user-form-actions">
            <button type="submit" className="button-primary" disabled={submitting}>
              {submitting ? 'Guardando…' : 'Guardar regla'}
            </button>
            <button
              type="button"
              className="button-secondary"
              onClick={() => setFormOpen(false)}
              disabled={submitting}
            >
              Cancelar
            </button>
          </div>
        </form>
      )}

      {loading && <p className="loading">Cargando alertas…</p>}
      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

      {!loading && !error && alerts.length === 0 && (
        <p className="empty-state">No hay alertas para los filtros seleccionados.</p>
      )}

      {!loading && alerts.length > 0 && (
        <table className="users-table">
          <thead>
            <tr>
              <th>Tipo</th>
              <th>Mensaje</th>
              <th>Variante / ubicación</th>
              <th>Detectada</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {alerts.map((alert) => (
              <tr key={alert.id}>
                <td>{alert.type === 'min_stock' ? 'Stock mínimo' : 'Inactividad'}</td>
                <td>{alert.message}</td>
                <td>
                  {alert.variant?.sku ?? '—'}
                  <br />
                  <span className="hint">{alert.location?.code ?? ''}</span>
                </td>
                <td>
                  {alert.detected_at
                    ? new Date(alert.detected_at).toLocaleString('es-AR')
                    : '—'}
                </td>
                <td>{alert.status === 'open' ? 'Abierta' : 'Resuelta'}</td>
                <td className="users-actions">
                  {canManage ? (
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => {
                        void handleStatus(alert);
                      }}
                    >
                      {alert.status === 'open' ? 'Resolver' : 'Reabrir'}
                    </button>
                  ) : (
                    <span className="hint">Solo lectura</span>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      <h3>Reglas configuradas</h3>
      {rules.length === 0 ? (
        <p className="empty-state">No hay reglas configuradas.</p>
      ) : (
        <table className="users-table">
          <thead>
            <tr>
              <th>Tipo</th>
              <th>Variante</th>
              <th>Ubicación</th>
              <th>Condición</th>
              {canManage && <th>Acciones</th>}
            </tr>
          </thead>
          <tbody>
            {rules.map((rule) => (
              <tr key={rule.id}>
                <td>{rule.type === 'min_stock' ? 'Stock mínimo' : 'Inactividad'}</td>
                <td>{rule.variant?.sku ?? '—'}</td>
                <td>{rule.location?.code ?? '—'}</td>
                <td>
                  {rule.type === 'min_stock'
                    ? `≤ ${rule.threshold}`
                    : `${rule.inactivity_days} días`}
                </td>
                {canManage && (
                  <td className="users-actions">
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => openEdit(rule)}
                    >
                      Editar
                    </button>
                  </td>
                )}
              </tr>
            ))}
          </tbody>
        </table>
      )}

      <nav className="pagination" aria-label="Paginación de alertas">
        <span>
          Página {meta.current_page} de {meta.last_page} · {meta.total} alertas
        </span>
      </nav>
    </div>
  );
}
