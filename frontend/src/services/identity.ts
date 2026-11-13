import type { Member, Paginated, Permission, Role } from '../types';
import { api } from './api';

export interface UserFilters {
  page?: number;
  per_page?: number;
  search?: string;
  status?: 'active' | 'inactive' | '';
  role_id?: number | '';
}

export interface CreateMemberInput {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  role_id: number;
}

export interface UpdateMemberInput {
  name?: string;
  email?: string;
  password?: string;
  password_confirmation?: string;
}

function toParams(filters: UserFilters): Record<string, string | number> {
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
  if (filters.role_id) {
    params.role_id = filters.role_id;
  }

  return params;
}

export async function listUsers(filters: UserFilters = {}): Promise<Paginated<Member>> {
  const { data } = await api.get<Paginated<Member>>('/v1/users', {
    params: toParams(filters),
  });

  return data;
}

export async function createUser(input: CreateMemberInput): Promise<Member> {
  const { data } = await api.post<{ data: Member }>('/v1/users', input);

  return data.data;
}

export async function updateUser(id: number, input: UpdateMemberInput): Promise<Member> {
  const { data } = await api.put<{ data: Member }>(`/v1/users/${id}`, input);

  return data.data;
}

export async function updateUserStatus(
  id: number,
  status: 'active' | 'inactive',
): Promise<Member> {
  const { data } = await api.patch<{ data: Member }>(`/v1/users/${id}/status`, { status });

  return data.data;
}

export async function assignUserRole(id: number, roleId: number): Promise<Member> {
  const { data } = await api.put<{ data: Member }>(`/v1/users/${id}/role`, {
    role_id: roleId,
  });

  return data.data;
}

export async function listRoles(): Promise<Role[]> {
  const { data } = await api.get<Paginated<Role>>('/v1/roles', {
    params: { per_page: 100 },
  });

  return data.data;
}

export async function listPermissions(): Promise<Permission[]> {
  const { data } = await api.get<Paginated<Permission>>('/v1/permissions', {
    params: { per_page: 100 },
  });

  return data.data;
}
