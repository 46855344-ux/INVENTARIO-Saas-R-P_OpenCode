# README — Sprint 3 «Unidades, conversiones y ubicaciones»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-03 — Unidades y ubicaciones
**Iteración:** Sprint 3
**Versión del documento:** 1.0
**Fecha:** 2026-11-13
**Estado:** Cerrado técnicamente — validación con MySQL/Docker y ratificación formal de DP-03 pendientes
**Canvas de referencia:** [`DOCS_origen/CANVAS_SPRINT_3_UNIDADES_ALMACENES.md`](./DOCS_origen/CANVAS_SPRINT_3_UNIDADES_ALMACENES.md)
**Plan del sprint:** [`SDD/PLAN_SPRINT_3_UNIDADES_UBICACIONES.md`](./SDD/PLAN_SPRINT_3_UNIDADES_UBICACIONES.md)

---

## 1. Resumen ejecutivo

El Sprint 3 incorporó la **configuración de inventario**: unidades de medida,
conversiones entre unidades compatibles y ubicaciones de almacén. Deja preparado
el terreno para el núcleo de inventario del Sprint 4.

| Historia | Objetivo | Resultado |
| --- | --- | --- |
| US-0301 | Configurar unidades de medida | **Completado** |
| US-0302 | Configurar conversiones de unidades | **Completado** |
| US-0303 | Administrar ubicaciones de almacén | **Completado** |

## 2. Contexto de entrada

- Sprint 2 cerrado: catálogo de categorías, marcas, productos y variantes.
- `TenantContext`, `TenantScope` y `BelongsToCompany` operativos desde el Sprint 1.
- Permisos de configuración definidos para este sprint.
- La tabla `stock_balances` aún no existe; la guardia de desactivación con stock
  se activa automáticamente cuando exista (Sprint 4).

## 3. Decisiones de entrada

| Tema | Decisión | Fundamento |
| --- | --- | --- |
| Precisión de cantidades | `DECIMAL(15,4)` | ESTANDAR §6.7 |
| Factores de conversión | `DECIMAL(18,6)`, mayores que cero | SDD-04 |
| Redondeo | `HALF_UP` a 4 decimales | ESTANDAR; DP-03 ajustado al estándar de 4 decimales |
| Dimensiones | `mass`, `volume`, `count`, `length`, `time`, `other` | Canvas §4 |
| Conversiones | Solo misma dimensión; inversa automática si existe el par inverso | RN-03-01/03 |
| Nombres internos | `units`, `unit_conversions`, `locations` | ESTANDAR §4.1–4.2 |
| Ubicaciones | `locations` con `type` y `parent_id`; un almacén es `type = warehouse` | SDD-04 §3.3 |
| Desactivación con stock | Guardia preparada; se activa con `stock_balances` (Sprint 4) | Canvas §3 US-0303 |
| Permisos | `settings.view` / `settings.manage` | Configuración de inventario |
| Tenant | `BelongsToCompany` + `TenantScope` | US-0104 / ADR-0002 |

> **DP-03:** la implementación adopta factor decimal, validación de factor
> positivo y redondeo `HALF_UP`. La escala de cantidades se fija en 4 decimales
> conforme al ESTANDAR; la ratificación formal de DP-03 sigue pendiente.

## 4. Modelo de datos y arquitectura

| Tabla | Campos principales |
| --- | --- |
| `units` | `id`, `company_id`, `code`, `name`, `dimension`, `precision`, `status` |
| `unit_conversions` | `id`, `company_id`, `from_unit_id`, `to_unit_id`, `factor`, `status` |
| `locations` | `id`, `company_id`, `parent_id`, `code`, `name`, `type`, `status` |

- Restricciones: `UNIQUE (company_id, code)` en unidades y ubicaciones;
  `UNIQUE (company_id, from_unit_id, to_unit_id)` en conversiones.
- `UnitService`, `UnitConversionService` y `LocationService` concentran las reglas.
- `UnitConversionService` usa `Brick\Math\BigDecimal` para evitar punto flotante
  binario y aplica `RoundingMode::HalfUp`.
- Policies por recurso y rutas con `can:settings.view` / `can:settings.manage`.

## 5. Historias y criterios de aceptación

| Historia | Criterios cubiertos | Evidencia |
| --- | --- | --- |
| US-0301 | Unidades activas, precisión 0–4, código único por empresa, dimensiones válidas | `tests/Feature/Inventory/UnitManagementTest.php` |
| US-0302 | Conversión directa e inversa, factor positivo, dimensiones compatibles, conversión no definida, redondeo | `tests/Feature/Inventory/UnitConversionManagementTest.php` |
| US-0303 | Crear/editar/desactivar ubicaciones, padre de la misma empresa, aislamiento y guardia de stock | `tests/Feature/Inventory/LocationManagementTest.php` |

## 6. Contratos API

| Método | Ruta | Permiso |
| --- | --- | --- |
| `GET` | `/api/v1/units` | `settings.view` |
| `POST` | `/api/v1/units` | `settings.manage` |
| `GET` | `/api/v1/units/{id}` | `settings.view` |
| `PUT` | `/api/v1/units/{id}` | `settings.manage` |
| `PATCH` | `/api/v1/units/{id}/status` | `settings.manage` |
| `GET` | `/api/v1/conversions` | `settings.view` |
| `POST` | `/api/v1/conversions` | `settings.manage` |
| `POST` | `/api/v1/conversions/simulate` | `settings.view` |
| `PUT` | `/api/v1/conversions/{id}` | `settings.manage` |
| `PATCH` | `/api/v1/conversions/{id}/status` | `settings.manage` |
| `GET` | `/api/v1/locations` | `settings.view` |
| `POST` | `/api/v1/locations` | `settings.manage` |
| `GET` | `/api/v1/locations/{id}` | `settings.view` |
| `PUT` | `/api/v1/locations/{id}` | `settings.manage` |
| `PATCH` | `/api/v1/locations/{id}/status` | `settings.manage` |

