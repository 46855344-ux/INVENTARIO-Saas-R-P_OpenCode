import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router-dom';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { AuthContext } from '../src/features/auth/AuthContext';
import type { AuthContextValue } from '../src/features/auth/AuthContext';
import { AccountPage } from '../src/features/account/AccountPage';

const mocks = vi.hoisted(() => ({
  listSocialAccounts: vi.fn(),
  listSocialProviders: vi.fn(),
  unlinkSocialAccount: vi.fn(),
  socialLinkUrl: vi.fn((provider: string) => `http://localhost:8000/auth/social/${provider}/link`),
}));

vi.mock('../src/services/social', () => ({
  listSocialAccounts: mocks.listSocialAccounts,
  listSocialProviders: mocks.listSocialProviders,
  unlinkSocialAccount: mocks.unlinkSocialAccount,
  socialLinkUrl: mocks.socialLinkUrl,
}));

function authValue(): AuthContextValue {
  const company = {
    id: 1,
    name: 'Empresa Demo',
    slug: 'empresa-demo',
    role: { id: 1, code: 'owner', name: 'Propietario' },
    permissions: [],
  };

  return {
    status: 'authenticated',
    user: { id: 1, name: 'Administrador Demo', email: 'admin@empresa-demo.test' },
    companies: [company],
    activeCompany: company,
    permissions: [],
    hasPermission: () => true,
    setActiveCompany: vi.fn(),
    login: vi.fn(),
    logout: vi.fn(),
  };
}

function renderAccount(entry = '/account') {
  render(
    <MemoryRouter initialEntries={[entry]}>
      <AuthContext.Provider value={authValue()}>
        <AccountPage />
      </AuthContext.Provider>
    </MemoryRouter>,
  );
}

describe('AccountPage', () => {
  beforeEach(() => {
    mocks.listSocialAccounts.mockReset();
    mocks.listSocialProviders.mockReset().mockResolvedValue([
      { provider: 'google', label: 'Google' },
      { provider: 'facebook', label: 'Facebook' },
    ]);
    mocks.unlinkSocialAccount.mockReset().mockResolvedValue(undefined);
    vi.spyOn(window, 'confirm').mockReturnValue(true);
  });

  it('muestra el método local y las identidades vinculadas', async () => {
    mocks.listSocialAccounts.mockResolvedValue([
      {
        provider: 'google',
        label: 'Google',
        linked_at: '2026-11-15T00:00:00Z',
      },
    ]);

    renderAccount();

    expect(await screen.findByText('Correo y contraseña')).toBeInTheDocument();
    expect(screen.getByText('admin@empresa-demo.test')).toBeInTheDocument();
    expect(screen.getAllByText('Vinculado')).toHaveLength(2);
    expect(screen.getByText('No vinculado')).toBeInTheDocument();
  });

  it('desvincula una cuenta social después de confirmar', async () => {
    mocks.listSocialAccounts.mockResolvedValue([
      {
        provider: 'google',
        label: 'Google',
        linked_at: '2026-11-15T00:00:00Z',
      },
    ]);

    renderAccount();

    await userEvent.click(await screen.findByRole('button', { name: 'Desvincular' }));
    await userEvent.click(
      within(screen.getByRole('dialog')).getByRole('button', { name: 'Desvincular' }),
    );

    expect(mocks.unlinkSocialAccount).toHaveBeenCalledWith('google');
  });

  it('muestra confirmación al volver de vincular y permite vincular otro proveedor', async () => {
    mocks.listSocialAccounts.mockResolvedValue([]);

    renderAccount('/account?social=linked');

    expect(
      await screen.findByText(/cuenta social vinculada correctamente/i),
    ).toBeInTheDocument();
    const links = screen.getAllByRole('link', { name: 'Vincular' });
    expect(links[0]).toHaveAttribute(
      'href',
      'http://localhost:8000/auth/social/google/link',
    );
  });
});
