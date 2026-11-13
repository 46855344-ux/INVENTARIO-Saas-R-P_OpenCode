describe('Aplicación', () => {
  it('muestra la pantalla de acceso', () => {
    cy.visit('/');
    cy.contains('SAAS Inversiones R&P', { timeout: 15000 }).should('be.visible');
  });
});
