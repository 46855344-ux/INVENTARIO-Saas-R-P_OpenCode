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
  createSupplier,
  createTransfer,
  listPurchases,
  listSuppliers,
  listTransfers,
  receivePurchase,
} from '../src/services/purchasing';

const page = {
  data: [],
  meta: { current_page: 1, last_page: 1, per_page: 10, total: 0 },
};

describe('purchasing service', () => {
  beforeEach(() => {
    get.mockReset().mockResolvedValue({ data: page });
    post.mockReset().mockResolvedValue({ data: { data: {} } });
    put.mockReset().mockResolvedValue({ data: { data: {} } });
    patch.mockReset().mockResolvedValue({ data: { data: {} } });
  });

  it('lista proveedores, compras y traslados con sus filtros', async () => {
    await listSuppliers({ page: 1, per_page: 10, search: 'central', status: 'active' });
    await listPurchases({ page: 1, per_page: 10, status: 'draft' });
    await listTransfers({ page: 1, per_page: 10, from_location_id: 2 });

    expect(get).toHaveBeenCalledWith('/v1/suppliers', {
      params: { page: 1, per_page: 10, search: 'central', status: 'active' },
    });
    expect(get).toHaveBeenCalledWith('/v1/purchases', {
      params: { page: 1, per_page: 10, status: 'draft' },
    });
    expect(get).toHaveBeenCalledWith('/v1/transfers', {
      params: { page: 1, per_page: 10, from_location_id: 2 },
    });
  });

  it('crea un proveedor', async () => {
    const input = {
      name: 'Distribuidora Central',
      tax_id: '30-12345678-9',
      email: null,
      phone: null,
    };

    await createSupplier(input);

    expect(post).toHaveBeenCalledWith('/v1/suppliers', input);
  });

  it('confirma la recepción con Idempotency-Key', async () => {
    await receivePurchase(7);

    expect(post).toHaveBeenCalledWith('/v1/purchases/7/receive', {}, {
      headers: { 'Idempotency-Key': expect.any(String) },
    });
  });

  it('registra un traslado con Idempotency-Key', async () => {
    const input = {
      from_location_id: 1,
      to_location_id: 2,
      notes: null,
      items: [{ variant_id: 5, quantity: '3' }],
    };

    await createTransfer(input);

    expect(post).toHaveBeenCalledWith('/v1/transfers', input, {
      headers: { 'Idempotency-Key': expect.any(String) },
    });
  });
});
