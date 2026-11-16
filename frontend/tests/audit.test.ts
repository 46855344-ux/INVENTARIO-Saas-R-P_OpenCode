import { beforeEach, describe, expect, it, vi } from 'vitest';

const { get, post, patch } = vi.hoisted(() => ({ get: vi.fn(), post: vi.fn(), patch: vi.fn() }));

vi.mock('../src/services/api', () => ({ api: { get, post, patch } }));

import { createStockCount, listAuditEvents, listStockCounts, updateStockCountStatus } from '../src/services/audit';

const page = { data: [], meta: { current_page: 1, last_page: 1, per_page: 10, total: 0 } };

describe('audit service', () => {
  beforeEach(() => {
    get.mockReset().mockResolvedValue({ data: page });
    post.mockReset().mockResolvedValue({ data: { data: {} } });
    patch.mockReset().mockResolvedValue({ data: { data: {} } });
  });

  it('lista conteos y auditoría', async () => {
    await listStockCounts({ status: 'open' });
    await listAuditEvents({ action: 'stock_count.approved' });

    expect(get).toHaveBeenCalledWith('/v1/stock-counts', { params: { status: 'open' } });
    expect(get).toHaveBeenCalledWith('/v1/audit-events', {
      params: { action: 'stock_count.approved' },
    });
  });

  it('crea y aprueba conteos', async () => {
    const input = {
      location_id: 1,
      notes: null,
      items: [{ variant_id: 2, counted_quantity: '8' }],
    };

    await createStockCount(input);
    await updateStockCountStatus(3, 'approved', 'Diferencia verificada');

    expect(post).toHaveBeenCalledWith('/v1/stock-counts', input);
    expect(patch).toHaveBeenCalledWith('/v1/stock-counts/3/status', {
      status: 'approved',
      reason: 'Diferencia verificada',
    });
  });
});
