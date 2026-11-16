import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { AuthContext } from '../src/features/auth/AuthContext';
import type { AuthContextValue } from '../src/features/auth/AuthContext';
import { LoginPage } from '../src/features/auth/LoginPage';

const { listSocialProviders, socialLoginUrl } = vi.hoisted(() => ({
  listSocialProviders: vi.fn(),
  socialLoginUrl: vi.fn((provider: string) => `http://localhost:8000/auth/social/${provider}/redirect`),
}));

vi.mock('../src/services/social', () => ({
  listSocialProviders,
  socialLoginUrl,
}));

function guestValue(): AuthContextValue {
  return {
    status: 'guest',
    user: null,
    companies: [],
    activeCompany: null,
    permissions: [],
    hasPermission: () => false,
    setActiveCompany: vi.fn(),
    login: vi.fn(),
    logout: vi.fn(),
  };
}

function renderLogin(entry = '/login') {
  render(
    <MemoryRouter initialEntries={[entry]}>
      <AuthContext.Provider value={guestValue()}>
        <LoginPage />
      </AuthContext.Provider>
    </MemoryRouter>,
  );
}

describe('LoginPage social access', () => {
  beforeEach(() => {
    listSocialProviders.mockReset().mockResolvedValue([
      { provider: 'google', label: 'Google' },
      { provider: 'facebook', label: 'Facebook' },
    ]);
  });

  it('muestra los proveedores configurados con enlaces accesibles', async () => {
    renderLogin();

    const google = await screen.findByRole('link', { name: 'Continuar con Google' });
    expect(google).toHaveAttribute(
      'href',
      'http://localhost:8000/auth/social/google/redirect',
    );
    expect(
      screen.getByRole('link', { name: 'Continuar con Facebook' }),
    ).toBeInTheDocument();
  });

  it('oculta los botones sociales si no hay proveedores configurados', async () => {
    listSocialProviders.mockResolvedValue([]);
    renderLogin();

    expect(await screen.findByRole('button', { name: /ingresar/i })).toBeInTheDocument();
    expect(
      screen.queryByRole('link', { name: /continuar con google/i }),
    ).not.toBeInTheDocument();
  });

  it('muestra un mensaje seguro cuando la identidad no está vinculada', async () => {
    renderLogin('/login?social_error=not_linked');

    expect(
      await screen.findByText(/la cuenta social no está vinculada/i),
    ).toBeInTheDocument();
  });
});
