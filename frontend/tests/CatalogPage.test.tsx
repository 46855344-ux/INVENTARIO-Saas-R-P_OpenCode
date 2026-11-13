import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { AuthContext } from '../src/features/auth/AuthContext';
import type { AuthContextValue } from '../src/features/auth/AuthContext';
import { CatalogPage } from '../src/features/products/CatalogPage';

const { listCategories, listBrands, listProducts, listVariants } = vi.hoisted(() => ({
  listCategories: vi.fn(),
  listBrands: vi.fn(),
  listProducts: vi.fn(),
  listVariants: vi.fn(),
}));

vi.mock('../src/services/catalog', () => ({
  listCategories,
  listBrands,
  listProducts,
  listVariants,
  createCategory: vi.fn(),
  updateCategory: vi.fn(),
  updateCategoryStatus: vi.fn(),
  createBrand: vi.fn(),
  updateBrand: vi.fn(),
  updateBrandStatus: vi.fn(),
  createProduct: vi.fn(),
  updateProduct: vi.fn(),
  updateProductStatus: vi.fn(),
  createVariant: vi.fn(),
  updateVariant: vi.fn(),
  updateVariantStatus: vi.fn(),
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

describe('CatalogPage', () => {
  beforeEach(() => {
    listCategories.mockReset().mockResolvedValue({
      data: [{ id: 1, name: 'Bebidas', status: 'active' }],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 1 },
    });
    listBrands.mockReset().mockResolvedValue({
      data: [{ id: 2, name: 'Marca X', status: 'active' }],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 1 },
    });
    listProducts.mockReset().mockResolvedValue({
      data: [],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 0 },
    });
    listVariants.mockReset().mockResolvedValue({
      data: [],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 0 },
    });
  });

  it('muestra un mensaje cuando falta el permiso de catálogo', () => {
    render(
      <AuthContext.Provider value={authValue([])}>
        <CatalogPage />
      </AuthContext.Provider>,
    );

    expect(
      screen.getByText(/no tiene permiso para ver el catálogo/i),
    ).toBeInTheDocument();
    expect(listCategories).not.toHaveBeenCalled();
  });

  it('lista las categorías de la empresa activa', async () => {
    render(
      <AuthContext.Provider value={authValue(['catalog.view'])}>
        <CatalogPage />
      </AuthContext.Provider>,
    );

    expect(await screen.findByText('Bebidas')).toBeInTheDocument();
    expect(listCategories).toHaveBeenCalledWith({ page: 1, per_page: 10 });
  });

  it('oculta las acciones de gestión sin catalog.manage', async () => {
    render(
      <AuthContext.Provider value={authValue(['catalog.view'])}>
        <CatalogPage />
      </AuthContext.Provider>,
    );

    await screen.findByText('Bebidas');

    expect(
      screen.queryByRole('button', { name: /nueva categoría/i }),
    ).not.toBeInTheDocument();
    expect(screen.queryByRole('button', { name: /editar/i })).not.toBeInTheDocument();
  });

  it('muestra las acciones de gestión con catalog.manage', async () => {
    render(
      <AuthContext.Provider value={authValue(['catalog.view', 'catalog.manage'])}>
        <CatalogPage />
      </AuthContext.Provider>,
    );

    await screen.findByText('Bebidas');

    expect(
      screen.getByRole('button', { name: /nueva categoría/i }),
    ).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /editar/i })).toBeInTheDocument();
  });

  it('permite cambiar a la pestaña de marcas', async () => {
    render(
      <AuthContext.Provider value={authValue(['catalog.view'])}>
        <CatalogPage />
      </AuthContext.Provider>,
    );

    await screen.findByText('Bebidas');
    await userEvent.click(screen.getByRole('button', { name: 'Marcas' }));

    expect(await screen.findByText('Marca X')).toBeInTheDocument();
    expect(listBrands).toHaveBeenCalledWith({ page: 1, per_page: 10 });
  });

  it('muestra los productos en su pestaña', async () => {
    listProducts.mockResolvedValue({
      data: [
        {
          id: 5,
          name: 'Café molido',
          description: null,
          status: 'active',
          category_id: null,
          brand_id: null,
          category: null,
          brand: null,
        },
      ],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 1 },
    });

    render(
      <AuthContext.Provider value={authValue(['catalog.view'])}>
        <CatalogPage />
      </AuthContext.Provider>,
    );

    await screen.findByText('Bebidas');
    await userEvent.click(screen.getByRole('button', { name: 'Productos' }));

    expect(await screen.findByText('Café molido')).toBeInTheDocument();
    expect(listProducts).toHaveBeenCalledWith({ page: 1, per_page: 10 });
  });

  it('muestra las variantes de un producto', async () => {
    listProducts.mockResolvedValue({
      data: [
        {
          id: 5,
          name: 'Café molido',
          description: null,
          status: 'active',
          category_id: null,
          brand_id: null,
          category: null,
          brand: null,
        },
      ],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 1 },
    });
    listVariants.mockResolvedValue({
      data: [
        {
          id: 8,
          product_id: 5,
          name: 'Talle M',
          sku: 'SKU-001',
          barcode: null,
          attributes: null,
          cost: '0.0000',
          price: '0.0000',
          status: 'active',
        },
      ],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 1 },
    });

    render(
      <AuthContext.Provider value={authValue(['catalog.view'])}>
        <CatalogPage />
      </AuthContext.Provider>,
    );

    await screen.findByText('Bebidas');
    await userEvent.click(screen.getByRole('button', { name: 'Productos' }));
    await screen.findByText('Café molido');
    await userEvent.click(screen.getByRole('button', { name: 'Variantes' }));

    expect(await screen.findByText('SKU-001')).toBeInTheDocument();
    expect(listVariants).toHaveBeenCalledWith(5, { page: 1, per_page: 10 });
  });
});
