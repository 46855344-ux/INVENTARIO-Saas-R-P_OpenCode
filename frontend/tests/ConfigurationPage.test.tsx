import { render, screen } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { AuthContext } from '../src/features/auth/AuthContext';
import type { AuthContextValue } from '../src/features/auth/AuthContext';
import { ConfigurationPage } from '../src/features/settings/ConfigurationPage';

const { listUnits, listConversions, listLocations, simulateConversion } = vi.hoisted(() => ({
  listUnits: vi.fn(),
  listConversions: vi.fn(),
  listLocations: vi.fn(),
  simulateConversion: vi.fn(),
}));

vi.mock('../src/services/settings', () => ({
  listUnits,
  listConversions,
  listLocations,
  simulateConversion,
  createUnit: vi.fn(),
  updateUnit: vi.fn(),
  updateUnitStatus: vi.fn(),
  createConversion: vi.fn(),
  updateConversion: vi.fn(),
  updateConversionStatus: vi.fn(),
  createLocation: vi.fn(),
  updateLocation: vi.fn(),
  updateLocationStatus: vi.fn(),
}));

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

describe('ConfigurationPage', () => {
  beforeEach(() => {
    listUnits.mockReset().mockResolvedValue({
      data: [
        {
          id: 1,
          code: 'KG',
          name: 'Kilogramo',
          dimension: 'mass',
          precision: 3,
          status: 'active',
        },
      ],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 1 },
    });
    listConversions.mockReset().mockResolvedValue({
      data: [],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 0 },
    });
    listLocations.mockReset().mockResolvedValue({
      data: [],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 0 },
    });
  });

  it('muestra un mensaje cuando falta el permiso de configuración', () => {
    render(
      <AuthContext.Provider value={authValue([])}>
        <ConfigurationPage />
      </AuthContext.Provider>,
    );

    expect(
      screen.getByText(/no tiene permiso para ver la configuración/i),
    ).toBeInTheDocument();
    expect(listUnits).not.toHaveBeenCalled();
  });

  it('lista las unidades de la empresa activa', async () => {
    render(
      <AuthContext.Provider value={authValue(['settings.view'])}>
        <ConfigurationPage />
      </AuthContext.Provider>,
    );

    expect(await screen.findByText('Kilogramo')).toBeInTheDocument();
    expect(listUnits).toHaveBeenCalledWith({ page: 1, per_page: 10 });
  });

  it('oculta las acciones de gestión sin settings.manage', async () => {
    render(
      <AuthContext.Provider value={authValue(['settings.view'])}>
        <ConfigurationPage />
      </AuthContext.Provider>,
    );

    await screen.findByText('Kilogramo');

    expect(
      screen.queryByRole('button', { name: /nueva unidad/i }),
    ).not.toBeInTheDocument();
    expect(screen.queryByRole('button', { name: /editar/i })).not.toBeInTheDocument();
  });

  it('muestra las acciones de gestión con settings.manage', async () => {
    render(
      <AuthContext.Provider value={authValue(['settings.view', 'settings.manage'])}>
        <ConfigurationPage />
      </AuthContext.Provider>,
    );

    await screen.findByText('Kilogramo');

    expect(
      screen.getByRole('button', { name: /nueva unidad/i }),
    ).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /editar/i })).toBeInTheDocument();
  });
});