Todos los endpoints requieren `auth:sanctum` y `X-Company-Id`.

## 7. Entregables y evidencia

| Entregable | Ubicación | Estado |
| --- | --- | --- |
| Migraciones de configuración | `backend/database/migrations/2026_11_13_08*.php` | Completo |
| Modelos, policies, resources y services | `backend/app/Modules/Inventory/` | Completo |
| API de unidades, conversiones y ubicaciones | `backend/routes/api.php` | Completo |
| Permisos `settings.*` | `backend/database/seeders/IdentitySeeder.php` | Completo |
| Pantalla React «Configuración» | `frontend/src/features/settings/` | Completo |
| Servicio frontend y tipos | `frontend/src/services/settings.ts`, `frontend/src/types/` | Completo |
| Pruebas de API y UI | `backend/tests/Feature/Inventory/`, `frontend/tests/` | Completo |

## 8. Evidencia de pruebas

| Verificación | Comando | Resultado |
| --- | --- | --- |
| Pruebas backend | `php artisan test` | 93 pruebas, 302 aserciones — OK |
| Estilo backend | `vendor/bin/pint --test` | Sin diferencias — OK |
| Pruebas frontend | `npm run test` | 42 pruebas — OK |
| Lint frontend | `npm run lint` | Sin errores — OK |
| Build frontend | `npm run build` | Compilado — OK |

Matriz del sprint: T-0301 a T-0312 cubiertos, incluidos decimales, conversiones
inversas, unidades incompatibles, aislamiento y guardia de desactivación con stock.

## 9. Definition of Done — estado

- [x] US-0301, US-0302 y US-0303 cumplen sus criterios de aceptación.
- [x] Migraciones, relaciones, índices y restricciones implementados.
- [x] Reglas de negocio en Laravel; React consume la API.
- [x] API protegida por autenticación, autorización y aislamiento.
- [x] React maneja carga, error y estado vacío.
- [x] Pruebas de configuración y aislamiento pasan en local.
- [x] Documentación de API y modelo actualizada.
- [ ] Ratificación formal de DP-03 — pendiente del Product Owner.
- [ ] Validación contra MySQL real y `docker compose` — pendiente por entorno.

## 10. Guion de demostración

1. Iniciar sesión con `admin@empresa-demo.test` / `password`.
2. Abrir **Configuración → Unidades** y crear `KG` (Masa, precisión 3) y `G` (Masa, precisión 3).
3. Abrir **Conversiones**, crear `KG → G` con factor `1000`.
4. En el simulador, convertir `2.5 KG` y ver `2500.0000 G` con regla «Factor directo».
5. Probar `500 G → KG` y ver `0.5000 KG` con regla «Factor inverso».
6. Intentar `KG → L` y comprobar el rechazo por dimensiones incompatibles.
7. Abrir **Ubicaciones**, crear `ALM-01` (Almacén) y `EST-01` (Estante) con padre `ALM-01`.
8. Desactivar una ubicación sin stock y comprobar la restricción cuando exista stock (Sprint 4).

## 11. Pendientes y bloqueos

| Pendiente | Motivo | Próximo paso |
| --- | --- | --- |
| Ratificar DP-03 | Decisión de negocio sobre precisión/redondeo | Validar la escala de 4 decimales con el PO |
| Guardia de stock efectiva | `stock_balances` llega en Sprint 4 | Activar al crear la tabla |
| Validar MySQL/Docker | Entorno no instalado | Instalar Docker o MySQL |
| Sprint 4 | Núcleo de inventario | Existencias, entradas, salidas y validación de stock |

## 12. Documentos generados

| Documento | Contenido |
| --- | --- |
| [`SDD/PLAN_SPRINT_3_UNIDADES_UBICACIONES.md`](./SDD/PLAN_SPRINT_3_UNIDADES_UBICACIONES.md) | Plan, decisiones, contratos y pruebas |
| [`SDD/SDD-04_DISENO_BASE_DATOS.md`](./SDD/SDD-04_DISENO_BASE_DATOS.md) | Modelo de datos y nota de implementación |
| [`SDD/SDD-05_DISENO_API.md`](./SDD/SDD-05_DISENO_API.md) | Contratos de la API actualizados |

## 13. Commits del sprint

```text
7e89415 feat(inventory): unidades, conversiones y ubicaciones (US-0301 a US-0303)
b1d8c0f docs(sprint-3): documentar unidades y ubicaciones
```

## 14. Próximo sprint

**Sprint 4 — Núcleo de inventario (EP-04):** existencias por variante y ubicación,
entradas, salidas con validación de stock, ajustes y movimientos históricos.
Ver el canvas
[`DOCS_origen/CANVAS_SPRINT_4_INVENTARIO_CENTRAL.md`](./DOCS_origen/CANVAS_SPRINT_4_INVENTARIO_CENTRAL.md).

## 15. Documentos relacionados

- [`README_SPRINT_0.md`](./README_SPRINT_0.md) — cierre del Sprint 0.
- [`README_SPRINT_1.md`](./README_SPRINT_1.md) — cierre del Sprint 1.
- [`README_SPRINT_2.md`](./README_SPRINT_2.md) — cierre del Sprint 2.
- [`README.md`](./README.md) — índice general de la documentación.
- [`../CHANGELOG.md`](../CHANGELOG.md) — historial de cambios.
