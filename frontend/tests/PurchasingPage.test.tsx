import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { AuthContext } from '../src/features/auth/AuthContext';
import type { AuthContextValue } from '../src/features/auth/AuthContext';
import { PurchasingPage } from '../src/features/purchasing/PurchasingPage';

const mocks = vi.hoisted(() => ({
  listSuppliers: vi.fn(),
  listPurchases: vi.fn(),
  listTransfers: vi.fn(),
  listAllVariants: vi.fn(),
  listLocations: vi.fn(),
}));

vi.mock('../src/services/purchasing', () => ({
  listSuppliers: mocks.listSuppliers,
  listPurchases: mocks.listPurchases,
  listTransfers: mocks.listTransfers,
  createSupplier: vi.fn(),
  updateSupplier: vi.fn(),
  updateSupplierStatus: vi.fn(),
  createPurchase: vi.fn(),
  updatePurchase: vi.fn(),
  receivePurchase: vi.fn(),
  createTransfer: vi.fn(),
  getPurchase: vi.fn(),
  getTransfer: vi.fn(),
}));

vi.mock('../src/services/catalog', () => ({
  listAllVariants: mocks.listAllVariants,
}));

vi.mock('../src/services/settings', () => ({
  listLocations: mocks.listLocations,
}));

const EMPTY_META = { current_page: 1, last_page: 1, per_page: 10, total: 0 };

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

describe('PurchasingPage', () => {
  beforeEach(() => {
    mocks.listSuppliers.mockReset().mockResolvedValue({
      data: [
        {
          id: 1,
          name: 'Distribuidora Central',
          tax_id: '30-12345678-9',
          email: 'compras@central.test',
          phone: null,
          status: 'active',
        },
      ],
      meta: { ...EMPTY_META, total: 1 },
    });
    mocks.listPurchases.mockReset().mockResolvedValue({ data: [], meta: EMPTY_META });
    mocks.listTransfers.mockReset().mockResolvedValue({ data: [], meta: EMPTY_META });
    mocks.listAllVariants.mockReset().mockResolvedValue({ data: [], meta: EMPTY_META });
    mocks.listLocations.mockReset().mockResolvedValue({ data: [], meta: EMPTY_META });
  });

  it('muestra un mensaje cuando no hay permisos de compras ni traslados', () => {
    render(
      <AuthContext.Provider value={authValue([])}>
        <PurchasingPage />
      </AuthContext.Provider>,
    );

    expect(
      screen.getByText(/no tiene permiso para ver compras ni traslados/i),
    ).toBeInTheDocument();
    expect(mocks.listSuppliers).not.toHaveBeenCalled();
  });

  it('lista los proveedores de la empresa activa', async () => {
    render(
      <AuthContext.Provider value={authValue(['purchases.view'])}>
        <PurchasingPage />
      </AuthContext.Provider>,
    );

    expect(await screen.findByText('Distribuidora Central')).toBeInTheDocument();
    expect(screen.getByText('30-12345678-9')).toBeInTheDocument();
    expect(mocks.listSuppliers).toHaveBeenCalledWith({ page: 1, per_page: 10 });
  });

  it('abre la pestaña de compras y muestra su estado vacío', async () => {
    render(
      <AuthContext.Provider value={authValue(['purchases.view'])}>
        <PurchasingPage />
      </AuthContext.Provider>,
    );

    await userEvent.click(screen.getByRole('button', { name: 'Compras' }));

    expect(await screen.findByText(/no hay compras registradas/i)).toBeInTheDocument();
    expect(mocks.listPurchases).toHaveBeenCalledWith({ page: 1, per_page: 10 });
  });

  it('muestra traslados cuando solo existe ese permiso', async () => {
    render(
      <AuthContext.Provider value={authValue(['transfers.view'])}>
        <PurchasingPage />
      </AuthContext.Provider>,
    );

    expect(await screen.findByText(/no hay traslados registrados/i)).toBeInTheDocument();
    expect(screen.queryByRole('button', { name: 'Proveedores' })).not.toBeInTheDocument();
  });
});
