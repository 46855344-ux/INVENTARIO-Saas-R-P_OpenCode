# Plan — Sprint 5 «Compras y traslados»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-05 — Compras y traslados
**Documento:** Plan de iteración, decisiones, contratos y pruebas
**Versión:** 1.0 — Sprint 5
**Fecha:** 2026-11-14
**Estado:** Implementado — US-0501 a US-0503 completadas; DP-02/DP-05 y validación MySQL pendientes
**Canvas de referencia:** [`DOCS_origen/CANVAS_SPRINT_5_COMPRAS_TRASLADOS.md`](../DOCS_origen/CANVAS_SPRINT_5_COMPRAS_TRASLADOS.md)

---

## 1. Objetivo

Incorporar la operación de abastecimiento: proveedores, compras con costos
históricos, recepción de compras que incrementa existencias a través de
`InventoryMovementService`, y traslados atómicos entre ubicaciones de la misma
empresa. Ninguna de estas operaciones muta existencias por fuera del servicio
central del Sprint 4.

## 2. Alcance

| Historia | Prioridad | Resultado esperado |
| --- | --- | --- |
| US-0501 — Gestionar proveedores | P1 | Alta, consulta, edición y desactivación con historial preservado |
| US-0502 — Registrar compras y costos históricos | P1 | Compra en borrador con detalle, costos inmutables y recepción única |
| US-0503 — Trasladar existencias entre ubicaciones | P1 | Salida y entrada atómicas, sin stock negativo y con saldo global cero |

Fuera de alcance de este sprint:

- Valorización global del inventario (DP-02) y márgenes (DP-09).
- Recepción parcial, multi-etapa o con tránsito.
- Cuentas por pagar, facturación electrónica, impuestos, moneda y descuentos.
- Anulación o reversión de compras y traslados ya confirmados.
- Transferencias entre empresas.
- Portal externo de proveedores.

## 3. Decisiones del sprint

| Tema | Decisión | Estado |
| --- | --- | --- |
| DP-02 — valorización | Se guarda `unit_cost` por línea de compra como costo histórico; no se calcula costo promedio ni valorización global | Delimitado; ratificación pendiente |
| DP-05 — aprobaciones | Se separan `purchases.receive` y `transfers.manage`; no se implementan niveles de aprobación | Propuesta del sprint |
| Recepción de compras | Total y en un solo paso desde el borrador; sin recepciones parciales | Decidido |
| Estados de compra | `draft` → `received`; una compra recibida es inmutable | Decidido |
| Edición de compras | Solo en `draft`, reemplazando el detalle completo | Decidido |
| Traslados | Ejecución inmediata y atómica al crear; estado único `confirmed`; sin borrador ni tránsito | Decidido |
| Origen y destino | Deben ser ubicaciones distintas de la misma empresa | Decidido (RN-05-08) |
| Idempotencia | `Idempotency-Key` obligatoria en recepción y traslado; repetir devuelve el registro existente sin duplicar stock | Decidido |
| Costos históricos | `unit_cost` y `subtotal` se congelan por línea; compras nuevas no los modifican | Decidido (RN-05-04) |
| Precisión monetaria | `unit_cost DECIMAL(15,4)`; `subtotal` y `total DECIMAL(15,2)` con `HALF_UP` | Decidido |
| Nombres internos | `suppliers`, `purchases`, `purchase_items`, `transfers`, `transfer_items` | ESTANDAR Anexo A y SDD-04 |
| Nombres de rutas | Recursos en inglés: `/api/v1/suppliers`, `/purchases`, `/transfers` | SDD-05 |
| Permisos | `purchases.view`, `purchases.manage`, `purchases.receive`, `transfers.view`, `transfers.manage` | Propuesta del sprint |
| Trazabilidad | Los movimientos de recepción y traslado guardan `reference_type` y `reference_id` | SDD-04 §§3.4–3.5 |
| Tipos de movimiento | Se añaden `transfer_in` y `transfer_out`; la recepción usa `entry` | SDD-04 §3.4 |

> **DP-02:** no se publica valorización definitiva. El total de la compra es la
> suma de subtotales del documento y no representa el costo del inventario.
>
> **DP-05:** la matriz de permisos es una propuesta operativa; los niveles de
> aprobación y el escalado sin administrador siguen pendientes.

## 4. Modelo de datos

