// Comandos personalizados de Cypress.
// Ejemplo: iniciar sesión con un usuario de prueba.

declare global {
  // eslint-disable-next-line @typescript-eslint/no-namespace
  namespace Cypress {
    interface Chainable {
      /** Inicia sesión con las credenciales indicadas. */
      login(email: string, password: string): Chainable<void>;
    }
  }
}

Cypress.Commands.add('login', (email: string, password: string) => {
  cy.session([email, password], () => {
    cy.request({
      method: 'POST',
      url: '/api/login',
      body: { email, password },
    });
  });
});

export {};
