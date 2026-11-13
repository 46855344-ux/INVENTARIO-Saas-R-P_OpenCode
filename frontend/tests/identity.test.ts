import { beforeEach, describe, expect, it, vi } from 'vitest';

const { get, post, put, patch } = vi.hoisted(() => ({
  get: vi.fn(),
  post: vi.fn(),
  put: vi.fn(),
  patch: vi.fn(),
}));

vi.mock('../src/services/api', () => ({
  api: { get, post, put, patch },
}));

import {
  assignUserRole,
  createUser,
  listPermissions,
  listRoles,
  listUsers,
  updateUser,
  updateUserStatus,
} from '../src/services/identity';

const member = {
  id: 7,
  name: 'Ana Pérez',
  email: 'ana@empresa.test',
  status: 'active' as const,
  role: null,
};

const page = {
  data: [member],
  meta: { current_page: 1, last_page: 1, per_page: 10, total: 1 },
};

describe('identity service', () => {
  beforeEach(() => {
    get.mockReset().mockResolvedValue({ data: page });
    post.mockReset().mockResolvedValue({ data: { data: member } });
    put.mockReset().mockResolvedValue({ data: { data: member } });
    patch.mockReset().mockResolvedValue({ data: { data: member } });
  });

  it('lista usuarios con los filtros validados en /v1/users', async () => {
    await expect(
      listUsers({ page: 2, per_page: 10, search: 'ana', status: 'active', role_id: 3 }),
    ).resolves.toEqual(page);

    expect(get).toHaveBeenCalledWith('/v1/users', {
      params: { page: 2, per_page: 10, search: 'ana', status: 'active', role_id: 3 },
    });
  });

  it('crea un usuario en /v1/users', async () => {
    const input = {
      name: 'Ana Pérez',
      email: 'ana@empresa.test',
      password: 'password123',
      password_confirmation: 'password123',
      role_id: 2,
    };

    await expect(createUser(input)).resolves.toEqual(member);
    expect(post).toHaveBeenCalledWith('/v1/users', input);
  });

  it('actualiza un usuario en /v1/users/{id}', async () => {
    await updateUser(7, { name: 'Ana Actualizada' });

    expect(put).toHaveBeenCalledWith('/v1/users/7', { name: 'Ana Actualizada' });
  });

  it('cambia el estado en /v1/users/{id}/status', async () => {
    await updateUserStatus(7, 'inactive');

    expect(patch).toHaveBeenCalledWith('/v1/users/7/status', { status: 'inactive' });
  });

  it('asigna un rol en /v1/users/{id}/role', async () => {
    await assignUserRole(7, 3);

    expect(put).toHaveBeenCalledWith('/v1/users/7/role', { role_id: 3 });
  });

  it('consulta los catálogos de roles y permisos', async () => {
    await listRoles();
    await listPermissions();

    expect(get).toHaveBeenCalledWith('/v1/roles', { params: { per_page: 100 } });
    expect(get).toHaveBeenCalledWith('/v1/permissions', { params: { per_page: 100 } });
  });
});
