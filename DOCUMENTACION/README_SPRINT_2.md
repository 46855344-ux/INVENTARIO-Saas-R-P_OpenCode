# README — Sprint 2 «Catálogo de productos»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-02 — Catálogo de productos
**Iteración:** Sprint 2
**Versión del documento:** 1.0
**Fecha:** 2026-11-13
**Estado:** Cerrado técnicamente — validación con MySQL/Docker pendiente
**Canvas de referencia:** [`DOCS_origen/CANVAS_SPRINT_2_CATALOGO_PRODUCTOS.md`](./DOCS_origen/CANVAS_SPRINT_2_CATALOGO_PRODUCTOS.md)
**Plan del sprint:** [`SDD/PLAN_SPRINT_2_CATALOGO.md`](./SDD/PLAN_SPRINT_2_CATALOGO.md)

---

## 1. Resumen ejecutivo

El Sprint 2 implementó el **catálogo administrable** de categorías, marcas,
productos y variantes, con validaciones en Laravel, autorización por rol,
aislamiento por empresa y pantallas React. Se completaron las tres historias de
la épica EP-02:

| Historia | Objetivo | Resultado |
| --- | --- | --- |
| US-0201 | Gestionar categorías y marcas | **Completado** |
| US-0202 | Registrar y mantener productos | **Completado** |
| US-0203 | Gestionar variantes y códigos | **Completado** |

## 2. Contexto de entrada

- Sprint 1 cerrado: autenticación, usuarios, roles, permisos y `TenantScope`.
- Permisos de catálogo definidos: `catalog.view` y `catalog.manage`.
- Regla de idioma del proyecto: tablas y modelos en inglés; interfaz en español.
- DD-05 (borrado lógico/auditoría) sigue como propuesta; este sprint usa
  desactivación por estado y no borra físicamente.

## 3. Decisiones de entrada

| Tema | Decisión | Fundamento |
| --- | --- | --- |
| Nombres internos | `categories`, `brands`, `products`, `product_variants` | ESTANDAR §4.1–4.2 |
| Unicidad | Nombre único por empresa; SKU único por empresa; código de barras único nullable por empresa | ESTANDAR §4.4 |
| Desactivación | `status = active/inactive`; sin borrado físico | Criterios de US-0201/US-0202 |
| Jerarquías | `categories.parent_id` existe, pero su gestión queda fuera de US-0201 | Canvas §2 |
| Permisos | `catalog.view` / `catalog.manage` | El catálogo no estaba en la matriz del Sprint 1 |
| Tenant | `BelongsToCompany` + `TenantScope` en todos los modelos | US-0104 / ADR-0002 |
| Auditoría | Se incorporará al cerrar DD-05 | Canvas §8 |

## 4. Modelo de datos y arquitectura

| Entidad | Tabla | Campos principales |
| --- | --- | --- |
| Categoría | `categories` | `id`, `company_id`, `parent_id`, `name`, `status` |
| Marca | `brands` | `id`, `company_id`, `name`, `status` |
| Producto | `products` | `id`, `company_id`, `category_id`, `brand_id`, `name`, `description`, `status` |
| Variante | `product_variants` | `id`, `company_id`, `product_id`, `name`, `sku`, `barcode`, `attributes`, `cost`, `price`, `status` |

- Restricciones: `UNIQUE (company_id, name)` en categorías, marcas y productos;
  `UNIQUE (company_id, sku)` y `UNIQUE (company_id, barcode)` en variantes.
- `CatalogService` concentra las reglas de categorías, marcas, productos y
  variantes.
- `Category`, `Brand`, `Product` y `ProductVariant` usan `BelongsToCompany`:
  filtro global, `company_id` desde el contexto e inmutabilidad.
- Policies por recurso (`CategoryPolicy`, `BrandPolicy`, `ProductPolicy`,
  `ProductVariantPolicy`) y rutas con `can:`.

## 5. Historias y criterios de aceptación

| Historia | Criterios cubiertos | Evidencia |
| --- | --- | --- |
| US-0201 | Crear, listar, editar y desactivar categorías/marcas; unicidad por empresa; aislamiento | `tests/Feature/Catalog/CatalogManagementTest.php` |
| US-0202 | Crear, consultar, editar y desactivar productos; campos obligatorios; categoría/marca de la misma empresa | `tests/Feature/Catalog/ProductManagementTest.php` |
| US-0203 | Variantes por producto; SKU y código de barras únicos; aislamiento | `tests/Feature/Catalog/ProductVariantManagementTest.php` |

## 6. Contratos API

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
| `GET` | `/api/v1/products` | `catalog.view` |
| `POST` | `/api/v1/products` | `catalog.manage` |
| `GET` | `/api/v1/products/{id}` | `catalog.view` |
| `PUT` | `/api/v1/products/{id}` | `catalog.manage` |
| `PATCH` | `/api/v1/products/{id}/status` | `catalog.manage` |
| `GET` | `/api/v1/products/{id}/variants` | `catalog.view` |
| `POST` | `/api/v1/products/{id}/variants` | `catalog.manage` |
| `GET` | `/api/v1/variants/{id}` | `catalog.view` |
| `PUT` | `/api/v1/variants/{id}` | `catalog.manage` |
| `PATCH` | `/api/v1/variants/{id}/status` | `catalog.manage` |

Todos los endpoints de tenant requieren `auth:sanctum` y `X-Company-Id`.

