import { render, screen } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { AuthContext } from '../src/features/auth/AuthContext';
import type { AuthContextValue } from '../src/features/auth/AuthContext';
import { UsersPage } from '../src/features/identity/UsersPage';

const { listUsers, listRoles } = vi.hoisted(() => ({
  listUsers: vi.fn(),
  listRoles: vi.fn(),
}));

vi.mock('../src/services/identity', () => ({
  listUsers,
  listRoles,
  createUser: vi.fn(),
  updateUser: vi.fn(),
  updateUserStatus: vi.fn(),
  assignUserRole: vi.fn(),
}));

const role = {
  id: 2,
  code: 'admin',
  name: 'Administrador',
  description: null,
  is_global: true,
  permissions: ['identity.users.view'],
};

const member = {
  id: 7,
  name: 'Ana Pérez',
  email: 'ana@empresa.test',
  status: 'active' as const,
  role,
};

function authValue(permissions: string[]): AuthContextValue {
  const company = {
    id: 1,
    name: 'Empresa Demo',
    slug: 'empresa-demo',
    role: { id: 1, code: 'owner', name: 'Propietario' },
    permissions,
  };

  return {
    status: 'authenticated',
    user: {
      id: 1,
      name: 'Administrador Demo',
      email: 'admin@empresa-demo.test',
    },
    companies: [company],
    activeCompany: company,
    permissions,
    hasPermission: (code: string) => permissions.includes(code),
    setActiveCompany: vi.fn(),
    login: vi.fn(),
    logout: vi.fn(),
  };
}

describe('UsersPage', () => {
  beforeEach(() => {
    listUsers.mockReset().mockResolvedValue({
      data: [member],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 1 },
    });
    listRoles.mockReset().mockResolvedValue([role]);
  });

  it('muestra un mensaje cuando falta el permiso de lectura', () => {
    render(
      <AuthContext.Provider value={authValue([])}>
        <UsersPage />
      </AuthContext.Provider>,
    );

    expect(
      screen.getByText(/no tiene permiso para ver los usuarios/i),
    ).toBeInTheDocument();
    expect(listUsers).not.toHaveBeenCalled();
  });

  it('lista los usuarios de la empresa activa', async () => {
    render(
      <AuthContext.Provider value={authValue(['identity.users.view'])}>
        <UsersPage />
      </AuthContext.Provider>,
    );

    expect(await screen.findByText('Ana Pérez')).toBeInTheDocument();
    expect(screen.getByText('ana@empresa.test')).toBeInTheDocument();
    expect(listUsers).toHaveBeenCalledWith({ page: 1, per_page: 10 });
  });

  it('oculta las acciones de gestión sin el permiso correspondiente', async () => {
    render(
      <AuthContext.Provider value={authValue(['identity.users.view'])}>
        <UsersPage />
      </AuthContext.Provider>,
    );

    await screen.findByText('Ana Pérez');

    expect(
      screen.queryByRole('button', { name: /nuevo usuario/i }),
    ).not.toBeInTheDocument();
    expect(screen.queryByRole('button', { name: /editar/i })).not.toBeInTheDocument();
  });

  it('muestra las acciones de gestión con el permiso correspondiente', async () => {
    render(
      <AuthContext.Provider
        value={authValue([
          'identity.users.view',
          'identity.users.manage',
          'identity.roles.view',
        ])}
      >
        <UsersPage />
      </AuthContext.Provider>,
    );

    await screen.findByText('Ana Pérez');

    expect(
      screen.getByRole('button', { name: /nuevo usuario/i }),
    ).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /editar/i })).toBeInTheDocument();
  });
});
