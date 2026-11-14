import type {
  Paginated,
  StockBalance,
  StockMovement,
  StockMovementType,
} from '../types';
import { api } from './api';

export interface StockFilters {
  page?: number;
  per_page?: number;
  search?: string;
  variant_id?: number | '';
  location_id?: number | '';
}

export interface MovementFilters {
  page?: number;
  per_page?: number;
  type?: StockMovementType | '';
  status?: 'confirmed' | '';
}

export interface MovementInput {
  variant_id: number;
  location_id: number;
  unit_id: number | null;
  quantity: string;
  reason: string;
  document_reference: string | null;
}

export interface ReturnInput extends MovementInput {
  original_movement_id: number | null;
}

export interface AdjustmentInput extends MovementInput {
  direction: 'in' | 'out';
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

function idempotencyKey(): string {
  if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
    return crypto.randomUUID();
  }

  return `${Date.now()}-${Math.random().toString(36).slice(2)}`;
}

async function postMovement(path: string, body: object): Promise<StockMovement> {
  const { data } = await api.post<{ data: StockMovement }>(path, body, {
    headers: {
      'Idempotency-Key': idempotencyKey(),
    },
  });

  return data.data;
}

export async function listStock(filters: StockFilters = {}): Promise<Paginated<StockBalance>> {
  const { data } = await api.get<Paginated<StockBalance>>('/v1/inventory/stocks', {
    params: toParams(filters),
  });

  return data;
}

export async function listMovements(
  filters: MovementFilters = {},
): Promise<Paginated<StockMovement>> {
  const { data } = await api.get<Paginated<StockMovement>>('/v1/inventory/movements', {
    params: toParams(filters),
  });

  return data;
}

export async function getMovement(id: number): Promise<StockMovement> {
  const { data } = await api.get<{ data: StockMovement }>(`/v1/inventory/movements/${id}`);

  return data.data;
}

export async function createEntry(input: MovementInput): Promise<StockMovement> {
  return postMovement('/v1/inventory/entries', input);
}

export async function createExit(input: MovementInput): Promise<StockMovement> {
  return postMovement('/v1/inventory/exits', input);
}

export async function createReturn(input: ReturnInput): Promise<StockMovement> {
  return postMovement('/v1/inventory/returns', input);
}

export async function createAdjustment(input: AdjustmentInput): Promise<StockMovement> {
  return postMovement('/v1/inventory/adjustments', input);
}

export async function reverseMovement(id: number, reason: string): Promise<StockMovement> {
  const { data } = await api.post<{ data: StockMovement }>(
    `/v1/inventory/movements/${id}/reverse`,
    { reason },
  );

  return data.data;
}
