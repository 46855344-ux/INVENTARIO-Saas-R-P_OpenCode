# README — Sprint 5 «Compras y traslados»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-05 — Compras y traslados
**Iteración:** Sprint 5
**Versión del documento:** 1.0
**Fecha:** 2026-11-14
**Estado:** Cerrado técnicamente — validación con MySQL/Docker y ratificación de DP-02/DP-05 pendientes
**Canvas de referencia:** [`DOCS_origen/CANVAS_SPRINT_5_COMPRAS_TRASLADOS.md`](./DOCS_origen/CANVAS_SPRINT_5_COMPRAS_TRASLADOS.md)
**Plan del sprint:** [`SDD/PLAN_SPRINT_5_COMPRAS_TRASLADOS.md`](./SDD/PLAN_SPRINT_5_COMPRAS_TRASLADOS.md)

---

## 1. Resumen ejecutivo

El Sprint 5 incorporó el abastecimiento y los movimientos internos:
proveedores, compras con costos históricos, recepción de compras y traslados
atómicos entre ubicaciones. La recepción y el traslado mutan existencias
exclusivamente a través de `InventoryMovementService`, manteniendo las garantías
del Sprint 4: transacción, idempotencia, saldo no negativo y trazabilidad.

| Historia | Objetivo | Resultado |
| --- | --- | --- |
| US-0501 | Gestionar proveedores | **Completado** |
| US-0502 | Registrar compras y costos históricos | **Completado** |
| US-0503 | Trasladar existencias entre ubicaciones | **Completado** |

## 2. Contexto de entrada

- Sprint 4 cerrado: existencias, movimientos, idempotencia y reversión.
- Catálogo, variantes, unidades y ubicaciones operativos (Sprints 2–3).
- `InventoryMovementService` como única puerta de mutación de stock.
- El módulo `Purchasing` ya estaba reservado en la arquitectura (SDD-01).

## 3. Decisiones del sprint

| Tema | Decisión | Fundamento |
| --- | --- | --- |
| DP-02 — valorización | Solo se conserva `unit_cost` por línea; no se calcula costo promedio ni valorización global | Decisión pendiente; se evita publicar una valoración no aprobada |
| DP-05 — aprobaciones | `purchases.receive` y `transfers.manage` separados; sin niveles de aprobación | Propuesta operativa del sprint |
| Recepción de compras | Total y en un solo paso desde el borrador | Recepción parcial fuera de alcance |
| Estados de compra | `draft` → `received`; la compra recibida es inmutable | Trazabilidad y bloqueo de edición |
| Edición de compras | Solo en `draft`, reemplazando el detalle completo | Permite corregir borradores |
| Traslados | Ejecución inmediata y atómica al crear; estado `confirmed` | Sin borrador ni tránsito aprobados |
| Origen y destino | Ubicaciones distintas de la misma empresa | RN-05-08 y aislamiento |
| Idempotencia | `Idempotency-Key` obligatoria en recepción y traslado | Reintentos sin duplicar stock |
| Costos históricos | `unit_cost` y `subtotal` congelados por línea | RN-05-04 |
| Precisión monetaria | `unit_cost DECIMAL(15,4)`; subtotal y total `DECIMAL(15,2)` con `HALF_UP` | Consistencia con el estándar |
| Trazabilidad | `reference_type`/`reference_id` en movimientos (`purchase`, `transfer`) | Enlace con el documento de origen |
| Tipos de movimiento | Se añaden `transfer_in` y `transfer_out`; la recepción usa `entry` | SDD-04 §3.4 |
| Permisos | `purchases.view`, `purchases.manage`, `purchases.receive`, `transfers.view`, `transfers.manage` | Matriz operativa |
| Nombres internos | `suppliers`, `purchases`, `purchase_items`, `transfers`, `transfer_items` | ESTANDAR Anexo A y SDD-04 |

> **DP-02:** el total de la compra es la suma de subtotales del documento y no
> representa una valorización del inventario.
>
> **DP-05:** la matriz de permisos es una propuesta; los niveles de aprobación y
> el escalado sin administrador siguen pendientes.

## 4. Modelo de datos y arquitectura

| Tabla | Campos principales |
| --- | --- |
| `suppliers` | `id`, `company_id`, `name`, `tax_id`, `email`, `phone`, `status`, timestamps |
| `purchases` | `id`, `company_id`, `supplier_id`, `document_number`, `ordered_at`, `status`, `total`, `user_id`, `received_by`, `received_at`, `reception_idempotency_key`, `notes`, timestamps |
| `purchase_items` | `id`, `company_id`, `purchase_id`, `variant_id`, `location_id`, `quantity`, `unit_cost`, `subtotal`, timestamps |
| `transfers` | `id`, `company_id`, `from_location_id`, `to_location_id`, `status`, `user_id`, `confirmed_at`, `idempotency_key`, `notes`, timestamps |
| `transfer_items` | `id`, `company_id`, `transfer_id`, `variant_id`, `quantity`, timestamps |

