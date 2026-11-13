/** Contrato de error único de la API (ver ESTANDAR.md, sección 6.8). */
export interface ApiErrorPayload {
  error: {
    code: string;
    message: string;
    details?: Record<string, unknown>;
    trace_id?: string;
  };
}

export interface PaginatedMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface Paginated<T> {
  data: T[];
  meta: PaginatedMeta;
}

export type CompanyId = number;

/** Usuario autenticado (contrato de `GET /api/v1/auth/me`). */
export interface AuthUser {
  id: number;
  name: string;
  email: string;
}

/** Rol resumido que el usuario tiene dentro de una empresa. */
export interface RoleSummary {
  id: number;
  code: string;
  name: string;
}

/** Rol disponible para la empresa activa. */
export interface Role extends RoleSummary {
  description: string | null;
  is_global: boolean;
  permissions: string[];
}

/** Permiso del catálogo global (`modulo.accion`). */
export interface Permission {
  code: string;
  name: string;
  description: string | null;
}

/** Empresa a la que pertenece el usuario, con su rol y permisos en ella. */
export interface Company {
  id: number;
  name: string;
  slug: string;
  role: RoleSummary | null;
  permissions: string[];
}

/** Perfil de sesión devuelto por login y `me`. */
export interface AuthSession {
  user: AuthUser;
  companies: Company[];
  permissions: string[];
}

/** Usuario dentro de la empresa activa. */
export interface Member {
  id: number;
  name: string;
  email: string;
  status: 'active' | 'inactive';
  role: Role | null;
}

export type CatalogStatus = 'active' | 'inactive';

/** Elemento del catálogo (categoría o marca). */
export interface CatalogItem {
  id: number;
  name: string;
  status: CatalogStatus;
  parent_id?: number | null;
  created_at?: string;
  updated_at?: string;
}
