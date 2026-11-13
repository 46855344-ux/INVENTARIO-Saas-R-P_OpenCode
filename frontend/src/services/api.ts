import axios from 'axios';

const apiBaseUrl = import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api';

/**
 * Origen del backend, sin la ruta `/api`.
 * La cookie CSRF de Sanctum se sirve en `<origen>/sanctum/csrf-cookie`,
 * por fuera del prefijo versionado de la API.
 */
export const API_ORIGIN = new URL(apiBaseUrl, window.location.origin).origin;

/**
 * Cliente HTTP único de la aplicación.
 * El componente no arma URLs: cada feature expone su servicio en `services/`.
 */
export const api = axios.create({
  baseURL: apiBaseUrl,
  headers: {
    Accept: 'application/json',
  },
  withCredentials: true,
  // Envía `X-XSRF-TOKEN` también en peticiones de origen cruzado (SPA → API).
  withXSRFToken: true,
});
