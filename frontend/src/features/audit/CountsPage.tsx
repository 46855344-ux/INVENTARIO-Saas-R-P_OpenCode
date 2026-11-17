import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { listAllVariants } from '../../services/catalog';
import { Alert } from '../../components/ui/Alert';
import { Button } from '../../components/ui/Button';
import { DataTable } from '../../components/ui/DataTable';
import { ReasonDialog } from '../../components/ui/Dialog';
import { SelectField } from '../../components/ui/SelectField';
import { TextField } from '../../components/ui/TextField';
import { resolveApiError } from '../../services/errors';
import {
  createStockCount,
  listAuditEvents,
  listStockCounts,
  updateStockCountStatus,
} from '../../services/audit';
import { listLocations } from '../../services/settings';
import type {
  AuditLog,
  Location,
  PaginatedMeta,
  ProductVariant,
  StockCount,
} from '../../types';
import { useAuth } from '../auth/AuthContext';

const EMPTY_META: PaginatedMeta = { current_page: 1, last_page: 1, per_page: 10, total: 0 };

let lineId = 0;

interface CountLine {
  key: string;
  variant_id: number;
  counted_quantity: string;
}

function makeLine(variantId = 0): CountLine {
  lineId += 1;
  return { key: `count-line-${lineId}`, variant_id: variantId, counted_quantity: '0' };
}

