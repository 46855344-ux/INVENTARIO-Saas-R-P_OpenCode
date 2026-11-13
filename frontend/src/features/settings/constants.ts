import type { LocationType, UnitDimension } from '../../types';

export const UNIT_DIMENSIONS: Array<{ value: UnitDimension; label: string }> = [
  { value: 'mass', label: 'Masa' },
  { value: 'volume', label: 'Volumen' },
  { value: 'count', label: 'Conteo' },
  { value: 'length', label: 'Longitud' },
  { value: 'time', label: 'Tiempo' },
  { value: 'other', label: 'Otra' },
];

export const LOCATION_TYPES: Array<{ value: LocationType; label: string }> = [
  { value: 'warehouse', label: 'Almacén' },
  { value: 'zone', label: 'Zona' },
  { value: 'shelf', label: 'Estante' },
  { value: 'bin', label: 'Bandeja' },
  { value: 'other', label: 'Otra' },
];

export function dimensionLabel(dimension: UnitDimension): string {
  return UNIT_DIMENSIONS.find((item) => item.value === dimension)?.label ?? dimension;
}

export function locationTypeLabel(type: LocationType): string {
  return LOCATION_TYPES.find((item) => item.value === type)?.label ?? type;
}
