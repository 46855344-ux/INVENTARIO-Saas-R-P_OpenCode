import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import App from '../src/app/App';

const { me, login, logout } = vi.hoisted(() => ({
  me: vi.fn(),
  login: vi.fn(),
  logout: vi.fn(),
}));

vi.mock('../src/services/auth', () => ({ me, login, logout }));

vi.mock('../src/services/social', () => ({
  listSocialProviders: vi.fn().mockResolvedValue([]),
  listSocialAccounts: vi.fn().mockResolvedValue([]),
  unlinkSocialAccount: vi.fn(),
  socialLoginUrl: (provider: string) => `http://localhost:8000/auth/social/${provider}/redirect`,
  socialLinkUrl: (provider: string) => `http://localhost:8000/auth/social/${provider}/link`,
}));

const session = {
  user: {
    id: 1,
    name: 'Administrador Demo',
    email: 'admin@empresa-demo.test',
  },
  companies: [
    {
      id: 1,
      name: 'Empresa Demo',
      slug: 'empresa-demo',
      role: { id: 1, code: 'owner', name: 'Propietario' },
      permissions: [
        'catalog.view',
        'catalog.manage',
        'settings.view',
        'settings.manage',
        'inventory.view',
        'inventory.move',
        'inventory.adjust',
        'inventory.approve',
        'identity.users.view',
        'identity.users.manage',
        'identity.roles.view',
      ],
    },
  ],
  permissions: [
    'catalog.view',
    'catalog.manage',
    'settings.view',
    'settings.manage',
    'inventory.view',
    'inventory.move',
    'inventory.adjust',
    'inventory.approve',
    'identity.users.view',
    'identity.users.manage',
    'identity.roles.view',
  ],
};

describe('App', () => {
  beforeEach(() => {
    window.localStorage.clear();
    me.mockReset().mockRejectedValue(new Error('unauthorized'));
    login.mockReset();
    logout.mockReset();
  });

  it('muestra la pantalla de acceso cuando no hay sesión', async () => {
    render(<App />);

    expect(
      await screen.findByRole('heading', {
        level: 1,
        name: /SAAS Inversiones R&P/i,
      }),
    ).toBeInTheDocument();

    expect(
      screen.getByText(/Sistema de gestión de inventario y almacén/i),
    ).toBeInTheDocument();

    expect(screen.getByRole('button', { name: /ingresar/i })).toBeInTheDocument();
  });

  it('inicia sesión y muestra el nombre del usuario', async () => {
    login.mockResolvedValue(session);

    render(<App />);

    await screen.findByRole('heading', { level: 1, name: /SAAS Inversiones R&P/i });

    await userEvent.type(
      screen.getByLabelText(/correo electrónico/i),
      'admin@empresa-demo.test',
    );
    await userEvent.type(screen.getByLabelText(/contraseña/i), 'password');
    await userEvent.click(screen.getByRole('button', { name: /ingresar/i }));

    expect(await screen.findByText('Administrador Demo')).toBeInTheDocument();
    expect(screen.getByRole('link', { name: /catálogo/i })).toBeInTheDocument();
    expect(screen.getByRole('link', { name: /configuración/i })).toBeInTheDocument();
    expect(screen.getByRole('link', { name: /inventario/i })).toBeInTheDocument();
    expect(screen.getByRole('link', { name: /usuarios/i })).toBeInTheDocument();
  });
});