export function CountsPage() {
  const { hasPermission } = useAuth();
  const canView = hasPermission('counts.view');
  const canManage = hasPermission('counts.manage');
  const canApprove = hasPermission('counts.approve');
  const canAudit = hasPermission('audit.view');
  const [tab, setTab] = useState<'counts' | 'audit'>(canView ? 'counts' : 'audit');

  const [counts, setCounts] = useState<StockCount[]>([]);
  const [meta, setMeta] = useState<PaginatedMeta>(EMPTY_META);
  const [variants, setVariants] = useState<ProductVariant[]>([]);
  const [locations, setLocations] = useState<Location[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [actionError, setActionError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [reloadToken, setReloadToken] = useState(0);
  const [dialogTarget, setDialogTarget] = useState<StockCount | null>(null);
  const [dialogStatus, setDialogStatus] = useState<'reviewing' | 'approved'>('reviewing');
  const [dialogBusy, setDialogBusy] = useState(false);

  const [formOpen, setFormOpen] = useState(false);
  const [locationId, setLocationId] = useState(0);
  const [notes, setNotes] = useState('');
  const [lines, setLines] = useState<CountLine[]>([]);
  const [formError, setFormError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  const [auditLogs, setAuditLogs] = useState<AuditLog[]>([]);
  const [auditMeta, setAuditMeta] = useState<PaginatedMeta>(EMPTY_META);
  const [actionFilter, setActionFilter] = useState('');
  const [auditLoading, setAuditLoading] = useState(false);
  const [auditError, setAuditError] = useState<string | null>(null);

  useEffect(() => {
    setLoading(true);
    setError(null);

    Promise.all([listStockCounts({ page: 1, per_page: 10 }), listAllVariants({ per_page: 100 }), listLocations({ per_page: 100 })])
      .then(([countPage, variantPage, locationPage]) => {
        setCounts(countPage.data);
        setMeta(countPage.meta);
        setVariants(variantPage.data);
        setLocations(locationPage.data);
      })
      .catch((requestError) => setError(resolveApiError(requestError).message))
      .finally(() => setLoading(false));
  }, [reloadToken]);

  useEffect(() => {
    if (tab !== 'audit' || !canAudit) {
      return;
    }

    setAuditLoading(true);
    setAuditError(null);

    listAuditEvents({ page: 1, per_page: 20, action: actionFilter })
      .then((result) => {
        setAuditLogs(result.data);
        setAuditMeta(result.meta);
      })
      .catch((requestError) => setAuditError(resolveApiError(requestError).message))
      .finally(() => setAuditLoading(false));
  }, [tab, actionFilter, reloadToken, canAudit]);

  const openCreate = () => {
    setLocationId(locations[0]?.id ?? 0);
    setNotes('');
    setLines([makeLine(variants[0]?.id ?? 0)]);
    setFormError(null);
    setFormOpen(true);
  };

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setFormError(null);

    if (locationId <= 0 || lines.some((line) => line.variant_id <= 0)) {
      setFormError('Seleccione una ubicación y al menos una variante.');
      return;
    }

    setSubmitting(true);

    try {
      await createStockCount({
        location_id: locationId,
        notes: notes.trim() === '' ? null : notes.trim(),
        items: lines.map((line) => ({
          variant_id: line.variant_id,
          counted_quantity: line.counted_quantity,
        })),
      });
      setFormOpen(false);
      setSuccess('Conteo registrado. El stock no cambió.');
      setReloadToken((token) => token + 1);
    } catch (submitError) {
      setFormError(resolveApiError(submitError).message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleStatus = (count: StockCount, status: 'reviewing' | 'approved') => {
    setActionError(null);
    setSuccess(null);
    setDialogTarget(count);
    setDialogStatus(status);
  };

  const confirmStatus = async (reason: string) => {
    if (dialogTarget === null) {
      return;
    }

    setDialogBusy(true);

    try {
      await updateStockCountStatus(dialogTarget.id, dialogStatus, reason);
      setSuccess(dialogStatus === 'approved' ? 'Conteo aprobado y stock ajustado.' : 'Conteo enviado a revisión.');
      setDialogTarget(null);
      setReloadToken((token) => token + 1);
    } catch (statusError) {
      setActionError(resolveApiError(statusError).message);
      setDialogTarget(null);
    } finally {
      setDialogBusy(false);
    }
  };

  if (!canView && !canAudit) {
    return <section className="home"><p className="lead">No tiene permiso para ver conteos ni auditoría.</p></section>;
  }

  const activeTab = tab === 'counts' && !canView ? 'audit' : tab === 'audit' && !canAudit ? 'counts' : tab;

  return (
    <section className="users-page catalog-page">
      <header className="users-header">
        <div>
          <p className="eyebrow">Control</p>
          <h1>Conteos y auditoría</h1>
          <p className="lead">Diferencias físicas y trazabilidad de operaciones.</p>
        </div>
      </header>

      <nav className="catalog-tabs" aria-label="Secciones de conteos y auditoría">
        {canView && <button type="button" className={activeTab === 'counts' ? 'catalog-tab is-active' : 'catalog-tab'} onClick={() => setTab('counts')}>Conteos</button>}
        {canAudit && <button type="button" className={activeTab === 'audit' ? 'catalog-tab is-active' : 'catalog-tab'} onClick={() => setTab('audit')}>Auditoría</button>}
      </nav>

      {actionError && <Alert variant="error">{actionError}</Alert>}
      {success && <Alert variant="success">{success}</Alert>}

      {activeTab === 'counts' && (
        <div className="catalog-section">
          <div className="catalog-section-header">
            <div>
              <h2>Conteos físicos</h2>
              <p className="hint">El stock teórico se congela al crear el conteo; el ajuste ocurre al aprobar.</p>
            </div>
            {canManage && <button type="button" className="button-primary" onClick={openCreate}>Nuevo conteo</button>}
          </div>

          {formOpen && (
            <form className="user-form" onSubmit={handleSubmit}>
              <h2>Nuevo conteo</h2>
              {formError && <Alert variant="error">{formError}</Alert>}
              <div className="user-form-grid">
                <SelectField
                  label="Ubicación"
                  value={locationId}
                  onChange={(event) => setLocationId(Number(event.target.value))}
                  required
                >
                  {locations.map((location) => <option key={location.id} value={location.id}>{location.code} — {location.name}</option>)}
                </SelectField>
                <TextField
                  label="Notas"
                  value={notes}
                  onChange={(event) => setNotes(event.target.value)}
                  maxLength={255}
                />
              </div>
              <DataTable
                ariaLabel="Líneas del conteo"
                columns={[
                  { key: 'variant', label: 'Variante' },
                  { key: 'quantity', label: 'Cantidad física' },
                  { key: 'actions', label: 'Acciones' },
                ]}
                rows={lines.map((line, index) => ({
                  id: line.key,
                  variant: (
                    <select
                      aria-label={`Variante de la línea ${index + 1}`}
                      value={line.variant_id}
                      onChange={(event) => setLines((current) => current.map((item) => item.key === line.key ? { ...item, variant_id: Number(event.target.value) } : item))}
                    >
                      {variants.map((variant) => <option key={variant.id} value={variant.id}>{variant.sku} — {variant.name}</option>)}
                    </select>
                  ),
                  quantity: (
                    <input
                      aria-label={`Cantidad física de la línea ${index + 1}`}
                      type="number"
                      step="0.0001"
                      min="0"
                      value={line.counted_quantity}
                      onChange={(event) => setLines((current) => current.map((item) => item.key === line.key ? { ...item, counted_quantity: event.target.value } : item))}
                      required
                    />
                  ),
                  actions: (
                    <button
                      type="button"
                      className="button-link"
                      aria-label={`Quitar línea ${index + 1}`}
                      disabled={lines.length === 1}
                      onClick={() => setLines((current) => current.filter((item) => item.key !== line.key))}
                    >
                      Quitar
                    </button>
                  ),
                }))}
                rowKey={(row) => String(row.id)}
              />
              <div className="user-form-actions">
                <Button variant="secondary" onClick={() => setLines((current) => [...current, makeLine(variants[0]?.id ?? 0)])}>Agregar línea</Button>
                <Button type="submit" loading={submitting}>{submitting ? 'Guardando…' : 'Guardar conteo'}</Button>
                <Button variant="secondary" onClick={() => setFormOpen(false)}>Cancelar</Button>
              </div>
            </form>
          )}

          {loading && <p className="loading">Cargando conteos…</p>}
          {error && <p className="form-error" role="alert">{error}</p>}
          {!loading && !error && counts.length === 0 && <p className="empty-state">No hay conteos registrados.</p>}
          {!loading && counts.length > 0 && (
            <table className="users-table">
              <thead><tr><th>Ubicación</th><th>Estado</th><th>Líneas</th><th>Responsable</th><th>Fecha</th><th>Acciones</th></tr></thead>
              <tbody>
                {counts.map((count) => (
                  <tr key={count.id}>
                    <td>{count.location?.code ?? '—'}</td>
                    <td>{count.status === 'open' ? 'Abierto' : count.status === 'reviewing' ? 'En revisión' : 'Aprobado'}</td>
                    <td>{count.items.map((item) => <div key={item.id}>{item.variant?.sku ?? '—'} · teórico {item.theoretical_quantity} / físico {item.counted_quantity} / dif. {item.difference}</div>)}</td>
                    <td>{count.user?.name ?? '—'}</td>
                    <td>{count.counted_at ? new Date(count.counted_at).toLocaleString('es-AR') : '—'}</td>
                    <td className="users-actions">
                      {canManage && count.status === 'open' && <button type="button" className="button-link" onClick={() => { void handleStatus(count, 'reviewing'); }}>Revisar</button>}
                      {canApprove && count.status === 'reviewing' && <button type="button" className="button-link" onClick={() => { void handleStatus(count, 'approved'); }}>Aprobar</button>}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
          <nav className="pagination"><span>Página {meta.current_page} de {meta.last_page} · {meta.total} conteos</span></nav>
        </div>
      )}

      {activeTab === 'audit' && (
        <div className="catalog-section">
          <div className="catalog-section-header"><div><h2>Auditoría</h2><p className="hint">Registro append-only; no se edita desde la aplicación.</p></div></div>
          <div className="users-filters">
            <label className="field"><span>Acción</span>
              <select value={actionFilter} onChange={(event) => setActionFilter(event.target.value)}>
                <option value="">Todas</option>
                <option value="stock_count.created">Conteo creado</option>
                <option value="stock_count.reviewing">Conteo en revisión</option>
                <option value="stock_count.approved">Conteo aprobado</option>
              </select>
            </label>
          </div>
          {auditLoading && <p className="loading">Cargando auditoría…</p>}
          {auditError && <p className="form-error" role="alert">{auditError}</p>}
          {!auditLoading && !auditError && auditLogs.length === 0 && <p className="empty-state">No hay eventos para el filtro.</p>}
          {!auditLoading && auditLogs.length > 0 && (
            <table className="users-table">
              <thead><tr><th>Acción</th><th>Entidad</th><th>Usuario</th><th>Fecha</th><th>Contexto</th></tr></thead>
              <tbody>
                {auditLogs.map((log) => (
                  <tr key={log.id}>
                    <td>{log.action}</td><td>{log.entity_type} #{log.entity_id ?? '—'}</td><td>{log.user?.name ?? '—'}</td>
                    <td>{log.created_at ? new Date(log.created_at).toLocaleString('es-AR') : '—'}</td>
                    <td><code>{log.context ? JSON.stringify(log.context) : '—'}</code></td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
          <nav className="pagination"><span>Página {auditMeta.current_page} de {auditMeta.last_page} · {auditMeta.total} eventos</span></nav>
        </div>
      )}

      <ReasonDialog
        open={dialogTarget !== null}
        title={dialogStatus === 'approved' ? 'Aprobar conteo' : 'Enviar a revisión'}
        label={dialogStatus === 'approved' ? 'Motivo de aprobación' : 'Motivo de revisión'}
        confirmLabel={dialogStatus === 'approved' ? 'Aprobar' : 'Enviar a revisión'}
        loading={dialogBusy}
        danger={dialogStatus === 'approved'}
        onConfirm={(reason) => {
          void confirmStatus(reason);
        }}
        onClose={() => setDialogTarget(null)}
      />
    </section>
  );
}