## 7. Entregables y evidencia

| Entregable | Ubicación | Estado |
| --- | --- | --- |
| Migraciones de catálogo | `backend/database/migrations/2026_11_13_07*.php` | Completo |
| Modelos, policies, resources y service | `backend/app/Modules/Catalog/` | Completo |
| API de categorías, marcas, productos y variantes | `backend/routes/api.php` | Completo |
| Pantalla React «Catálogo» con pestañas | `frontend/src/features/products/` | Completo |
| Permisos `catalog.view`/`catalog.manage` | `backend/database/seeders/IdentitySeeder.php` | Completo |
| Pruebas de API y UI | `backend/tests/Feature/Catalog/`, `frontend/tests/` | Completo |
| Documentación de API y modelo | `SDD-04`, `SDD-05`, plan del sprint | Completo |

## 8. Evidencia de pruebas

| Verificación | Comando | Resultado |
| --- | --- | --- |
| Pruebas backend | `php artisan test` | 65 pruebas, 212 aserciones — OK |
| Estilo backend | `vendor/bin/pint --test` | Sin diferencias — OK |
| Pruebas frontend | `npm run test` | 33 pruebas — OK |
| Lint frontend | `npm run lint` | Sin errores — OK |
| Build frontend | `npm run build` | Compilado — OK |

Matriz del sprint: CT-201 a CT-214 (categorías/marcas), validaciones y
aislamiento de productos, y unicidad/referencias de variantes. CT-212 (producto
con historial) se activará cuando existan movimientos de inventario (Sprint 4).

## 9. Definition of Done — estado

- [x] US-0201, US-0202 y US-0203 cumplen sus criterios de aceptación.
- [x] Migraciones, índices, claves foráneas y restricciones implementados.
- [x] Laravel centraliza las reglas; React consume la API con carga, error y vacío.
- [x] Validaciones y autorización de backend activas.
- [x] Pruebas de catálogo y aislamiento pasan en local.
- [x] Documentación de API y modelo actualizada.
- [ ] Validación contra MySQL real y `docker compose` — pendiente por entorno.
- [ ] Restricción de borrado por historial — se verifica al integrar movimientos (Sprint 4).

## 10. Guion de demostración

1. Iniciar sesión con `admin@empresa-demo.test` / `password`.
2. Abrir **Catálogo** y crear una categoría (por ejemplo, «Bebidas»).
3. Crear una marca (por ejemplo, «Marca Demo»).
4. En **Productos**, crear «Café molido» con su categoría y marca.
5. Abrir **Variantes** del producto y registrar un SKU y un código de barras.
6. Desactivar una categoría, una marca o una variante y comprobar el estado.
7. Cerrar sesión y entrar con `operador@empresa-demo.test` / `password` para
   comprobar que solo puede consultar, no administrar.

## 11. Pendientes y bloqueos

| Pendiente | Motivo | Próximo paso |
| --- | --- | --- |
| DD-05 (borrado lógico/auditoría) | Sigue como propuesta | Resolver antes de auditoría |
| Restricción de producto con historial | Requiere movimientos de inventario | Sprint 4 |
| Múltiples códigos por variante | El backlog menciona `codigo_variante`; se implementó `sku`/`barcode` | Añadir `product_variant_codes` si se requiere |
| Validar MySQL/Docker | Entorno no instalado | Instalar Docker o MySQL |
| Sprint 3 | Unidades, conversiones y almacenes/ubicaciones | Planificar y ejecutar |

## 12. Documentos generados

| Documento | Contenido |
| --- | --- |
| [`SDD/PLAN_SPRINT_2_CATALOGO.md`](./SDD/PLAN_SPRINT_2_CATALOGO.md) | Plan, decisiones, contratos y pruebas |
| [`SDD/SDD-04_DISENO_BASE_DATOS.md`](./SDD/SDD-04_DISENO_BASE_DATOS.md) | Modelo de datos y nota de implementación |
| [`SDD/SDD-05_DISENO_API.md`](./SDD/SDD-05_DISENO_API.md) | Contratos de la API actualizados |

## 13. Commits del sprint

```text
e79bd45 feat(catalog): gestionar categorias y marcas (US-0201)
c7a8397 docs(sprint-2): planificar catalogo y documentar US-0201
7c7ce46 feat(catalog): registrar y mantener productos (US-0202)
3cfe57c docs(sprint-2): documentar productos (US-0202)
1696075 feat(catalog): gestionar variantes y codigos (US-0203)
bf79215 docs(sprint-2): documentar variantes y cierre del catalogo (US-0203)
```

## 14. Próximo sprint

**Sprint 3 — Unidades y almacenes (EP-03):** unidades de medida, conversiones,
almacenes y ubicaciones. Ver el canvas
[`DOCS_origen/CANVAS_SPRINT_3_UNIDADES_ALMACENES.md`](./DOCS_origen/CANVAS_SPRINT_3_UNIDADES_ALMACENES.md).

## 15. Documentos relacionados

- [`README_SPRINT_0.md`](./README_SPRINT_0.md) — cierre del Sprint 0.
- [`README_SPRINT_1.md`](./README_SPRINT_1.md) — cierre del Sprint 1.
- [`README.md`](./README.md) — índice general de la documentación.
- [`../CHANGELOG.md`](../CHANGELOG.md) — historial de cambios.
