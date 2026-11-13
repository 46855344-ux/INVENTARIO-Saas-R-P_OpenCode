import { beforeEach, describe, expect, it, vi } from 'vitest';
import type { AuthSession } from '../src/types';

const { get, post } = vi.hoisted(() => ({ get: vi.fn(), post: vi.fn() }));

vi.mock('../src/services/api', () => ({
  api: { get, post },
  API_ORIGIN: 'http://localhost:8000',
}));

import { login, logout, me } from '../src/services/auth';

const session: AuthSession = {
  user: { id: 1, name: 'Administrador Demo', email: 'admin@empresa-demo.test' },
  companies: [{ id: 1, name: 'Empresa Demo', slug: 'empresa-demo' }],
  permissions: [],
};

describe('auth service', () => {
  beforeEach(() => {
    get.mockReset().mockResolvedValue({ data: { data: session } });
    post.mockReset().mockResolvedValue({ data: { data: session } });
  });

  it('pide la cookie CSRF y llama a /v1/auth/login', async () => {
    await expect(login('admin@empresa-demo.test', 'password')).resolves.toEqual(session);

    expect(get).toHaveBeenCalledWith('http://localhost:8000/sanctum/csrf-cookie');
    expect(post).toHaveBeenCalledWith('/v1/auth/login', {
      email: 'admin@empresa-demo.test',
      password: 'password',
    });
  });

  it('consulta el perfil en /v1/auth/me', async () => {
    await expect(me()).resolves.toEqual(session);

    expect(get).toHaveBeenCalledWith('/v1/auth/me');
  });

  it('cierra sesión en /v1/auth/logout', async () => {
    await logout();

    expect(post).toHaveBeenCalledWith('/v1/auth/logout');
  });
});
