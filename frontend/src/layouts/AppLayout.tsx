import { Link, Outlet } from 'react-router-dom';

export function AppLayout() {
  return (
    <div className="app-shell">
      <header className="app-header">
        <Link to="/" className="app-brand">
          SAAS Inversiones R&amp;P
        </Link>
        <nav aria-label="Navegación principal">
          <span className="app-tag">Inventario y almacén</span>
        </nav>
      </header>

      <main className="app-main">
        <Outlet />
      </main>

      <footer className="app-footer">
        <span>Sprint 0 · Fundación técnica</span>
        <span>React · Laravel · MySQL</span>
      </footer>
    </div>
  );
}