- Restricciones: `UNIQUE (company_id, tax_id)` en proveedores; unicidad de
  variante por compra y por traslado; unicidad de claves de idempotencia por
  empresa; claves foráneas con `RESTRICT` hacia el historial.
- `stock_movements` incorpora `reference_type` y `reference_id` con índice por
  empresa y referencia.
- `SupplierService`, `PurchaseService` y `TransferService` concentran las reglas.
- La recepción y el traslado llaman a `InventoryMovementService::record`, que
  bloquea la existencia y aplica `HALF_UP` con `Brick\Math\BigDecimal`.

## 5. Historias y criterios de aceptación

| Historia | Criterios cubiertos | Evidencia |
| --- | --- | --- |
| US-0501 | Alta, consulta, edición y desactivación; validaciones; historial preservado; aislamiento | `tests/Feature/Purchasing/SupplierManagementTest.php` |
| US-0502 | Compra en borrador, detalle, costos históricos, edición de borrador, recepción única y trazabilidad | `tests/Feature/Purchasing/PurchaseManagementTest.php` |
| US-0503 | Traslado atómico, stock insuficiente, rollback, origen distinto, idempotencia y aislamiento | `tests/Feature/Purchasing/TransferManagementTest.php` |

## 6. Contratos API

| Método | Ruta | Permiso |
| --- | --- | --- |
| `GET` | `/api/v1/suppliers` | `purchases.view` |
| `POST` | `/api/v1/suppliers` | `purchases.manage` |
| `GET` | `/api/v1/suppliers/{id}` | `purchases.view` |
| `PUT` | `/api/v1/suppliers/{id}` | `purchases.manage` |
| `PATCH` | `/api/v1/suppliers/{id}/status` | `purchases.manage` |
| `GET` | `/api/v1/purchases` | `purchases.view` |
| `POST` | `/api/v1/purchases` | `purchases.manage` |
| `GET` | `/api/v1/purchases/{id}` | `purchases.view` |
| `PUT` | `/api/v1/purchases/{id}` | `purchases.manage` |
| `POST` | `/api/v1/purchases/{id}/receive` | `purchases.receive` |
| `GET` | `/api/v1/transfers` | `transfers.view` |
| `POST` | `/api/v1/transfers` | `transfers.manage` |
| `GET` | `/api/v1/transfers/{id}` | `transfers.view` |

Todos los endpoints requieren `auth:sanctum` y `X-Company-Id`. La recepción y el
traslado exigen `Idempotency-Key`.

Errores de dominio del sprint:

| Código | HTTP | Situación |
| --- | --- | --- |
| `PURCHASE_NOT_EDITABLE` | 409 | Se intenta editar una compra recibida |
| `IDEMPOTENCY_CONFLICT` | 409 | La clave ya pertenece a otra operación |
| `INSUFFICIENT_STOCK` | 409 | El traslado supera el saldo de origen |
| `VALIDATION_ERROR` | 422 | Datos inválidos, origen igual a destino o falta la clave |
| `FORBIDDEN` | 403 | Falta el permiso requerido |
| `NOT_FOUND` | 404 | Recurso de otra empresa o inexistente |

## 7. Entregables y evidencia

| Entregable | Ubicación | Estado |
| --- | --- | --- |
| Migraciones de compras y traslados | `backend/database/migrations/2026_11_14_09*.php` | Completo |
| Modelos, policies, requests, resources y services | `backend/app/Modules/Purchasing/` | Completo |
| Referencia de origen en movimientos | `backend/app/Modules/Inventory/` | Completo |
| API de proveedores, compras y traslados | `backend/routes/api.php` | Completo |
| Permisos de compras y traslados | `backend/database/seeders/IdentitySeeder.php` | Completo |
| Pantalla React «Compras y traslados» | `frontend/src/features/purchasing/` | Completo |
| Servicio frontend y tipos | `frontend/src/services/purchasing.ts`, `frontend/src/types/` | Completo |
| Pruebas de API y UI | `backend/tests/Feature/Purchasing/`, `frontend/tests/` | Completo |

## 8. Evidencia de pruebas

| Verificación | Comando | Resultado |
| --- | --- | --- |
| Pruebas backend | `php artisan test` | 131 pruebas, 488 aserciones — OK |
| Estilo backend | `vendor/bin/pint --test` | Sin diferencias — OK |
| Pruebas frontend | `npm run test` | 56 pruebas — OK |
| Lint frontend | `npm run lint` | Sin errores — OK |
| Build frontend | `npm run build` | Compilado — OK |

Matriz del sprint: T-0501 a T-0518 cubiertos, incluidos costos históricos,
recepción idempotente, edición bloqueada, traslado con rollback, stock
insuficiente, origen igual a destino, aislamiento multiempresa y repetición sin
doble descuento.

**Recorrido real contra la API** (base SQLite demo):

