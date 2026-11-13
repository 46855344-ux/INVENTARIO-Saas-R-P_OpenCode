import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from '../features/auth/AuthContext';
import { Can } from '../features/auth/Can';

const linkClass = ({ isActive }: { isActive: boolean }) =>
  isActive ? 'app-nav-link is-active' : 'app-nav-link';

export function AppLayout() {
  const { user, companies, activeCompany, setActiveCompany, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate('/login', { replace: true });
  };

  return (
    <div className="app-shell">
      <header className="app-header">
        <Link to="/" className="app-brand">
          SAAS Inversiones R&amp;P
        </Link>
        <nav aria-label="Navegación principal" className="app-nav">
          <NavLink to="/" end className={linkClass}>
            Inicio
          </NavLink>
          <Can permission="identity.users.view">
            <NavLink to="/users" className={linkClass}>
              Usuarios
            </NavLink>
          </Can>
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

      <main className="app-main">
        <Outlet key={activeCompany?.id ?? 'none'} />
      </main>

      <footer className="app-footer">
        <span>Sprint 1 · Identidad y multiempresa</span>
        <span>React · Laravel · MySQL</span>
      </footer>
    </div>
  );
}
