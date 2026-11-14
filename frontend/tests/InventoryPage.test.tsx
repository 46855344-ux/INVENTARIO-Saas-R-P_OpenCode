import { render, screen } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { AuthContext } from '../src/features/auth/AuthContext';
import type { AuthContextValue } from '../src/features/auth/AuthContext';
import { InventoryPage } from '../src/features/inventory/InventoryPage';

const { listStock, listMovements } = vi.hoisted(() => ({
  listStock: vi.fn(),
  listMovements: vi.fn(),
}));

vi.mock('../src/services/inventory', () => ({
  listStock,
  listMovements,
  getMovement: vi.fn(),
  createEntry: vi.fn(),
  createExit: vi.fn(),
  createReturn: vi.fn(),
  createAdjustment: vi.fn(),
  reverseMovement: vi.fn(),
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

describe('InventoryPage', () => {
  beforeEach(() => {
    listStock.mockReset().mockResolvedValue({
      data: [
        {
          id: 1,
          variant_id: 1,
          location_id: 1,
          quantity: '5.0000',
          reserved_quantity: '0.0000',
          variant: {
            id: 1,
            sku: 'SKU-1',
            name: 'Variante',
            product: { id: 1, name: 'Café' },
          },
          location: { id: 1, code: 'ALM-01', name: 'Almacén' },
        },
      ],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 1 },
    });
    listMovements.mockReset().mockResolvedValue({
      data: [],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 0 },
    });
  });

  it('muestra un mensaje cuando falta el permiso de inventario', () => {
    render(
      <AuthContext.Provider value={authValue([])}>
        <InventoryPage />
      </AuthContext.Provider>,
    );

    expect(
      screen.getByText(/no tiene permiso para ver el inventario/i),
    ).toBeInTheDocument();
    expect(listStock).not.toHaveBeenCalled();
  });

  it('lista las existencias de la empresa activa', async () => {
    render(
      <AuthContext.Provider value={authValue(['inventory.view'])}>
        <InventoryPage />
      </AuthContext.Provider>,
    );

    expect(await screen.findByText('Café')).toBeInTheDocument();
    expect(screen.getByText('SKU-1')).toBeInTheDocument();
    expect(screen.getByText('5.0000')).toBeInTheDocument();
    expect(listStock).toHaveBeenCalledWith({ page: 1, per_page: 10 });
  });
});
