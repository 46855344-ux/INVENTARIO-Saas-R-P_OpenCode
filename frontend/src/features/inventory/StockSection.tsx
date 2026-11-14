import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { resolveApiError } from '../../services/errors';
import { listStock } from '../../services/inventory';
import type { StockFilters } from '../../services/inventory';
import type { PaginatedMeta, StockBalance } from '../../types';

const PER_PAGE = 10;

const EMPTY_META: PaginatedMeta = {
  current_page: 1,
  last_page: 1,
  per_page: PER_PAGE,
  total: 0,
};

export function StockSection() {
  const [balances, setBalances] = useState<StockBalance[]>([]);
  const [meta, setMeta] = useState<PaginatedMeta>(EMPTY_META);
  const [query, setQuery] = useState<StockFilters>({ page: 1, per_page: PER_PAGE });
  const [searchInput, setSearchInput] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let active = true;

    const load = async () => {
      setLoading(true);
      setError(null);

      try {
        const result = await listStock(query);

        if (active) {
          setBalances(result.data);
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
  }, [query]);

  const handleFilter = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setQuery({ page: 1, per_page: PER_PAGE, search: searchInput.trim() });
  };

  return (
    <div className="catalog-section">
      <div className="catalog-section-header">
        <div>
          <h2>Existencias</h2>
          <p className="hint">Saldo por variante y ubicación de la empresa activa.</p>
        </div>
      </div>

      <form className="users-filters" onSubmit={handleFilter}>
        <label className="field">
          <span>Buscar</span>
          <input
            name="search"
            value={searchInput}
            onChange={(event) => setSearchInput(event.target.value)}
            placeholder="Producto, SKU o ubicación"
          />
        </label>
        <button type="submit" className="button-secondary">
          Filtrar
        </button>
      </form>

      {loading && <p className="loading">Cargando existencias…</p>}

      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

      {!loading && !error && balances.length === 0 && (
        <p className="empty-state">No hay existencias registradas para esta empresa.</p>
      )}

      {!loading && balances.length > 0 && (
        <table className="users-table">
          <thead>
            <tr>
              <th>Producto / variante</th>
              <th>Ubicación</th>
              <th>Cantidad</th>
              <th>Reservado</th>
              <th>Actualizado</th>
            </tr>
          </thead>
          <tbody>
            {balances.map((balance) => (
              <tr key={balance.id}>
                <td>
                  {balance.variant?.product?.name ?? '—'}
                  <br />
                  <span className="hint">{balance.variant?.sku ?? '—'}</span>
                </td>
                <td>
                  {balance.location?.code ?? '—'}
                  <br />
                  <span className="hint">{balance.location?.name ?? ''}</span>
                </td>
                <td>{balance.quantity}</td>
                <td>{balance.reserved_quantity}</td>
                <td>
                  {balance.updated_at
                    ? new Date(balance.updated_at).toLocaleString('es-AR')
                    : '—'}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      <nav className="pagination" aria-label="Paginación de existencias">
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
          Página {meta.current_page} de {meta.last_page} · {meta.total} existencias
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
