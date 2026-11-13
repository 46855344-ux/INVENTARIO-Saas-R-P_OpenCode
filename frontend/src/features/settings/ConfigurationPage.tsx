import { useState } from 'react';
import { useAuth } from '../auth/AuthContext';
import { ConversionsSection } from './ConversionsSection';
import { LocationsSection } from './LocationsSection';
import { UnitsSection } from './UnitsSection';

type SettingsTab = 'units' | 'conversions' | 'locations';

const TABS: Array<{ id: SettingsTab; label: string }> = [
  { id: 'units', label: 'Unidades' },
  { id: 'conversions', label: 'Conversiones' },
  { id: 'locations', label: 'Ubicaciones' },
];

export function ConfigurationPage() {
  const { activeCompany, hasPermission } = useAuth();
  const canView = hasPermission('settings.view');
  const canManage = hasPermission('settings.manage');
  const [tab, setTab] = useState<SettingsTab>('units');

  if (!canView) {
    return (
      <section className="home">
        <p className="lead">No tiene permiso para ver la configuración de esta empresa.</p>
      </section>
    );
  }

  return (
    <section className="users-page catalog-page">
      <header className="users-header">
        <div>
          <p className="eyebrow">Configuración</p>
          <h1>Unidades y ubicaciones</h1>
          <p className="lead">
            Configuración de inventario de {activeCompany?.name ?? 'la empresa activa'}.
          </p>
        </div>
      </header>

      <nav className="catalog-tabs" aria-label="Secciones de configuración">
        {TABS.map((item) => (
          <button
            key={item.id}
            type="button"
            className={tab === item.id ? 'catalog-tab is-active' : 'catalog-tab'}
            onClick={() => setTab(item.id)}
          >
            {item.label}
          </button>
        ))}
      </nav>

      {tab === 'units' && <UnitsSection canManage={canManage} />}
      {tab === 'conversions' && <ConversionsSection canManage={canManage} />}
      {tab === 'locations' && <LocationsSection canManage={canManage} />}
    </section>
  );
}
