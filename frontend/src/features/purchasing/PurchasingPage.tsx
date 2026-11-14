import { useState } from 'react';
import { useAuth } from '../auth/AuthContext';
import { PurchasesSection } from './PurchasesSection';
import { SuppliersSection } from './SuppliersSection';
import { TransfersSection } from './TransfersSection';

type PurchasingTab = 'suppliers' | 'purchases' | 'transfers';

const TAB_LABELS: Record<PurchasingTab, string> = {
  suppliers: 'Proveedores',
  purchases: 'Compras',
  transfers: 'Traslados',
};

export function PurchasingPage() {
  const { activeCompany, hasPermission } = useAuth();
  const canViewPurchases = hasPermission('purchases.view');
  const canManagePurchases = hasPermission('purchases.manage');
  const canReceivePurchases = hasPermission('purchases.receive');
  const canViewTransfers = hasPermission('transfers.view');
  const canManageTransfers = hasPermission('transfers.manage');
  const [tab, setTab] = useState<PurchasingTab>('suppliers');

  const tabs: PurchasingTab[] = [];

  if (canViewPurchases) {
    tabs.push('suppliers', 'purchases');
  }

  if (canViewTransfers) {
    tabs.push('transfers');
  }

  if (tabs.length === 0) {
    return (
      <section className="home">
        <p className="lead">No tiene permiso para ver compras ni traslados de esta empresa.</p>
      </section>
    );
  }

  const activeTab = tabs.includes(tab) ? tab : tabs[0];

  return (
    <section className="users-page catalog-page">
      <header className="users-header">
        <div>
          <p className="eyebrow">Abastecimiento</p>
          <h1>Compras y traslados</h1>
          <p className="lead">
            Proveedores, recepciones y movimientos internos de{' '}
            {activeCompany?.name ?? 'la empresa activa'}.
          </p>
        </div>
      </header>

      <nav className="catalog-tabs" aria-label="Secciones de compras y traslados">
        {tabs.map((item) => (
          <button
            key={item}
            type="button"
            className={activeTab === item ? 'catalog-tab is-active' : 'catalog-tab'}
            onClick={() => setTab(item)}
          >
            {TAB_LABELS[item]}
          </button>
        ))}
      </nav>

      {activeTab === 'suppliers' && <SuppliersSection canManage={canManagePurchases} />}
      {activeTab === 'purchases' && (
        <PurchasesSection
          canManage={canManagePurchases}
          canReceive={canReceivePurchases}
        />
      )}
      {activeTab === 'transfers' && <TransfersSection canManage={canManageTransfers} />}
    </section>
  );
}