| Tabla | Campos principales |
| --- | --- |
| `suppliers` | `id`, `company_id`, `name`, `tax_id`, `email`, `phone`, `status`, timestamps |
| `purchases` | `id`, `company_id`, `supplier_id`, `document_number`, `ordered_at`, `status`, `total`, `user_id`, `received_by`, `received_at`, `reception_idempotency_key`, `notes`, timestamps |
| `purchase_items` | `id`, `company_id`, `purchase_id`, `variant_id`, `location_id`, `quantity DECIMAL(15,4)`, `unit_cost DECIMAL(15,4)`, `subtotal DECIMAL(15,2)`, timestamps |
| `transfers` | `id`, `company_id`, `from_location_id`, `to_location_id`, `status`, `user_id`, `confirmed_at`, `idempotency_key`, `notes`, timestamps |
| `transfer_items` | `id`, `company_id`, `transfer_id`, `variant_id`, `quantity DECIMAL(15,4)`, timestamps |

Restricciones:

- `UNIQUE (company_id, tax_id)` en proveedores (los documentos vacíos no chocan).
- `UNIQUE (purchase_id, variant_id)` y `UNIQUE (transfer_id, variant_id)`.
- `UNIQUE (company_id, reception_idempotency_key)` y
  `UNIQUE (company_id, idempotency_key)`.
- Claves foráneas con `RESTRICT` hacia proveedores, variantes y ubicaciones;
  `CASCADE` del detalle hacia su cabecera.
- `stock_movements` incorpora `reference_type` y `reference_id` con índice por
  empresa y referencia.

## 5. Contratos API

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

Todas las peticiones requieren `auth:sanctum` y `X-Company-Id`. La recepción y
el traslado exigen la cabecera `Idempotency-Key`.

Errores previstos:

| Código | HTTP | Situación |
| --- | --- | --- |
| `PURCHASE_NOT_EDITABLE` | 409 | Se intenta editar una compra recibida |
| `IDEMPOTENCY_CONFLICT` | 409 | La clave de idempotencia ya pertenece a otra operación |
| `INSUFFICIENT_STOCK` | 409 | El traslado supera el saldo de origen |
| `VALIDATION_ERROR` | 422 | Datos inválidos, origen igual a destino o falta la clave |
| `FORBIDDEN` | 403 | Falta el permiso requerido |
| `NOT_FOUND` | 404 | Recurso de otra empresa o inexistente |

## 6. Reglas transaccionales

### Compra

1. Validar proveedor, variantes y ubicaciones de la empresa activa.
2. Crear la compra en `draft` con su detalle; no tocar existencias.
3. Calcular `subtotal = quantity × unit_cost` y `total` con `HALF_UP` a 2
   decimales; conservarlos en la línea.
4. Editar solo si está en `draft`, reemplazando el detalle en una transacción.
5. En la recepción, bloquear la compra y verificar que siga en `draft`.
6. Por cada línea, registrar una entrada mediante `InventoryMovementService` con
   `reference_type = purchase` y `reference_id = compra`.
7. Marcar `received`, guardar usuario y fecha; si algo falla, revertir todo.
8. Repetir la recepción devuelve la compra recibida sin nuevos movimientos.

### Traslado

1. Validar origen y destino distintos y de la misma empresa.
2. Abrir transacción y crear la cabecera `confirmed` con su detalle.
3. Por cada línea, registrar la salida de origen y la entrada de destino con
   `reference_type = transfer` y el mismo `reference_id`.
4. Si cualquier salida no tiene saldo suficiente, revertir cabecera, líneas y
   movimientos.
5. Repetir la petición con la misma clave devuelve el traslado existente.

## 7. Matriz de pruebas

