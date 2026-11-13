import axios from 'axios';

/**
 * Cliente HTTP único de la aplicación.
 * El componente no arma URLs: cada feature expone su servicio en `services/`.
 */
export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api',
  headers: {
    Accept: 'application/json',
  },
  withCredentials: true,
});
