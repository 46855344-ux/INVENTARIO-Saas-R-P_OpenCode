const modules = [
  'Autenticación y multiempresa',
  'Catálogo de productos',
  'Unidades y ubicaciones',
  'Inventario y movimientos',
  'Compras y traslados',
  'Métricas y alertas',
];

export function HomePage() {
  return (
    <section className="home">
      <p className="eyebrow">Sprint 1 · Identidad y multiempresa</p>
      <h1>SAAS Inversiones R&amp;P</h1>
      <p className="lead">
        Sistema de gestión de inventario y almacén. Sesión iniciada con
        autenticación SPA (React + Laravel Sanctum).
      </p>

      <h2>Módulos previstos</h2>
      <ul className="module-list">
        {modules.map((module) => (
          <li key={module}>{module}</li>
        ))}
      </ul>
    </section>
  );
}
