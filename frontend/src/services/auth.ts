import type { AuthSession } from '../types';
import { API_ORIGIN, api } from './api';

/**
 * Recupera la cookie CSRF que Sanctum exige antes de un POST desde la SPA.
 */
async function ensureCsrfCookie(): Promise<void> {
  await api.get(`${API_ORIGIN}/sanctum/csrf-cookie`);
}

export async function login(email: string, password: string): Promise<AuthSession> {
  await ensureCsrfCookie();

  const { data } = await api.post<{ data: AuthSession }>('/v1/auth/login', {
    email,
    password,
  });

  return data.data;
}

export async function logout(): Promise<void> {
  await api.post('/v1/auth/logout');
}

export async function me(): Promise<AuthSession> {
  const { data } = await api.get<{ data: AuthSession }>('/v1/auth/me');

  return data.data;
}
