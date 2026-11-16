import type { AuditLog, Paginated, StockCount, StockCountStatus } from '../types';
import { api } from './api';

export interface StockCountFilters {
  page?: number;
  per_page?: number;
  status?: StockCountStatus | '';
  location_id?: number | '';
}

export interface StockCountInput {
  location_id: number;
  notes: string | null;
  items: Array<{ variant_id: number; counted_quantity: string }>;
}

export interface AuditFilters {
  page?: number;
  per_page?: number;
  action?: string;
  entity_type?: string;
}

function toParams(filters: object): Record<string, string | number> {
  const params: Record<string, string | number> = {};

  Object.entries(filters).forEach(([key, value]) => {
    if (typeof value === 'string' || typeof value === 'number') {
      if (value !== '') {
        params[key] = value;
      }
    }
  });

  return params;
}

export async function listStockCounts(
  filters: StockCountFilters = {},
): Promise<Paginated<StockCount>> {
  const { data } = await api.get<Paginated<StockCount>>('/v1/stock-counts', {
    params: toParams(filters),
  });

  return data;
}

export async function createStockCount(input: StockCountInput): Promise<StockCount> {
  const { data } = await api.post<{ data: StockCount }>('/v1/stock-counts', input);

  return data.data;
}

export async function updateStockCountStatus(
  id: number,
  status: 'reviewing' | 'approved',
  reason: string,
): Promise<StockCount> {
  const { data } = await api.patch<{ data: StockCount }>(
    `/v1/stock-counts/${id}/status`,
    { status, reason },
  );

  return data.data;
}

export async function listAuditEvents(
  filters: AuditFilters = {},
): Promise<Paginated<AuditLog>> {
  const { data } = await api.get<Paginated<AuditLog>>('/v1/audit-events', {
    params: toParams(filters),
  });

  return data;
}
