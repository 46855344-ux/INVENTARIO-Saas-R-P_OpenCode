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
      <p className="eyebrow">Sprint 0 · Fundación técnica</p>
      <h1>SAAS Inversiones R&amp;P</h1>
      <p className="lead">
        Sistema de gestión de inventario y almacén. Esta es la base del proyecto:
        frontend React, API Laravel y MySQL.
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
