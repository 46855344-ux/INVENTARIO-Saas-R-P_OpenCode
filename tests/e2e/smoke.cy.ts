describe('Aplicación', () => {
  it('muestra la página de inicio', () => {
    cy.visit('/');
    cy.contains('SAAS Inversiones R&P');
  });
});
