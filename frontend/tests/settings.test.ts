import { beforeEach, describe, expect, it, vi } from 'vitest';

const { get, post, put, patch } = vi.hoisted(() => ({
  get: vi.fn(),
  post: vi.fn(),
  put: vi.fn(),
  patch: vi.fn(),
}));

vi.mock('../src/services/api', () => ({
  api: { get, post, put, patch },
}));

import {
  createConversion,
  createLocation,
  createUnit,
  listConversions,
  listLocations,
  listUnits,
  simulateConversion,
  updateConversion,
  updateLocationStatus,
  updateUnitStatus,
} from '../src/services/settings';

const page = {
  data: [],
  meta: { current_page: 1, last_page: 1, per_page: 10, total: 0 },
};

const unit = {
  id: 1,
  code: 'KG',
  name: 'Kilogramo',
  dimension: 'mass',
  precision: 3,
  status: 'active',
};

describe('settings service', () => {
  beforeEach(() => {
    get.mockReset().mockResolvedValue({ data: page });
    post.mockReset().mockResolvedValue({ data: { data: unit } });
    put.mockReset().mockResolvedValue({ data: { data: unit } });
    patch.mockReset().mockResolvedValue({ data: { data: unit } });
  });

  it('lista unidades con sus filtros', async () => {
    await listUnits({ page: 2, per_page: 10, search: 'kg', status: 'active', dimension: 'mass' });

    expect(get).toHaveBeenCalledWith('/v1/units', {
      params: { page: 2, per_page: 10, search: 'kg', status: 'active', dimension: 'mass' },
    });
  });

  it('crea una unidad y cambia su estado', async () => {
    await createUnit({ code: 'KG', name: 'Kilogramo', dimension: 'mass', precision: 3 });
    await updateUnitStatus(1, 'inactive');

    expect(post).toHaveBeenCalledWith('/v1/units', {
      code: 'KG',
      name: 'Kilogramo',
      dimension: 'mass',
      precision: 3,
    });
    expect(patch).toHaveBeenCalledWith('/v1/units/1/status', { status: 'inactive' });
  });

  it('lista, crea y actualiza conversiones', async () => {
    await listConversions({ status: 'active' });
    await createConversion({ from_unit_id: 1, to_unit_id: 2, factor: '1000' });
    await updateConversion(5, '2.5');

    expect(get).toHaveBeenCalledWith('/v1/conversions', { params: { status: 'active' } });
    expect(post).toHaveBeenCalledWith('/v1/conversions', {
      from_unit_id: 1,
      to_unit_id: 2,
      factor: '1000',
    });
    expect(put).toHaveBeenCalledWith('/v1/conversions/5', { factor: '2.5' });
  });

  it('simula una conversión', async () => {
    await simulateConversion({ from_unit_id: 1, to_unit_id: 2, quantity: '2.5' });

    expect(post).toHaveBeenCalledWith('/v1/conversions/simulate', {
      from_unit_id: 1,
      to_unit_id: 2,
      quantity: '2.5',
    });
  });

  it('lista, crea y cambia el estado de ubicaciones', async () => {
    await listLocations({ type: 'warehouse' });
    await createLocation({
      code: 'ALM-01',
      name: 'Almacén',
      type: 'warehouse',
      parent_id: null,
    });
    await updateLocationStatus(3, 'inactive');

    expect(get).toHaveBeenCalledWith('/v1/locations', { params: { type: 'warehouse' } });
    expect(post).toHaveBeenCalledWith('/v1/locations', {
      code: 'ALM-01',
      name: 'Almacén',
      type: 'warehouse',
      parent_id: null,
    });
    expect(patch).toHaveBeenCalledWith('/v1/locations/3/status', { status: 'inactive' });
  });
});
