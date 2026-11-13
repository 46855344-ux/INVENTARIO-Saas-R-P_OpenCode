import type { CatalogItem, CatalogStatus, Paginated } from '../types';
import { api } from './api';

export interface CatalogFilters {
  page?: number;
  per_page?: number;
  search?: string;
  status?: CatalogStatus | '';
}

function toParams(filters: CatalogFilters): Record<string, string | number> {
  const params: Record<string, string | number> = {};

  if (filters.page) {
    params.page = filters.page;
  }
  if (filters.per_page) {
    params.per_page = filters.per_page;
  }
  if (filters.search) {
    params.search = filters.search;
  }
  if (filters.status) {
    params.status = filters.status;
  }

  return params;
}

export async function listCategories(filters: CatalogFilters = {}): Promise<Paginated<CatalogItem>> {
  const { data } = await api.get<Paginated<CatalogItem>>('/v1/categories', {
    params: toParams(filters),
  });

  return data;
}

export async function createCategory(name: string): Promise<CatalogItem> {
  const { data } = await api.post<{ data: CatalogItem }>('/v1/categories', { name });

  return data.data;
}

export async function updateCategory(id: number, name: string): Promise<CatalogItem> {
  const { data } = await api.put<{ data: CatalogItem }>(`/v1/categories/${id}`, { name });

  return data.data;
}

export async function updateCategoryStatus(
  id: number,
  status: CatalogStatus,
): Promise<CatalogItem> {
  const { data } = await api.patch<{ data: CatalogItem }>(`/v1/categories/${id}/status`, {
    status,
  });

  return data.data;
}

export async function listBrands(filters: CatalogFilters = {}): Promise<Paginated<CatalogItem>> {
  const { data } = await api.get<Paginated<CatalogItem>>('/v1/brands', {
    params: toParams(filters),
  });

  return data;
}

export async function createBrand(name: string): Promise<CatalogItem> {
  const { data } = await api.post<{ data: CatalogItem }>('/v1/brands', { name });

  return data.data;
}

export async function updateBrand(id: number, name: string): Promise<CatalogItem> {
  const { data } = await api.put<{ data: CatalogItem }>(`/v1/brands/${id}`, { name });

  return data.data;
}

export async function updateBrandStatus(
  id: number,
  status: CatalogStatus,
): Promise<CatalogItem> {
  const { data } = await api.patch<{ data: CatalogItem }>(`/v1/brands/${id}/status`, {
    status,
  });

  return data.data;
}
