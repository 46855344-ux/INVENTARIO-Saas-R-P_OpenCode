# Plan — Sprint 3 «Unidades, conversiones y ubicaciones»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-03 — Unidades y ubicaciones
**Documento:** Plan de iteración, decisiones, contratos y pruebas
**Versión:** 1.0 — Sprint 3
**Fecha:** 2026-11-13
**Estado:** US-0301 a US-0303 implementadas

---

## 1. Objetivo

Implementar unidades de medida, conversiones entre unidades compatibles y
ubicaciones de almacén, respetando precisión decimal, autorización por rol y
aislamiento por empresa. Es la antesala del núcleo de inventario (Sprint 4).

## 2. Decisiones de entrada

| Tema | Decisión | Fundamento |
| --- | --- | --- |
| Precisión de cantidades | `DECIMAL(15,4)` | ESTANDAR §6.7 |
| Factores de conversión | `DECIMAL(18,6)`, mayor que cero | SDD-04 |
| Redondeo | `HALF_UP` a 4 decimales al convertir | ESTANDAR + DP-03 (ajustado al estándar de 4 decimales) |
| Dimensiones | `mass`, `volume`, `count`, `length`, `time`, `other` | Canvas §4 |
| Conversiones | Solo unidades de la misma dimensión; inversa automática cuando existe el par inverso | RN-03-01/03 |
| Nombres internos | `units`, `unit_conversions`, `locations` | ESTANDAR §4.1–4.2 |
| Ubicaciones | `locations` con `type` y `parent_id`; un almacén es una ubicación de tipo `warehouse` | SDD-04 §3.3 (no se crea una tabla `warehouses` separada) |
| Desactivación de ubicación con stock | Guardia preparada; se activa al existir `stock_balances` (Sprint 4) | Canvas §3 US-0303 |
| Permisos | `settings.view` y `settings.manage` | Configuración de inventario |
| Tenant | `BelongsToCompany` + `TenantScope` | US-0104 / ADR-0002 |

> **DP-03:** se adopta el factor decimal, la validación de factor positivo y el
> redondeo `HALF_UP`. La escala de cantidades se fija en 4 decimales conforme al
> ESTANDAR; el valor de 3 decimales de la propuesta DP-03 queda como parámetro a
> ratificar por el Product Owner.

## 3. Historias

### US-0301 — Configurar unidades de medida

- CRUD de unidades activas con `code`, `name`, `dimension`, `precision` y `status`.
- `code` único por empresa; `precision` entre 0 y 4.
- Endpoints `/api/v1/units`.
- Pantalla de configuración con pestaña de unidades.

### US-0302 — Configurar conversiones de unidades

- Pares `from_unit → to_unit` con factor decimal; únicos por empresa.
- Solo se permiten unidades de la misma dimensión y de la misma empresa.
- `UnitConversionService` resuelve la conversión directa y la inversa, redondea
  a 4 decimales con `HALF_UP` y explica la regla aplicada.
- Simulador `POST /api/v1/conversions/simulate`.
- Endpoints `/api/v1/conversions`.

### US-0303 — Administrar ubicaciones de almacén

- CRUD de ubicaciones con `code`, `name`, `type`, `parent_id` y `status`.
- `code` único por empresa; el padre debe ser de la misma empresa.
- Endpoints `/api/v1/locations`.
- Guardia de desactivación: si existe la tabla `stock_balances` y la ubicación
  tiene cantidad, se rechaza la desactivación. La guardia se activa en Sprint 4.

## 4. Modelo de datos

| Tabla | Campos |
| --- | --- |
| `units` | `id`, `company_id`, `code`, `name`, `dimension`, `precision`, `status`, timestamps; `UNIQUE (company_id, code)` |
| `unit_conversions` | `id`, `company_id`, `from_unit_id`, `to_unit_id`, `factor DECIMAL(18,6)`, `status`, timestamps; `UNIQUE (company_id, from_unit_id, to_unit_id)` |
| `locations` | `id`, `company_id`, `parent_id`, `code`, `name`, `type`, `status`, timestamps; `UNIQUE (company_id, code)` |

## 5. Contratos API

| Método | Ruta | Permiso |
| --- | --- | --- |
| `GET` | `/api/v1/units` | `settings.view` |
| `POST` | `/api/v1/units` | `settings.manage` |
| `GET` | `/api/v1/units/{id}` | `settings.view` |
| `PUT` | `/api/v1/units/{id}` | `settings.manage` |
| `PATCH` | `/api/v1/units/{id}/status` | `settings.manage` |
| `GET` | `/api/v1/conversions` | `settings.view` |
| `POST` | `/api/v1/conversions` | `settings.manage` |
| `PUT` | `/api/v1/conversions/{id}` | `settings.manage` |
| `PATCH` | `/api/v1/conversions/{id}/status` | `settings.manage` |
| `POST` | `/api/v1/conversions/simulate` | `settings.view` |
| `GET` | `/api/v1/locations` | `settings.view` |
| `POST` | `/api/v1/locations` | `settings.manage` |
| `GET` | `/api/v1/locations/{id}` | `settings.view` |
| `PUT` | `/api/v1/locations/{id}` | `settings.manage` |
| `PATCH` | `/api/v1/locations/{id}/status` | `settings.manage` |

Todos los endpoints de tenant requieren `auth:sanctum` y `X-Company-Id`.

## 6. Matriz de pruebas

| ID | Caso | Resultado |
| --- | --- | --- |
| T-0301 | Crear unidad válida | 201, en la empresa activa |
| T-0302 | Unidad duplicada por código | 422 |
| T-0303 | Precisión fuera de 0..4 | 422 |
| T-0304 | Conversión compatible directa | Resultado con factor y redondeo |
| T-0305 | Conversión inversa | Resultado con factor recíproco |
| T-0306 | Conversión de dimensiones distintas | 422 `INCOMPATIBLE_UNITS` |
| T-0307 | Conversión no configurada | 422 `CONVERSION_NOT_DEFINED` |
| T-0308 | Unidades de otra empresa | 404 |
| T-0309 | Crear/editar ubicación | 200/201 en la empresa correcta |
| T-0310 | Usuario sin `settings.manage` | 403 |
| T-0311 | Ubicación de otra empresa | 404 |
| T-0312 | Desactivar ubicación con stock | 422 (guardia preparada) |

## 7. Estado de implementación

**Completado el 2026-11-13.**

- Migraciones, modelos, factories, requests, resources, policies y servicios.
- Endpoints de unidades, conversiones y ubicaciones.
- `UnitConversionService` con `BigDecimal` y `HALF_UP`.
- Permisos `settings.view` / `settings.manage`.
- Pantalla React «Configuración» con pestañas de unidades, conversiones y ubicaciones.
- Pruebas de API y de frontend.
