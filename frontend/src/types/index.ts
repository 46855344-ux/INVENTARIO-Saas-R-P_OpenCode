/** Contrato de error único de la API (ver ESTANDAR.md, sección 6.8). */
export interface ApiErrorPayload {
  error: {
    code: string;
    message: string;
    details?: Record<string, unknown>;
    trace_id?: string;
  };
}

export interface Paginated<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export type CompanyId = number;

/** Usuario autenticado (contrato de `GET /api/v1/auth/me`). */
export interface AuthUser {
  id: number;
  name: string;
  email: string;
}

/** Empresa a la que pertenece el usuario y que puede activar en la sesión. */
export interface Company {
  id: number;
  name: string;
  slug: string;
}

/** Perfil de sesión devuelto por login y `me`. */
export interface AuthSession {
  user: AuthUser;
  companies: Company[];
  permissions: string[];
}
