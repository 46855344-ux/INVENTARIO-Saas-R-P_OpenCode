import axios from 'axios';

const apiBaseUrl = import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api';

/**
 * Origen del backend, sin la ruta `/api`.
 * La cookie CSRF de Sanctum se sirve en `<origen>/sanctum/csrf-cookie`,
 * por fuera del prefijo versionado de la API.
 */
export const API_ORIGIN = new URL(apiBaseUrl, window.location.origin).origin;

let activeCompanyId: number | null = null;

/**
 * Fija la empresa activa que viaja en `X-Company-Id` (ver ADR-0002).
 * La asigna el proveedor de autenticación al cargar o cambiar de empresa.
 */
export function setActiveCompanyId(companyId: number | null): void {
  activeCompanyId = companyId;
}

export function getActiveCompanyId(): number | null {
  return activeCompanyId;
}

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

api.interceptors.request.use((config) => {
  if (activeCompanyId !== null) {
    config.headers.set('X-Company-Id', String(activeCompanyId));
  }

  return config;
});
