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

export interface CatalogReference {
  id: number;
  name: string;
}

/** Producto del catálogo. */
export interface Product {
  id: number;
  name: string;
  description: string | null;
  status: CatalogStatus;
  category_id: number | null;
  brand_id: number | null;
  category: CatalogReference | null;
  brand: CatalogReference | null;
  created_at?: string;
  updated_at?: string;
}

/** Variante de un producto. */
export interface ProductVariant {
  id: number;
  product_id: number;
  name: string;
  sku: string;
  barcode: string | null;
  attributes: Record<string, unknown> | null;
  cost: string;
  price: string;
  status: CatalogStatus;
  created_at?: string;
  updated_at?: string;
}

export type UnitDimension = 'mass' | 'volume' | 'count' | 'length' | 'time' | 'other';

export interface Unit {
  id: number;
  code: string;
  name: string;
  dimension: UnitDimension;
  precision: number;
  status: CatalogStatus;
  created_at?: string;
  updated_at?: string;
}

export interface UnitSummary {
  id: number;
  code: string;
  name: string;
  dimension: UnitDimension;
  precision?: number;
}

export interface UnitConversion {
  id: number;
  from_unit_id: number;
  to_unit_id: number;
  factor: string;
  status: CatalogStatus;
  from_unit: UnitSummary | null;
  to_unit: UnitSummary | null;
  created_at?: string;
  updated_at?: string;
}

export interface SimulationResult {
  from_unit: UnitSummary;
  to_unit: UnitSummary;
  quantity: string;
  factor: string;
  result: string;
  rule: string;
  rounding: string;
  scale: number;
}

export type LocationType = 'warehouse' | 'zone' | 'shelf' | 'bin' | 'other';

export interface LocationSummary {
  id: number;
  code: string;
  name: string;
  type: LocationType;
}

export interface Location {
  id: number;
  code: string;
  name: string;
  type: LocationType;
  parent_id: number | null;
  status: CatalogStatus;
  parent: LocationSummary | null;
  created_at?: string;
  updated_at?: string;
}

export type StockMovementType =
  | 'entry'
  | 'exit'
  | 'return'
  | 'adjustment'
  | 'transfer_in'
  | 'transfer_out'
  | 'reversal';

export interface StockBalance {
  id: number;
  variant_id: number;
  location_id: number;
  quantity: string;
  reserved_quantity: string;
  variant: {
    id: number;
    sku: string;
    name: string;
    product: { id: number; name: string } | null;
  } | null;
  location: { id: number; code: string; name: string } | null;
  updated_at?: string;
}

export interface StockMovementLine {
  id: number;
  variant_id: number;
  location_id: number;
  unit_id: number | null;
  quantity: string;
  normalized_quantity: string;
  direction: 'in' | 'out';
  variant: { id: number; sku: string; name: string } | null;
  location: { id: number; code: string; name: string } | null;
  unit: { id: number; code: string; name: string } | null;
}

export interface StockMovement {
  id: number;
  type: StockMovementType;
  status: string;
  reason: string;
  document_reference: string | null;
  reference_type: string | null;
  reference_id: number | null;
  original_movement_id: number | null;
  reversal_of_movement_id: number | null;
  confirmed_at: string | null;
  created_at: string | null;
  user: { id: number; name: string } | null;
  lines: StockMovementLine[];
  reversal: { id: number; type: string; confirmed_at: string | null } | null;
}

export type PurchaseStatus = 'draft' | 'received';

export type SocialProvider = 'google' | 'facebook';

export interface SocialProviderOption {
  provider: SocialProvider;
  label: string;
}

export interface SocialIdentity {
  provider: SocialProvider;
  label: string;
  linked_at: string | null;
}

/** Proveedor de la empresa activa. */
export interface Supplier {
  id: number;
  name: string;
  tax_id: string | null;
  email: string | null;
  phone: string | null;
  status: CatalogStatus;
  created_at?: string;
  updated_at?: string;
}

/** Línea de compra con costo histórico. */
export interface PurchaseItem {
  id: number;
  variant_id: number;
  location_id: number;
  quantity: string;
  unit_cost: string;
  subtotal: string;
  variant: { id: number; sku: string; name: string } | null;
  location: { id: number; code: string; name: string } | null;
}

/** Compra a proveedor. */
export interface Purchase {
  id: number;
  status: PurchaseStatus;
  document_number: string | null;
  ordered_at: string | null;
  notes: string | null;
  total: string;
  received_at: string | null;
  supplier: { id: number; name: string; tax_id: string | null } | null;
  user: { id: number; name: string } | null;
  received_by: { id: number; name: string } | null;
  items: PurchaseItem[];
  created_at?: string;
  updated_at?: string;
}

/** Línea de un traslado. */
export interface TransferItem {
  id: number;
  variant_id: number;
  quantity: string;
  variant: { id: number; sku: string; name: string } | null;
}

/** Traslado confirmado entre ubicaciones. */
export interface Transfer {
  id: number;
  status: 'confirmed';
  notes: string | null;
  confirmed_at: string | null;
  from_location: { id: number; code: string; name: string } | null;
  to_location: { id: number; code: string; name: string } | null;
  user: { id: number; name: string } | null;
  items: TransferItem[];
  created_at?: string;
}
