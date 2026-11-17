describe('Flujo crítico de interfaz', () => {
  it('inicia sesión, consulta métricas y usa el asistente', () => {
    cy.visit('/login');
    cy.get('input[name="email"]').type('admin@empresa-demo.test');
    cy.get('input[name="password"]').type('password');
    cy.get('button[type="submit"]').click();

    cy.contains('Métricas', { timeout: 20000 }).should('be.visible');

    cy.visit('/metrics');
    cy.contains('Total de productos', { timeout: 20000 }).should('be.visible');
    cy.contains('Valor total').should('be.visible');

    cy.get('button[aria-label="Abrir asistente"]').click();
    cy.get('input[aria-label="Pregunta para el asistente"]').type('resumen del inventario');
    cy.contains('button', 'Enviar').click();
    cy.contains('Resumen:', { timeout: 20000 }).should('be.visible');
  });
});
