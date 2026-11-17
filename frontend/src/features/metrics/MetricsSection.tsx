import { useEffect, useState } from 'react';
import { DataTable } from '../../components/ui/DataTable';
import { resolveApiError } from '../../services/errors';
import { getMetricsOverview, listStockDetail } from '../../services/metrics';
import type { MetricsOverview, PaginatedMeta, StockBalance } from '../../types';

const EMPTY_META: PaginatedMeta = {
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
};

export function MetricsSection() {
  const [overview, setOverview] = useState<MetricsOverview | null>(null);
  const [detail, setDetail] = useState<StockBalance[]>([]);
  const [meta, setMeta] = useState<PaginatedMeta>(EMPTY_META);
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [detailLoading, setDetailLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [detailError, setDetailError] = useState<string | null>(null);
  const [reloadToken, setReloadToken] = useState(0);

  useEffect(() => {
    let active = true;

    setLoading(true);
    setError(null);

    getMetricsOverview({ days: 30 })
      .then((data) => {
        if (active) {
          setOverview(data);
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
  }, [reloadToken]);

  useEffect(() => {
    let active = true;

    setDetailLoading(true);
    setDetailError(null);

    listStockDetail({ page, per_page: 10 })
      .then((result) => {
        if (active) {
          setDetail(result.data);
          setMeta(result.meta);
        }
      })
      .catch((requestError) => {
        if (active) {
          setDetailError(resolveApiError(requestError).message);
        }
      })
      .finally(() => {
        if (active) {
          setDetailLoading(false);
        }
      });

    return () => {
      active = false;
    };
  }, [page, reloadToken]);

  if (loading) {
    return <p className="loading">Cargando indicadores…</p>;
  }

  if (error || overview === null) {
    return (
      <div className="catalog-section">
        <p className="form-error" role="alert">
          {error ?? 'No se pudieron cargar los indicadores.'}
        </p>
        <button
          type="button"
          className="button-secondary"
          onClick={() => setReloadToken((token) => token + 1)}
        >
          Reintentar
        </button>
      </div>
    );
  }

  return (
    <div className="catalog-section">
      <div className="catalog-section-header">
        <div>
          <h2>Indicadores</h2>
          <p className="hint">
            Datos al {new Date(overview.generated_at).toLocaleString('es-AR')} · periodo de{' '}
            {overview.period_days} días.
          </p>
        </div>
        <button
          type="button"
          className="button-secondary"
          onClick={() => setReloadToken((token) => token + 1)}
        >
          Actualizar
        </button>
      </div>

      <div className="metric-grid">
        <article className="metric-card">
          <span className="metric-label">Total de productos</span>
          <strong className="metric-value">{overview.total_products}</strong>
          <span className="hint">{overview.total_variants} variantes activas</span>
        </article>
        <article className="metric-card">
          <span className="metric-label">Existencias totales</span>
          <strong className="metric-value">{overview.total_stock}</strong>
          <span className="hint">Unidades en la empresa activa</span>
        </article>
        <article className="metric-card">
          <span className="metric-label">Productos agotados</span>
          <strong className="metric-value">{overview.out_of_stock}</strong>
          <span className="hint">Variante + ubicación con existencia 0</span>
        </article>
        <article className="metric-card">
          <span className="metric-label">Stock mínimo</span>
          <strong className="metric-value">{overview.low_stock}</strong>
          <span className="hint">Según reglas activas</span>
        </article>
        <article className="metric-card">
          <span className="metric-label">Valor total</span>
          <strong className="metric-value metric-pending">Pendiente</strong>
          <span className="hint">{overview.valuation_message}</span>
        </article>
        <article className="metric-card">
          <span className="metric-label">Más salidas</span>
          <strong className="metric-value">
            {overview.top_outflows[0]?.quantity ?? '0'}
          </strong>
          <span className="hint">
            {overview.top_outflows[0]
              ? `${overview.top_outflows[0].sku} · ${overview.top_outflows[0].name}`
              : 'Sin salidas en el periodo'}
          </span>
        </article>
      </div>

      <h3>Más salidas del periodo</h3>
      <DataTable
        ariaLabel="Más salidas del periodo"
        columns={[
          { key: 'sku', label: 'Variante' },
          { key: 'name', label: 'Producto' },
          { key: 'quantity', label: 'Cantidad salida' },
        ]}
        rows={overview.top_outflows.map((item) => ({
          id: item.variant_id,
          sku: item.sku,
          name: item.name,
          quantity: item.quantity,
        }))}
        rowKey={(row) => String(row.id)}
        empty="No hay salidas confirmadas en el periodo."
      />

      <h3>Sin movimiento en el periodo</h3>
      <DataTable
        ariaLabel="Existencias sin movimiento"
        columns={[
          { key: 'variant', label: 'Variante' },
          { key: 'location', label: 'Ubicación' },
          { key: 'quantity', label: 'Existencia' },
        ]}
        rows={overview.inactive.map((item) => ({
          id: `${item.variant_id}-${item.location_id}`,
          variant: (
            <>
              {item.sku ?? '—'}
              <br />
              <span className="hint">{item.name ?? ''}</span>
            </>
          ),
          location: item.location_code ?? '—',
          quantity: item.quantity,
        }))}
        rowKey={(row) => String(row.id)}
        empty="No hay existencias sin movimiento."
      />

      <h3>Detalle de stock</h3>
      {detailLoading && <p className="loading">Cargando detalle…</p>}
      {detailError && (
        <p className="form-error" role="alert">
          {detailError}
        </p>
      )}
      {!detailLoading && !detailError && detail.length === 0 && (
        <p className="empty-state">No hay existencias registradas.</p>
      )}
      {!detailLoading && detail.length > 0 && (
        <table className="users-table">
          <thead>
            <tr>
              <th>Producto / variante</th>
              <th>Ubicación</th>
              <th>Cantidad</th>
            </tr>
          </thead>
          <tbody>
            {detail.map((item) => (
              <tr key={item.id}>
                <td>
                  {item.variant?.product?.name ?? '—'}
                  <br />
                  <span className="hint">{item.variant?.sku ?? '—'}</span>
                </td>
                <td>{item.location?.code ?? '—'}</td>
                <td>{item.quantity}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      <nav className="pagination" aria-label="Paginación de detalle de stock">
        <button
          type="button"
          className="button-secondary"
          disabled={meta.current_page <= 1}
          onClick={() => setPage((current) => Math.max(1, current - 1))}
        >
          Anterior
        </button>
        <span>
          Página {meta.current_page} de {meta.last_page} · {meta.total} existencias
        </span>
        <button
          type="button"
          className="button-secondary"
          disabled={meta.current_page >= meta.last_page}
          onClick={() => setPage((current) => current + 1)}
        >
          Siguiente
        </button>
      </nav>
    </div>
  );
}
