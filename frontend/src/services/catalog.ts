import type {
  CatalogItem,
  CatalogStatus,
  Paginated,
  Product,
  ProductVariant,
} from '../types';
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

export interface ProductFilters {
  page?: number;
  per_page?: number;
  search?: string;
  status?: CatalogStatus | '';
  category_id?: number | '';
  brand_id?: number | '';
}

export interface ProductInput {
  name: string;
  description: string;
  category_id: number | null;
  brand_id: number | null;
}

function toProductParams(filters: ProductFilters): Record<string, string | number> {
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
  if (filters.category_id) {
    params.category_id = filters.category_id;
  }
  if (filters.brand_id) {
    params.brand_id = filters.brand_id;
  }

  return params;
}

export async function listProducts(
  filters: ProductFilters = {},
): Promise<Paginated<Product>> {
  const { data } = await api.get<Paginated<Product>>('/v1/products', {
    params: toProductParams(filters),
  });

  return data;
}

export async function createProduct(input: ProductInput): Promise<Product> {
  const { data } = await api.post<{ data: Product }>('/v1/products', input);

  return data.data;
}

export async function updateProduct(id: number, input: ProductInput): Promise<Product> {
  const { data } = await api.put<{ data: Product }>(`/v1/products/${id}`, input);

  return data.data;
}

export async function updateProductStatus(
  id: number,
  status: CatalogStatus,
): Promise<Product> {
  const { data } = await api.patch<{ data: Product }>(`/v1/products/${id}/status`, {
    status,
  });

  return data.data;
}

export interface VariantFilters {
  page?: number;
  per_page?: number;
  search?: string;
  status?: CatalogStatus | '';
}

export interface VariantInput {
  name: string;
  sku: string;
  barcode: string | null;
  cost: string;
  price: string;
}

function toVariantParams(filters: VariantFilters): Record<string, string | number> {
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

export async function listVariants(
  productId: number,
  filters: VariantFilters = {},
): Promise<Paginated<ProductVariant>> {
  const { data } = await api.get<Paginated<ProductVariant>>(
    `/v1/products/${productId}/variants`,
    { params: toVariantParams(filters) },
  );

  return data;
}

export async function listAllVariants(
  filters: CatalogFilters = {},
): Promise<Paginated<ProductVariant>> {
  const { data } = await api.get<Paginated<ProductVariant>>('/v1/variants', {
    params: toParams(filters),
  });

  return data;
}

export async function createVariant(
  productId: number,
  input: VariantInput,
): Promise<ProductVariant> {
  const { data } = await api.post<{ data: ProductVariant }>(
    `/v1/products/${productId}/variants`,
    input,
  );

  return data.data;
}

export async function updateVariant(
  id: number,
  input: VariantInput,
): Promise<ProductVariant> {
  const { data } = await api.put<{ data: ProductVariant }>(`/v1/variants/${id}`, input);

  return data.data;
}

export async function updateVariantStatus(
  id: number,
  status: CatalogStatus,
): Promise<ProductVariant> {
  const { data } = await api.patch<{ data: ProductVariant }>(`/v1/variants/${id}/status`, {
    status,
  });

  return data.data;
}
