describe('Flujo E2E de inventario', () => {
  const document = `E2E-${Date.now()}`;

  beforeEach(() => {
    cy.visit('/login');
    cy.get('input[name="email"]').type('admin@empresa-demo.test');
    cy.get('input[name="password"]').type('password');
    cy.get('button[type="submit"]').click();
    cy.contains('Métricas', { timeout: 30000 }).should('be.visible');
  });

  it('compra, recepción, traslado, conteo y asistente', () => {
    cy.visit('/purchasing');
    cy.contains('Compras y traslados').should('be.visible');
    cy.contains('button', 'Compras').click();
    cy.contains('button', 'Nueva compra').should('not.be.disabled').click();

    cy.contains('label', 'Proveedor').parent().find('select').first().select('Proveedor E2E');
    cy.get('input[name="document_number"]').clear().type(document);
    cy.get('select[aria-label="Variante de la línea 1"]').first().select('E2E-1 — Variante E2E');
    cy.get('select[aria-label="Ubicación de la línea 1"]').first().select('ALM-E2E — Almacén E2E');
    cy.get('input[aria-label="Cantidad de la línea 1"]').clear().type('3');
    cy.get('input[aria-label="Costo unitario de la línea 1"]').clear().type('10');
    cy.contains('button', 'Guardar borrador').click();

    cy.contains('tr', document, { timeout: 30000 }).contains('Recibir').click();
    cy.get('[role="dialog"]').contains('button', 'Confirmar recepción').click();
    cy.contains('Recepción confirmada', { timeout: 30000 }).should('be.visible');

    cy.contains('button', 'Traslados').click();
    cy.intercept('POST', '**/api/v1/transfers').as('transfer');
    cy.contains('button', 'Nuevo traslado').should('not.be.disabled').click();
    cy.get('.user-form').contains('label', 'Origen').find('select').select('ALM-E2E — Almacén E2E');
    cy.get('.user-form').contains('label', 'Destino').find('select').select('SUC-E2E — Sucursal E2E');
    cy.get('select[aria-label="Variante del traslado 1"]').first().select('E2E-1 — Variante E2E');
    cy.get('input[aria-label="Cantidad del traslado 1"]').clear().type('1');
    cy.contains('button', 'Confirmar traslado').click();
    cy.wait('@transfer').then(({ response }) => {
      expect(response?.statusCode, JSON.stringify(response?.body)).to.eq(201);
    });
    cy.contains('Traslado confirmado', { timeout: 30000 }).should('be.visible');

    cy.visit('/counts');
    cy.contains('button', 'Nuevo conteo').click();
    cy.get('.user-form').contains('label', 'Ubicación').parent().find('select').first().select('ALM-E2E — Almacén E2E');
    cy.get('select[aria-label="Variante de la línea 1"]').first().select('E2E-1 — Variante E2E');
    cy.get('input[aria-label="Cantidad física de la línea 1"]').clear().type('12');
    cy.contains('button', 'Guardar conteo').click();

    cy.contains('tr', 'Abierto', { timeout: 30000 }).contains('Revisar').click();
    cy.get('[role="dialog"]').find('input').type('Revisión E2E');
    cy.get('[role="dialog"]').contains('button', 'Enviar a revisión').click();
    cy.contains('tr', 'En revisión', { timeout: 30000 }).contains('Aprobar').click();
    cy.get('[role="dialog"]').find('input').type('Aprobación E2E');
    cy.get('[role="dialog"]').contains('button', 'Aprobar').click();
    cy.contains('Conteo aprobado', { timeout: 30000 }).should('be.visible');

    cy.get('button[aria-label="Abrir asistente"]').click();
    cy.get('input[aria-label="Pregunta para el asistente"]').type('resumen del inventario');
    cy.contains('button', 'Enviar').click();
    cy.contains('Resumen:', { timeout: 30000 }).should('be.visible');
  });
});