1. Compra en borrador por 10 unidades a costo 80 → total `800.00`, stock intacto.
2. Recepción confirmada → existencia del origen `10.0000`.
3. Traslado de 4 unidades → origen `6.0000`, destino `4.0000`.
4. Movimientos generados: `entry`, `transfer_out`, `transfer_in` (3 en total),
   con `reference_type` y `reference_id` enlazados.

## 9. Definition of Done — estado

- [x] US-0501, US-0502 y US-0503 cumplen sus criterios de aceptación.
- [x] Migraciones, relaciones, índices y restricciones implementados.
- [x] Recepción y traslado usan exclusivamente `InventoryMovementService`.
- [x] Operaciones atómicas, idempotentes y sin stock negativo.
- [x] Costos históricos preservados y trazabilidad por referencia.
- [x] Policies, TenantScope y validaciones activas.
- [x] React con estados de carga, error, vacío y permisos.
- [x] Pruebas de API y UI pasan; documentación actualizada.
- [ ] Ratificación formal de DP-02 — pendiente del Product Owner.
- [ ] Ratificación formal de DP-05 — pendiente del Product Owner.
- [ ] Validación contra MySQL real y `docker compose` — pendiente por entorno.

## 10. Guion de demostración

1. Iniciar sesión con `admin@empresa-demo.test` / `password`.
2. Abrir **Compras → Proveedores** y crear un proveedor.
3. Abrir **Compras**, crear una compra con fecha, documento y una línea con
   variante, ubicación destino, cantidad y costo unitario.
4. Comprobar que la compra queda en **Borrador** y que **Inventario → Existencias**
   no cambió.
5. Pulsar **Recibir** y confirmar; verificar el aumento de existencias y el
   movimiento `entry` enlazado a la compra.
6. Intentar recibir de nuevo: no se duplica stock.
7. Abrir **Traslados**, elegir origen y destino distintos, una variante y una
   cantidad menor o igual al saldo.
8. Confirmar el traslado y verificar que el origen baja, el destino sube y el
   total global no cambia.
9. Intentar un traslado superior al saldo y comprobar el rechazo por stock
   insuficiente.
10. Revisar **Inventario → Movimientos** y ver `transfer_out`/`transfer_in`
    enlazados al traslado.

## 11. Pendientes y bloqueos

| Pendiente | Motivo | Próximo paso |
| --- | --- | --- |
| Ratificar DP-02 | Método de valorización no aprobado | Validar el alcance con el PO |
| Ratificar DP-05 | Niveles de aprobación no definidos | Acordar la matriz definitiva |
| Validar MySQL/Docker | Entorno no instalado | Probar bloqueos y concurrencia reales |
| Recepción parcial | Fuera de alcance | Evaluar `purchase_receipts` en una iteración futura |
| Traslados con tránsito | Fuera de alcance | Evaluar estados intermedios si el negocio lo requiere |
| Impuestos, moneda y descuentos | Fuera de alcance | Definir reglas antes de incorporarlos |
| Sprint 6 | Métricas y alertas | Indicadores, stock mínimo y tareas programadas |

## 12. Documentos generados

| Documento | Contenido |
| --- | --- |
| [`SDD/PLAN_SPRINT_5_COMPRAS_TRASLADOS.md`](./SDD/PLAN_SPRINT_5_COMPRAS_TRASLADOS.md) | Plan, decisiones, modelo, contratos y pruebas |
| [`SDD/SDD-04_DISENO_BASE_DATOS.md`](./SDD/SDD-04_DISENO_BASE_DATOS.md) | Modelo de datos y nota de implementación |
| [`SDD/SDD-05_DISENO_API.md`](./SDD/SDD-05_DISENO_API.md) | Contratos de la API actualizados |
| [`README_SPRINT_5.md`](./README_SPRINT_5.md) | Este documento de cierre |

## 13. Commits del sprint

```text
3d8a47b docs(sprint-5): planificar compras y traslados
c52c1e7 feat(purchasing): compras, proveedores y traslados (US-0501 a US-0503)
```

## 14. Próximo sprint

**Sprint 6 — Métricas y alertas (EP-06):** indicadores de inventario, stock
mínimo, inactividad y tareas programadas. Ver el canvas
[`DOCS_origen/CANVAS_SPRINT_6_METRICAS_ALERTAS.md`](./DOCS_origen/CANVAS_SPRINT_6_METRICAS_ALERTAS.md).

## 15. Documentos relacionados

- [`README_SPRINT_0.md`](./README_SPRINT_0.md) — cierre del Sprint 0.
- [`README_SPRINT_1.md`](./README_SPRINT_1.md) — cierre del Sprint 1.
- [`README_SPRINT_2.md`](./README_SPRINT_2.md) — cierre del Sprint 2.
- [`README_SPRINT_3.md`](./README_SPRINT_3.md) — cierre del Sprint 3.
- [`README_SPRINT_4.md`](./README_SPRINT_4.md) — cierre del Sprint 4.
- [`README.md`](./README.md) — índice general de la documentación.
- [`../CHANGELOG.md`](../CHANGELOG.md) — historial de cambios.
