describe('Escáner y QR de variantes', () => {
  beforeEach(() => {
    cy.visit('/login');
    cy.get('input[name="email"]').type('admin@empresa-demo.test');
    cy.get('input[name="password"]').type('password');
    cy.get('button[type="submit"]').click();
    cy.contains('Métricas', { timeout: 30000 }).should('be.visible');
  });

  it('genera el QR de una variante y busca el producto por SKU', () => {
    cy.visit('/catalog');
    cy.contains('button', 'Productos').click();
    cy.contains('tr', 'Producto E2E', { timeout: 30000 })
      .contains('button', 'Variantes')
      .click();

    cy.contains('tr', 'E2E-1', { timeout: 30000 }).within(() => {
      cy.contains('button', 'QR').click();
    });

    cy.get('[role="dialog"]').should('be.visible');
    cy.get('[role="dialog"]').contains('dd', 'E2E-1').should('be.visible');
    cy.get('[role="dialog"] img.qr-image')
      .should('be.visible')
      .and('have.attr', 'src')
      .and('match', /^data:image\/png;base64,/);
    cy.contains('[role="dialog"] a', 'Descargar PNG')
      .should('have.attr', 'download')
      .and('include', 'qr-E2E-1');
    cy.get('[role="dialog"]').contains('button', 'Cerrar').click();

    cy.get('nav[aria-label="Navegación principal"]').contains('Escáner').click();
    cy.contains('h1', 'Escanear código QR', { timeout: 30000 }).should('be.visible');

    cy.get('input[name="code"]').type('E2E-1');
    cy.contains('button', 'Buscar').click();

    cy.contains('h2', 'Producto E2E', { timeout: 30000 }).should('be.visible');
    cy.contains('Stock total:').should('be.visible');
    cy.contains('ALM-E2E').should('be.visible');
  });
});
