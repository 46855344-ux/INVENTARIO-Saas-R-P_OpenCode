import { defineConfig } from 'cypress';

export default defineConfig({
  chromeWebSecurity: false,
  e2e: {
    // Se puede sobreescribir con la variable de entorno CYPRESS_BASE_URL.
    baseUrl: 'http://localhost:5173',
    specPattern: 'tests/e2e/**/*.cy.ts',
    supportFile: 'tests/support/e2e.ts',
    fixturesFolder: 'tests/fixtures',
    screenshotsFolder: 'tests/reports/screenshots',
    videosFolder: 'tests/reports/videos',
    downloadsFolder: 'tests/reports/downloads',
    video: false,
    viewportWidth: 1280,
    viewportHeight: 720,
    setupNodeEvents(_on, config) {
      return config;
    },
  },
});
