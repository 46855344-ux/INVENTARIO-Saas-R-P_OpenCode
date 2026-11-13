import type {
  CatalogStatus,
  Location,
  LocationType,
  Paginated,
  SimulationResult,
  Unit,
  UnitConversion,
  UnitDimension,
} from '../types';
import { api } from './api';

export interface UnitFilters {
  page?: number;
  per_page?: number;
  search?: string;
  status?: CatalogStatus | '';
  dimension?: UnitDimension | '';
}

export interface UnitInput {
  code: string;
  name: string;
  dimension: UnitDimension;
  precision: number;
}

export interface ConversionFilters {
  page?: number;
  per_page?: number;
  status?: CatalogStatus | '';
}

export interface ConversionInput {
  from_unit_id: number;
  to_unit_id: number;
  factor: string;
}

export interface LocationFilters {
  page?: number;
  per_page?: number;
  search?: string;
  status?: CatalogStatus | '';
  type?: LocationType | '';
}

export interface LocationInput {
  code: string;
  name: string;
  type: LocationType;
  parent_id: number | null;
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

export async function listUnits(filters: UnitFilters = {}): Promise<Paginated<Unit>> {
  const { data } = await api.get<Paginated<Unit>>('/v1/units', {
    params: toParams(filters),
  });

  return data;
}

export async function createUnit(input: UnitInput): Promise<Unit> {
  const { data } = await api.post<{ data: Unit }>('/v1/units', input);

  return data.data;
}

export async function updateUnit(id: number, input: UnitInput): Promise<Unit> {
  const { data } = await api.put<{ data: Unit }>(`/v1/units/${id}`, input);

  return data.data;
}

export async function updateUnitStatus(id: number, status: CatalogStatus): Promise<Unit> {
  const { data } = await api.patch<{ data: Unit }>(`/v1/units/${id}/status`, { status });

  return data.data;
}

export async function listConversions(
  filters: ConversionFilters = {},
): Promise<Paginated<UnitConversion>> {
  const { data } = await api.get<Paginated<UnitConversion>>('/v1/conversions', {
    params: toParams(filters),
  });

  return data;
}

export async function createConversion(input: ConversionInput): Promise<UnitConversion> {
  const { data } = await api.post<{ data: UnitConversion }>('/v1/conversions', input);

  return data.data;
}

export async function updateConversion(id: number, factor: string): Promise<UnitConversion> {
  const { data } = await api.put<{ data: UnitConversion }>(`/v1/conversions/${id}`, { factor });

  return data.data;
}

export async function updateConversionStatus(
  id: number,
  status: CatalogStatus,
): Promise<UnitConversion> {
  const { data } = await api.patch<{ data: UnitConversion }>(`/v1/conversions/${id}/status`, {
    status,
  });

  return data.data;
}

export async function simulateConversion(input: {
  from_unit_id: number;
  to_unit_id: number;
  quantity: string;
}): Promise<SimulationResult> {
  const { data } = await api.post<{ data: SimulationResult }>(
    '/v1/conversions/simulate',
    input,
  );

  return data.data;
}

export async function listLocations(
  filters: LocationFilters = {},
): Promise<Paginated<Location>> {
  const { data } = await api.get<Paginated<Location>>('/v1/locations', {
    params: toParams(filters),
  });

  return data;
}

export async function createLocation(input: LocationInput): Promise<Location> {
  const { data } = await api.post<{ data: Location }>('/v1/locations', input);

  return data.data;
}

export async function updateLocation(id: number, input: LocationInput): Promise<Location> {
  const { data } = await api.put<{ data: Location }>(`/v1/locations/${id}`, input);

  return data.data;
}

export async function updateLocationStatus(
  id: number,
  status: CatalogStatus,
): Promise<Location> {
  const { data } = await api.patch<{ data: Location }>(`/v1/locations/${id}/status`, {
    status,
  });

  return data.data;
}
