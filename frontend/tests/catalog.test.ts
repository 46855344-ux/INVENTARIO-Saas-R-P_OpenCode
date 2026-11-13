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
  createBrand,
  createCategory,
  createProduct,
  listBrands,
  listCategories,
  listProducts,
  updateBrandStatus,
  updateCategory,
  updateCategoryStatus,
  updateProduct,
  updateProductStatus,
} from '../src/services/catalog';

const item = { id: 1, name: 'Bebidas', status: 'active' as const };

const page = {
  data: [item],
  meta: { current_page: 1, last_page: 1, per_page: 10, total: 1 },
};

describe('catalog service', () => {
  beforeEach(() => {
    get.mockReset().mockResolvedValue({ data: page });
    post.mockReset().mockResolvedValue({ data: { data: item } });
    put.mockReset().mockResolvedValue({ data: { data: item } });
    patch.mockReset().mockResolvedValue({ data: { data: item } });
  });

  it('lista categorías con filtros en /v1/categories', async () => {
    await expect(
      listCategories({ page: 2, per_page: 10, search: 'beb', status: 'active' }),
    ).resolves.toEqual(page);

    expect(get).toHaveBeenCalledWith('/v1/categories', {
      params: { page: 2, per_page: 10, search: 'beb', status: 'active' },
    });
  });

  it('crea y actualiza categorías', async () => {
    await createCategory('Bebidas');
    await updateCategory(1, 'Bebidas sin alcohol');

    expect(post).toHaveBeenCalledWith('/v1/categories', { name: 'Bebidas' });
    expect(put).toHaveBeenCalledWith('/v1/categories/1', { name: 'Bebidas sin alcohol' });
  });

  it('cambia el estado de una categoría', async () => {
    await updateCategoryStatus(1, 'inactive');

    expect(patch).toHaveBeenCalledWith('/v1/categories/1/status', { status: 'inactive' });
  });

  it('lista y crea marcas', async () => {
    await listBrands();
    await createBrand('Marca X');

    expect(get).toHaveBeenCalledWith('/v1/brands', { params: {} });
    expect(post).toHaveBeenCalledWith('/v1/brands', { name: 'Marca X' });
  });

  it('cambia el estado de una marca', async () => {
    await updateBrandStatus(2, 'inactive');

    expect(patch).toHaveBeenCalledWith('/v1/brands/2/status', { status: 'inactive' });
  });

  it('lista y crea productos con sus filtros', async () => {
    await listProducts({
      page: 1,
      per_page: 10,
      search: 'café',
      status: 'active',
      category_id: 3,
      brand_id: 4,
    });
    await createProduct({
      name: 'Café',
      description: '500 g',
      category_id: 3,
      brand_id: 4,
    });

    expect(get).toHaveBeenCalledWith('/v1/products', {
      params: {
        page: 1,
        per_page: 10,
        search: 'café',
        status: 'active',
        category_id: 3,
        brand_id: 4,
      },
    });
    expect(post).toHaveBeenCalledWith('/v1/products', {
      name: 'Café',
      description: '500 g',
      category_id: 3,
      brand_id: 4,
    });
  });

  it('actualiza y cambia el estado de un producto', async () => {
    await updateProduct(9, {
      name: 'Café',
      description: '',
      category_id: null,
      brand_id: null,
    });
    await updateProductStatus(9, 'inactive');

    expect(put).toHaveBeenCalledWith('/v1/products/9', {
      name: 'Café',
      description: '',
      category_id: null,
      brand_id: null,
    });
    expect(patch).toHaveBeenCalledWith('/v1/products/9/status', { status: 'inactive' });
  });
});
