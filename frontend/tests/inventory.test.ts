import { beforeEach, describe, expect, it, vi } from 'vitest';

const { get, post } = vi.hoisted(() => ({ get: vi.fn(), post: vi.fn() }));

vi.mock('../src/services/api', () => ({
  api: { get, post },
}));

import {
  createAdjustment,
  createEntry,
  createExit,
  createReturn,
  listMovements,
  listStock,
  reverseMovement,
} from '../src/services/inventory';

const movement = {
  id: 1,
  type: 'entry',
  status: 'confirmed',
  reason: 'Ingreso',
  lines: [],
};

const page = {
  data: [],
  meta: { current_page: 1, last_page: 1, per_page: 10, total: 0 },
};

describe('inventory service', () => {
  beforeEach(() => {
    get.mockReset().mockResolvedValue({ data: page });
    post.mockReset().mockResolvedValue({ data: { data: movement } });
  });

  it('lista existencias y movimientos', async () => {
    await listStock({ page: 1, per_page: 10, search: 'café' });
    await listMovements({ page: 1, per_page: 10, type: 'entry' });

    expect(get).toHaveBeenCalledWith('/v1/inventory/stocks', {
      params: { page: 1, per_page: 10, search: 'café' },
    });
    expect(get).toHaveBeenCalledWith('/v1/inventory/movements', {
      params: { page: 1, per_page: 10, type: 'entry' },
    });
  });

  it('registra una entrada con Idempotency-Key', async () => {
    const input = {
      variant_id: 1,
      location_id: 1,
      unit_id: null,
      quantity: '10',
      reason: 'Ingreso',
      document_reference: null,
    };

    await createEntry(input);

    expect(post).toHaveBeenCalledWith('/v1/inventory/entries', input, {
      headers: { 'Idempotency-Key': expect.any(String) },
    });
  });

  it('registra salidas, devoluciones y ajustes', async () => {
    const base = {
      variant_id: 1,
      location_id: 1,
      unit_id: null,
      quantity: '2',
      reason: 'Movimiento',
      document_reference: null,
    };

    await createExit(base);
    await createReturn({ ...base, original_movement_id: 5 });
    await createAdjustment({ ...base, direction: 'out' });

    expect(post).toHaveBeenCalledWith('/v1/inventory/exits', base, {
      headers: { 'Idempotency-Key': expect.any(String) },
    });
    expect(post).toHaveBeenCalledWith('/v1/inventory/returns', {
      ...base,
      original_movement_id: 5,
    }, {
      headers: { 'Idempotency-Key': expect.any(String) },
    });
    expect(post).toHaveBeenCalledWith('/v1/inventory/adjustments', {
      ...base,
      direction: 'out',
    }, {
      headers: { 'Idempotency-Key': expect.any(String) },
    });
  });

  it('revierte un movimiento', async () => {
    await reverseMovement(5, 'Error de captura');

    expect(post).toHaveBeenCalledWith('/v1/inventory/movements/5/reverse', {
      reason: 'Error de captura',
    });
  });
});
