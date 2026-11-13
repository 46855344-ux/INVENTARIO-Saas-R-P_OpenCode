# Plan — Sprint 2 «Catálogo de productos»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-02 — Catálogo de productos
**Documento:** Plan de iteración, decisiones, contratos y pruebas
**Versión:** 1.0 — Sprint 2
**Fecha:** 2026-11-13
**Estado:** US-0201 implementada; US-0202 y US-0203 planificadas

---

## 1. Objetivo

Implementar el catálogo administrable de categorías, marcas, productos,
variantes y códigos, con validaciones en Laravel, autorización por rol,
aislamiento por empresa y pantallas React.

El Sprint 1 dejó operativos la autenticación, los permisos, el `TenantContext`
y el `TenantScope`; este sprint es el primer consumidor real de esa base.

## 2. Decisiones de entrada

| Tema | Decisión | Fundamento |
| --- | --- | --- |
| Nombres internos | Tablas y modelos en inglés: `categories`, `brands`, `products`, `product_variants` | ESTANDAR §4.1–4.2 |
| Unicidad | Nombre único por empresa (`company_id`, `name`) para categorías y marcas; `sku` único por empresa y `barcode` único por empresa (nullable) para variantes | ESTANDAR §4.4 |
| Desactivación | `status = active/inactive`; la API no borra físicamente | US-0201/US-0202 piden desactivar; DD-05 (borrado lógico/auditoría) queda para una decisión posterior |
| Jerarquías | `categories.parent_id` existe en el esquema, pero la gestión de jerarquías queda fuera de US-0201 | Canvas §2 y §5 |
| Permisos | Nuevos códigos `catalog.view` y `catalog.manage` | El catálogo no estaba en la matriz del Sprint 1 |
| Tenant | `BelongsToCompany` + `TenantScope` en todos los modelos del catálogo | US-0104 / ADR-0002 |
| Auditoría | Los cambios relevantes se registrarán cuando se cierre DD-05; en este sprint se documenta la intención | Canvas §8 |

## 3. Historias

### US-0201 — Gestionar categorías y marcas

**Criterios de aceptación**

- Crear, editar, listar y desactivar categorías y marcas.
- Evitar duplicados por empresa.
- Aislar los registros por empresa.

**Implementado:** backend CRUD + estado, políticas, pruebas de API; pantalla
React de catálogo con pestañas de categorías y marcas.

### US-0202 — Registrar y mantener productos

**Criterios de aceptación**

- Crear, consultar, editar y desactivar productos.
- Validar campos obligatorios y códigos únicos.
- No eliminar un producto con historial: restringir o desactivar.

**Planificado:** tabla `products` (`company_id`, `category_id`, `brand_id`,
`name`, `description`, `status`), `CatalogService`, endpoints y pantalla de
productos. La restricción por historial se activará al incorporar movimientos
(Sprint 4).

### US-0203 — Gestionar variantes y códigos

**Criterios de aceptación**

- Un producto puede tener una o más variantes.
- Los códigos no se duplican dentro de la empresa.
- La variante queda disponible para las operaciones de inventario.

**Planificado:** `product_variants` (`company_id`, `product_id`, `sku`,
`barcode`, `attributes`, `cost`, `price`, `status`) con `sku` único por
empresa y `barcode` único nullable. Si se requieren múltiples códigos por
variante, se añadirá `product_variant_codes` en una decisión posterior.

## 4. Diseño técnico (US-0201)

```text
backend/app/Modules/Catalog/
├── Models/{Category,Brand}.php
├── Http/Controllers/{CategoryController,BrandController}.php
├── Http/Requests/...
├── Http/Resources/{CategoryResource,BrandResource}.php
├── Policies/{CategoryPolicy,BrandPolicy}.php
└── Services/CatalogService.php
```

- Migraciones `categories` y `brands` con `company_id`, `name`, `status` y
  unicidad `(company_id, name)`.
- `Category` y `Brand` usan `BelongsToCompany`: filtro global por empresa,
  `company_id` asignado desde el contexto e inmutable.
- Permisos: `catalog.view` y `catalog.manage` (owner/admin administran;
  operator/viewer consultan).
- Rutas con `can:catalog.view` / `can:catalog.manage`, policies por recurso y
  errores unificados (`403 FORBIDDEN`, `404 NOT_FOUND`).

## 5. Contratos API (US-0201)

| Método | Ruta | Permiso |
| --- | --- | --- |
| `GET` | `/api/v1/categories` | `catalog.view` |
| `POST` | `/api/v1/categories` | `catalog.manage` |
| `GET` | `/api/v1/categories/{id}` | `catalog.view` |
| `PUT` | `/api/v1/categories/{id}` | `catalog.manage` |
| `PATCH` | `/api/v1/categories/{id}/status` | `catalog.manage` |
| `GET` | `/api/v1/brands` | `catalog.view` |
| `POST` | `/api/v1/brands` | `catalog.manage` |
| `GET` | `/api/v1/brands/{id}` | `catalog.view` |
| `PUT` | `/api/v1/brands/{id}` | `catalog.manage` |
| `PATCH` | `/api/v1/brands/{id}/status` | `catalog.manage` |

Listados paginados con `search`, `status`, `page` y `per_page` (máximo 100).

## 6. Matriz de pruebas (US-0201)

| ID | Caso | Resultado esperado |
| --- | --- | --- |
| CT-201 | Crear categoría válida | Se guarda en la empresa activa |
| CT-202 | Crear categoría duplicada en la empresa | 422 `VALIDATION_ERROR` |
| CT-203 | Editar y desactivar marca | Cambios persistentes |
| CT-204 | Crear categoría sin nombre | 422 |
| CT-205 | Listar y buscar categorías | Solo la empresa activa |
| CT-206 | Desactivar categoría | No aparece como activa |
| CT-210 | Consultar categoría de otra empresa | 404 `NOT_FOUND` |
| CT-211 | Usuario sin `catalog.manage` | 403 `FORBIDDEN` |
| CT-213 | Mismo nombre en otra empresa | Permitido |
| CT-214 | `company_id` manipulado en el payload | Se ignora y se usa el contexto |

## 7. Definition of Ready / Done

**DoR:** Sprint 1 desplegado; `TenantScope` operativo; permisos de catálogo
definidos; contratos de US-0201 acordados.

**DoD:** criterios de US-0201 cumplidos; migraciones, claves e índices
revisados; autorización y validación en backend; pantalla React con estados de
carga, error y vacío; pruebas de API y UI; documentación actualizada.

## 8. Estado de implementación

**US-0201 — completada el 2026-11-13.**

- Migraciones `categories` y `brands`.
- Modelos con `BelongsToCompany`, factories, requests, resources, policies y
  `CatalogService`.
- Endpoints de categorías y marcas.
- Permisos `catalog.view` / `catalog.manage` en `IdentitySeeder`.
- Pantalla React «Catálogo» con pestañas y gating por permisos.
- Pruebas de API y de frontend.
