import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from '../features/auth/AuthContext';
import { Can } from '../features/auth/Can';

const linkClass = ({ isActive }: { isActive: boolean }) =>
  isActive ? 'app-nav-link is-active' : 'app-nav-link';

export function AppLayout() {
  const { user, companies, activeCompany, setActiveCompany, logout, hasPermission } =
    useAuth();
  const navigate = useNavigate();
  const canViewPurchasing =
    hasPermission('purchases.view') || hasPermission('transfers.view');

  const handleLogout = async () => {
    await logout();
    navigate('/login', { replace: true });
  };

  return (
    <div className="app-shell">
      <a className="skip-link" href="#main-content">
        Saltar al contenido
      </a>
      <header className="app-header">
        <Link to="/" className="app-brand">
          SAAS Inversiones R&amp;P
        </Link>
        <nav aria-label="Navegación principal" className="app-nav">
          <NavLink to="/" end className={linkClass}>
            Inicio
          </NavLink>
          <Can permission="catalog.view">
            <NavLink to="/catalog" className={linkClass}>
              Catálogo
            </NavLink>
          </Can>
          <Can permission="settings.view">
            <NavLink to="/settings" className={linkClass}>
              Configuración
            </NavLink>
          </Can>
          <Can permission="inventory.view">
            <NavLink to="/inventory" className={linkClass}>
              Inventario
            </NavLink>
          </Can>
          {canViewPurchasing && (
            <NavLink to="/purchasing" className={linkClass}>
              Compras
            </NavLink>
          )}
          {(hasPermission('metrics.view') || hasPermission('alerts.view')) && (
            <NavLink to="/metrics" className={linkClass}>
              Métricas
            </NavLink>
          )}
          {(hasPermission('counts.view') || hasPermission('audit.view')) && (
            <NavLink to="/counts" className={linkClass}>
              Conteos
            </NavLink>
          )}
          {hasPermission('assistant.use') && (
            <NavLink to="/assistant" className={linkClass}>
              Asistente
            </NavLink>
          )}
          <Can permission="identity.users.view">
            <NavLink to="/users" className={linkClass}>
              Usuarios
            </NavLink>
          </Can>
          <NavLink to="/account" className={linkClass}>
            Mi cuenta
          </NavLink>
          {companies.length > 1 && (
            <label className="company-picker">
              <span>Empresa</span>
              <select
                aria-label="Empresa activa"
                value={activeCompany?.id ?? ''}
                onChange={(event) => setActiveCompany(Number(event.target.value))}
              >
                {companies.map((company) => (
                  <option key={company.id} value={company.id}>
                    {company.name}
                  </option>
                ))}
              </select>
            </label>
          )}
          <span className="app-tag">Inventario y almacén</span>
          {user && <span className="app-user">{user.name}</span>}
          <button
            type="button"
            className="button-link"
            onClick={() => {
              void handleLogout();
            }}
          >
            Cerrar sesión
          </button>
        </nav>
      </header>

      <main className="app-main" id="main-content" tabIndex={-1}>
        <Outlet key={activeCompany?.id ?? 'none'} />
      </main>

      <footer className="app-footer">
        <span>Sistema de inventario y almacén</span>
        <span>React · Laravel · MySQL</span>
      </footer>
    </div>
  );
}
