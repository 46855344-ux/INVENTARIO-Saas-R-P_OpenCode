import { Link, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from '../features/auth/AuthContext';

export function AppLayout() {
  const { user, logout } = useAuth();
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
        <Outlet />
      </main>

      <footer className="app-footer">
        <span>Sprint 1 · Identidad y multiempresa</span>
        <span>React · Laravel · MySQL</span>
      </footer>
    </div>
  );
}
