import type {
  CatalogStatus,
  Paginated,
  Purchase,
  PurchaseStatus,
  Supplier,
  Transfer,
} from '../types';
import { api } from './api';

export interface SupplierFilters {
  page?: number;
  per_page?: number;
  search?: string;
  status?: CatalogStatus | '';
}

export interface SupplierInput {
  name: string;
  tax_id: string | null;
  email: string | null;
  phone: string | null;
}

export interface PurchaseFilters {
  page?: number;
  per_page?: number;
  search?: string;
  status?: PurchaseStatus | '';
  supplier_id?: number | '';
}

export interface PurchaseLineInput {
  variant_id: number;
  location_id: number;
  quantity: string;
  unit_cost: string;
}

export interface PurchaseInput {
  supplier_id: number;
  ordered_at: string;
  document_number: string | null;
  notes: string | null;
  items: PurchaseLineInput[];
}

export interface TransferFilters {
  page?: number;
  per_page?: number;
  from_location_id?: number | '';
  to_location_id?: number | '';
}

export interface TransferLineInput {
  variant_id: number;
  quantity: string;
}

export interface TransferInput {
  from_location_id: number;
  to_location_id: number;
  notes: string | null;
  items: TransferLineInput[];
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

export async function listSuppliers(
  filters: SupplierFilters = {},
): Promise<Paginated<Supplier>> {
  const { data } = await api.get<Paginated<Supplier>>('/v1/suppliers', {
    params: toParams(filters),
  });

  return data;
}

export async function createSupplier(input: SupplierInput): Promise<Supplier> {
  const { data } = await api.post<{ data: Supplier }>('/v1/suppliers', input);

  return data.data;
}

export async function updateSupplier(
  id: number,
  input: SupplierInput,
): Promise<Supplier> {
  const { data } = await api.put<{ data: Supplier }>(`/v1/suppliers/${id}`, input);

  return data.data;
}

export async function updateSupplierStatus(
  id: number,
  status: CatalogStatus,
): Promise<Supplier> {
  const { data } = await api.patch<{ data: Supplier }>(`/v1/suppliers/${id}/status`, {
    status,
  });

  return data.data;
}

export async function listPurchases(
  filters: PurchaseFilters = {},
): Promise<Paginated<Purchase>> {
  const { data } = await api.get<Paginated<Purchase>>('/v1/purchases', {
    params: toParams(filters),
  });

  return data;
}

export async function getPurchase(id: number): Promise<Purchase> {
  const { data } = await api.get<{ data: Purchase }>(`/v1/purchases/${id}`);

  return data.data;
}

export async function createPurchase(input: PurchaseInput): Promise<Purchase> {
  const { data } = await api.post<{ data: Purchase }>('/v1/purchases', input);

  return data.data;
}

export async function updatePurchase(
  id: number,
  input: PurchaseInput,
): Promise<Purchase> {
  const { data } = await api.put<{ data: Purchase }>(`/v1/purchases/${id}`, input);

  return data.data;
}

export async function receivePurchase(id: number): Promise<Purchase> {
  const { data } = await api.post<{ data: Purchase }>(
    `/v1/purchases/${id}/receive`,
    {},
    {
      headers: {
        'Idempotency-Key': idempotencyKey(),
      },
    },
  );

  return data.data;
}

export async function listTransfers(
  filters: TransferFilters = {},
): Promise<Paginated<Transfer>> {
  const { data } = await api.get<Paginated<Transfer>>('/v1/transfers', {
    params: toParams(filters),
  });

  return data;
}

export async function getTransfer(id: number): Promise<Transfer> {
  const { data } = await api.get<{ data: Transfer }>(`/v1/transfers/${id}`);

  return data.data;
}

export async function createTransfer(input: TransferInput): Promise<Transfer> {
  const { data } = await api.post<{ data: Transfer }>('/v1/transfers', input, {
    headers: {
      'Idempotency-Key': idempotencyKey(),
    },
  });

  return data.data;
}
