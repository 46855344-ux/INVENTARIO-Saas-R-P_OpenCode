import { render, screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { Can } from '../src/features/auth/Can';
import { AuthContext } from '../src/features/auth/AuthContext';
import type { AuthContextValue } from '../src/features/auth/AuthContext';

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
    user: { id: 1, name: 'Administrador Demo', email: 'admin@empresa-demo.test' },
    companies: [company],
    activeCompany: company,
    permissions,
    hasPermission: (code: string) => permissions.includes(code),
    setActiveCompany: vi.fn(),
    login: vi.fn(),
    logout: vi.fn(),
  };
}

describe('Can', () => {
  it('muestra el contenido cuando el permiso está presente', () => {
    render(
      <AuthContext.Provider value={authValue(['identity.users.view'])}>
        <Can permission="identity.users.view">
          <span>visible</span>
        </Can>
      </AuthContext.Provider>,
    );

    expect(screen.getByText('visible')).toBeInTheDocument();
  });

  it('muestra el fallback cuando falta el permiso', () => {
    render(
      <AuthContext.Provider value={authValue([])}>
        <Can permission="identity.users.manage" fallback={<span>sin permiso</span>}>
          <span>accion</span>
        </Can>
      </AuthContext.Provider>,
    );

    expect(screen.getByText('sin permiso')).toBeInTheDocument();
    expect(screen.queryByText('accion')).not.toBeInTheDocument();
  });
});
