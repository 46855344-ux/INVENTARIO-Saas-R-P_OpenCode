import { useState } from 'react';
import { useAuth } from '../auth/AuthContext';
import { Tabs } from '../../components/ui/Tabs';
import { AlertsSection } from './AlertsSection';
import { MetricsSection } from './MetricsSection';

type MetricsTab = 'metrics' | 'alerts';

export function MetricsPage() {
  const { activeCompany, hasPermission } = useAuth();
  const canViewMetrics = hasPermission('metrics.view');
  const canViewAlerts = hasPermission('alerts.view');
  const canManageAlerts = hasPermission('alerts.manage');
  const [tab, setTab] = useState<MetricsTab>(canViewMetrics ? 'metrics' : 'alerts');

  const tabs: MetricsTab[] = [];

  if (canViewMetrics) {
    tabs.push('metrics');
  }

  if (canViewAlerts) {
    tabs.push('alerts');
  }

  if (tabs.length === 0) {
    return (
      <section className="home">
        <p className="lead">No tiene permiso para ver métricas ni alertas de esta empresa.</p>
      </section>
    );
  }

  const activeTab = tabs.includes(tab) ? tab : tabs[0];

  return (
    <section className="users-page catalog-page">
      <header className="users-header">
        <div>
          <p className="eyebrow">Supervisión</p>
          <h1>Métricas y alertas</h1>
          <p className="lead">
            Indicadores operativos de {activeCompany?.name ?? 'la empresa activa'}.
          </p>
        </div>
      </header>

      <Tabs
        items={tabs.map((item) => ({
          id: item,
          label: item === 'metrics' ? 'Indicadores' : 'Alertas',
        }))}
        active={activeTab}
        ariaLabel="Secciones de métricas y alertas"
        onChange={(id) => setTab(id as MetricsTab)}
      />

      {activeTab === 'metrics' && <MetricsSection />}
      {activeTab === 'alerts' && <AlertsSection canManage={canManageAlerts} />}
    </section>
  );
}
