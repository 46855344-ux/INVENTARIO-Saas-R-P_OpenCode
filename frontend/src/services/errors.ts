import axios from 'axios';
import type { ApiErrorPayload } from '../types';

export interface ApiError {
  code: string;
  message: string;
}

/**
 * Normaliza cualquier fallo a un mensaje en español apto para la interfaz.
 * Nunca expone trazas ni estructura interna del servidor.
 */
export function resolveApiError(error: unknown): ApiError {
  if (axios.isAxiosError<ApiErrorPayload>(error) && error.response?.data?.error) {
    return {
      code: error.response.data.error.code,
      message: error.response.data.error.message,
    };
  }

  return {
    code: 'NETWORK_ERROR',
    message: 'No se pudo conectar con el servidor. Intente nuevamente.',
  };
}