| ID | Caso | Resultado esperado |
| --- | --- | --- |
| T-0501 | Crear proveedor válido | Creado en la empresa activa |
| T-0502 | Editar proveedor | Datos actualizados con validaciones |
| T-0503 | Desactivar proveedor con compras | Historial de compras preservado |
| T-0504 | Proveedor de otra empresa | 404 al consultarlo |
| T-0505 | Crear compra con detalle | Persistida sin alterar stock |
| T-0506 | Dos compras con precios distintos | Cada línea conserva su costo |
| T-0507 | Confirmar recepción | Stock incrementado una sola vez |
| T-0508 | Repetir recepción | Sin movimientos ni stock duplicados |
| T-0509 | Editar compra recibida | 409 `PURCHASE_NOT_EDITABLE` |
| T-0510 | Traslado con saldo suficiente | Origen baja y destino sube |
| T-0511 | Traslado superior al disponible | 409 sin cambios |
| T-0512 | Traslado válido | Variación global igual a cero |
| T-0513 | Falla una línea del traslado | Rollback completo |
| T-0514 | Origen igual a destino | 422 |
| T-0515 | Ubicaciones de otra empresa | 422/404 sin filtrar datos |
| T-0516 | Usuario sin permiso | 403 |
| T-0517 | Repetir traslado con la misma clave | Sin doble descuento |
| T-0518 | Traslado confirmado | Conserva usuario, fecha, origen y destino |

## 8. Frontend

- `PurchasingPage` con pestañas **Proveedores**, **Compras** y **Traslados**.
- Proveedores: tabla, búsqueda, alta/edición y activación/desactivación.
- Compras: listado con filtros, formulario con cabecera y líneas dinámicas,
  edición de borradores, detalle y confirmación de recepción.
- Traslados: formulario con origen, destino, variantes y cantidades; listado con
  usuario, fecha y estado; mensajes de stock insuficiente.
- Todas las vistas con estados de carga, vacío, error y bloqueo de doble envío.
- Acciones ocultas o deshabilitadas según permisos, sin reemplazar la
  autorización del backend.

## 9. Definition of Ready

- [x] US-0501 a US-0503 comprendidas y priorizadas.
- [x] `InventoryMovementService` disponible y probado (Sprint 4).
- [x] Modelo y contratos revisados contra SDD-04 y SDD-05.
- [x] Decisiones de recepción total y traslado inmediato delimitadas.
- [x] DP-02 y DP-05 acotadas sin asumir aprobación.
- [ ] Validación con MySQL real — pendiente por entorno.

## 10. Definition of Done

- [x] US-0501, US-0502 y US-0503 cumplen sus criterios.
- [x] Migraciones, índices y restricciones implementados.
- [x] Recepción y traslado usan exclusivamente `InventoryMovementService`.
- [x] Operaciones atómicas, idempotentes y sin stock negativo.
- [x] Costos históricos preservados y trazabilidad por referencia.
- [x] Policies, TenantScope y validaciones activas.
- [x] React con estados de carga, error, vacío y permisos.
- [x] Pruebas de API y UI pasan; documentación actualizada.

## 11. Orden de implementación

1. Migraciones, modelos, factories y referencia en `stock_movements`.
2. CRUD de proveedores, policy y pruebas de tenant.
3. Compras: creación, edición de borrador, totales y pruebas de costos.
4. Recepción de compras con `InventoryMovementService` e idempotencia.
5. Traslados atómicos, tipos `transfer_in`/`transfer_out` y pruebas de rollback.
6. Frontend de proveedores, compras y traslados.
7. Cierre: README del Sprint 5, índice, SDD y `CHANGELOG`.

## 12. Riesgos y pendientes

| Riesgo / pendiente | Mitigación |
| --- | --- |
| DP-02 sin ratificar | Guardar solo costo histórico por línea; no exponer valorización |
| DP-05 sin ratificar | Permisos operativos separados; documentar la propuesta |
| Concurrencia real | Bloqueo de existencia y transacción; validar en MySQL |
| Recepción multi-etapa | Fuera de alcance; el modelo permite añadir `purchase_receipts` |
| Traslados con tránsito | Fuera de alcance; ejecución inmediata y atómica |
| Impuestos, moneda y descuentos | Fuera de alcance; total = suma de subtotales |

## 13. Estado de implementación

**Completado el 2026-11-14.**

- Migraciones `suppliers`, `purchases`, `purchase_items`, `transfers` y
  `transfer_items`; referencia de origen en `stock_movements`.
- CRUD de proveedores con desactivación que preserva compras.
- Compras en borrador con edición y costos históricos; recepción total e
  idempotente que usa `InventoryMovementService`.
- Traslados atómicos con `transfer_out`/`transfer_in`, validación de saldo y
  compensación global cero.
- Pantalla React «Compras y traslados» con proveedores, compras y traslados.
- Evidencia: backend 131 pruebas / 488 aserciones, frontend 56 pruebas,
  lint/build OK y recorrido real compra → recepción → traslado verificado.
