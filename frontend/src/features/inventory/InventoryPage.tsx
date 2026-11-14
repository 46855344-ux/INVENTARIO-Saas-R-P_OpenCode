import { useState } from 'react';
import { useAuth } from '../auth/AuthContext';
import { MovementsSection } from './MovementsSection';
import { StockSection } from './StockSection';

type InventoryTab = 'stock' | 'movements';

export function InventoryPage() {
  const { activeCompany, hasPermission } = useAuth();
  const canView = hasPermission('inventory.view');
  const canMove = hasPermission('inventory.move');
  const canAdjust = hasPermission('inventory.adjust');
  const canApprove = hasPermission('inventory.approve');
  const [tab, setTab] = useState<InventoryTab>('stock');

  if (!canView) {
    return (
      <section className="home">
        <p className="lead">No tiene permiso para ver el inventario de esta empresa.</p>
      </section>
    );
  }

  return (
    <section className="users-page catalog-page">
      <header className="users-header">
        <div>
          <p className="eyebrow">Inventario</p>
          <h1>Inventario</h1>
          <p className="lead">
            Existencias y movimientos de {activeCompany?.name ?? 'la empresa activa'}.
          </p>
        </div>
      </header>

      <nav className="catalog-tabs" aria-label="Secciones de inventario">
        <button
          type="button"
          className={tab === 'stock' ? 'catalog-tab is-active' : 'catalog-tab'}
          onClick={() => setTab('stock')}
        >
          Existencias
        </button>
        <button
          type="button"
          className={tab === 'movements' ? 'catalog-tab is-active' : 'catalog-tab'}
          onClick={() => setTab('movements')}
        >
          Movimientos
        </button>
      </nav>

      {tab === 'stock' && <StockSection />}
      {tab === 'movements' && (
        <MovementsSection
          canMove={canMove}
          canAdjust={canAdjust}
          canApprove={canApprove}
        />
      )}
    </section>
  );
}
