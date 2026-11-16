import { beforeEach, describe, expect, it, vi } from 'vitest';

const { get, post, patch } = vi.hoisted(() => ({
  get: vi.fn(),
  post: vi.fn(),
  patch: vi.fn(),
}));

vi.mock('../src/services/api', () => ({
  api: { get, post, patch },
}));

import {
  createAlertRule,
  getMetricsOverview,
  listAlerts,
  listStockDetail,
  updateAlertStatus,
} from '../src/services/metrics';

const page = {
  data: [],
  meta: { current_page: 1, last_page: 1, per_page: 10, total: 0 },
};

describe('metrics service', () => {
  beforeEach(() => {
    get.mockReset();
    post.mockReset().mockResolvedValue({ data: { data: {} } });
    patch.mockReset().mockResolvedValue({ data: { data: {} } });
  });

  it('consulta el resumen y el detalle de stock', async () => {
    get.mockResolvedValueOnce({ data: { data: { total_products: 3 } } });
    get.mockResolvedValueOnce({ data: page });

    await getMetricsOverview({ days: 30, location_id: 1 });
    await listStockDetail({ page: 1, per_page: 10, location_id: 1 });

    expect(get).toHaveBeenCalledWith('/v1/metrics/overview', {
      params: { days: 30, location_id: 1 },
    });
    expect(get).toHaveBeenCalledWith('/v1/metrics/stock-detail', {
      params: { page: 1, per_page: 10, location_id: 1 },
    });
  });

  it('lista alertas y cambia su estado', async () => {
    get.mockResolvedValueOnce({ data: page });
    await listAlerts({ status: 'open', type: 'min_stock' });

    await updateAlertStatus(4, 'resolved');

    expect(get).toHaveBeenCalledWith('/v1/alerts', {
      params: { status: 'open', type: 'min_stock' },
    });
    expect(patch).toHaveBeenCalledWith('/v1/alerts/4/status', {
      status: 'resolved',
    });
  });

  it('crea reglas de alerta', async () => {
    const input = {
      type: 'min_stock' as const,
      variant_id: 2,
      location_id: 3,
      threshold: '5',
      inactivity_days: null,
    };

    await createAlertRule(input);

    expect(post).toHaveBeenCalledWith('/v1/alert-rules', input);
  });
});
