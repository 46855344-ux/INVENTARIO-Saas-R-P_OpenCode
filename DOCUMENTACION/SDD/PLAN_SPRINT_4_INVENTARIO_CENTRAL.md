# Plan — Sprint 4 «Núcleo de inventario»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-04 — Núcleo de inventario
**Documento:** Plan de iteración, decisiones, contratos y pruebas
**Versión:** 1.0 — Sprint 4
**Fecha:** 2026-11-13
**Estado:** Planificado — DP-01/DP-05/DD-03 delimitados; ratificación formal de DP-03 pendiente
**Canvas de referencia:** [`DOCS_origen/CANVAS_SPRINT_4_INVENTARIO_CENTRAL.md`](./DOCS_origen/CANVAS_SPRINT_4_INVENTARIO_CENTRAL.md)

---

## 1. Objetivo

Implementar el núcleo transaccional del inventario: consultar existencias y
registrar entradas, salidas, devoluciones, ajustes y reversiones con permisos,
aislamiento por empresa, prevención de stock negativo, idempotencia y
trazabilidad. Es la base para compras y traslados (Sprint 5).

## 2. Alcance

| Historia | Prioridad | Resultado esperado |
| --- | --- | --- |
| US-0401 — Consultar existencias | P0 | Stock por variante y ubicación, filtros y paginación |
| US-0402 — Registrar entradas | P0 | Entrada transaccional, idempotente y trazable |
| US-0403 — Registrar salidas | P0 | Salida sin stock negativo y con historial |
| US-0404 — Registrar devoluciones | P1 | Movimiento independiente y auditable |
| US-0405 — Ajustes autorizados | P0 | Ajuste con motivo y permiso específico |
| US-0406 — Revertir movimientos | P0 | Compensación enlazada, sin borrado ni doble reversión |

Fuera de alcance: ventas (DP-01), recepción formal de compras y traslados
(Sprint 5), conteos físicos (Sprint 7) y alertas (Sprint 6).

## 3. Decisiones y bloqueos

| Tema | Decisión para el sprint | Estado |
| --- | --- | --- |
| DP-03 — precisión/redondeo | Factor y cantidades con `DECIMAL`; redondeo `HALF_UP` a 4 decimales, conforme al ESTANDAR | Implementado en Sprint 3; ratificación formal pendiente |
| DD-03 — ubicación en el movimiento | `location_id` obligatorio en toda línea que afecte stock | Adoptado (ESTANDAR §4.4 y SDD-04) |
| DP-01 — quién registra salidas por venta | Fuera de alcance; la salida es genérica y no se vincula a ventas | Delimitado |
| DP-05 — autorización sin administrador | El ajuste requiere `inventory.adjust`; el flujo de escalado queda para una decisión posterior | Delimitado |
| Idempotencia | Cabecera `Idempotency-Key` única por empresa; un reintento devuelve el movimiento existente sin duplicar stock | Decidido |
| Concurrencia | Transacción + bloqueo de la fila de existencia; restricción única por variante/ubicación | Decidido |
| Reversión | Movimiento compensatorio enlazado al original; una sola reversión por movimiento | Decidido |
| Permisos | `inventory.view`, `inventory.move`, `inventory.adjust`, `inventory.approve` | Reutiliza la matriz del Sprint 1 |

## 4. Modelo de datos

| Tabla | Campos principales |
| --- | --- |
| `stock_balances` | `id`, `company_id`, `variant_id`, `location_id`, `quantity DECIMAL(15,4)`, `reserved_quantity DECIMAL(15,4)` (reservado para Sprint 5), timestamps |
| `stock_movements` | `id`, `company_id`, `type`, `status`, `user_id`, `reason`, `document_reference`, `original_movement_id`, `idempotency_key`, `confirmed_at`, timestamps |
| `stock_movement_lines` | `id`, `company_id`, `stock_movement_id`, `variant_id`, `location_id`, `unit_id`, `quantity DECIMAL(15,4)`, `normalized_quantity DECIMAL(15,4)`, `direction` (`in`/`out`), timestamps |

Restricciones:

- `UNIQUE (company_id, variant_id, location_id)` en `stock_balances`.
- `UNIQUE (company_id, idempotency_key)` en `stock_movements`.
- `UNIQUE (original_movement_id)` para impedir una segunda reversión.
- Movimientos confirmados inmutables: no se editan ni se eliminan.

Tipos de movimiento del sprint: `entry`, `exit`, `return`, `adjustment`,
`reversal`. Las compras y traslados añadirán sus tipos en el Sprint 5.

## 5. Contratos API

| Método | Ruta | Permiso |
| --- | --- | --- |
| `GET` | `/api/v1/inventory/stocks` | `inventory.view` |
| `GET` | `/api/v1/inventory/movements` | `inventory.view` |
| `GET` | `/api/v1/inventory/movements/{id}` | `inventory.view` |
| `POST` | `/api/v1/inventory/entries` | `inventory.move` |
| `POST` | `/api/v1/inventory/exits` | `inventory.move` |
| `POST` | `/api/v1/inventory/returns` | `inventory.move` |
| `POST` | `/api/v1/inventory/adjustments` | `inventory.adjust` |
| `POST` | `/api/v1/inventory/movements/{id}/reverse` | `inventory.approve` |

Todas las peticiones de tenant requieren `auth:sanctum` y `X-Company-Id`.
Las operaciones de escritura aceptan `Idempotency-Key`.

## 6. Reglas transaccionales

1. Resolver empresa activa y validar que variante y ubicación pertenecen a ella.
2. Validar cantidad positiva, unidad compatible y motivo/documento.
3. Buscar la existencia por `(company_id, variant_id, location_id)` y bloquearla
   dentro de la transacción; crear la fila si no existe.
4. Entradas, devoluciones y ajustes positivos suman; salidas y ajustes negativos
   restan.
5. Una salida o ajuste negativo no puede dejar la cantidad en negativo.
6. Insertar cabecera y líneas y actualizar la existencia en la misma transacción.
7. Ante una clave de idempotencia repetida, devolver el movimiento existente sin
   volver a aplicar el efecto.
8. Una reversión genera un movimiento `reversal` compensatorio; si ya existe una
   reversión para el original, responder conflicto.

## 7. Matriz de pruebas

| ID | Caso | Resultado |
| --- | --- | --- |
| T-04-01 | Stock de la empresa activa | Solo registros autorizados |
| T-04-02 | Stock de otra empresa | No visible (404) |
| T-04-03 | Entrada válida | Stock incrementado una vez |
| T-04-04 | Reintento de entrada con la misma clave | Sin duplicar movimiento ni stock |
| T-04-05 | Salida menor o igual al saldo | Descuento correcto |
| T-04-06 | Salida superior al saldo | 409 `INSUFFICIENT_STOCK` sin cambios |
| T-04-07 | Dos salidas consecutivas sobre el saldo | La segunda rechazada |
| T-04-08 | Devolución parcial | Movimiento independiente |
| T-04-09 | Devolución sin origen válido | 422 |
| T-04-10 | Ajuste sin motivo | 422 |
| T-04-11 | Ajuste sin permiso | 403 |
| T-04-12 | Reversión de movimiento | Compensación enlazada |
| T-04-13 | Revertir dos veces | 409 `ALREADY_REVERSED` |
| T-04-14 | Error en líneas | Rollback completo |
| T-04-15 | Cantidad decimal | Resultado con 4 decimales |
| T-04-16 | Borrar movimiento confirmado | Prohibido |

## 8. Frontend

- `InventoryStockPage`: tabla de existencias con filtros por producto, variante y
  ubicación; estados de carga, vacío y error.
- `InventoryMovementsPage`: historial con tipo, fecha, usuario, motivo y detalle.
- Formularios de entrada, salida, devolución y ajuste; confirmación explícita y
  bloqueo visual del doble envío (sin sustituir la idempotencia del backend).
- Diálogo de reversión con motivo y visualización del movimiento original.

## 9. Definition of Ready

- [x] Catálogo, variantes, unidades y ubicaciones operativos (Sprints 2–3).
- [x] Autenticación, permisos y `TenantScope` operativos (Sprint 1).
- [x] DP-03 implementada conforme al ESTANDAR (ratificación formal pendiente).
- [x] DP-01, DP-05 y DD-03 delimitados para el alcance del sprint.
- [ ] Contratos y errores revisados antes de implementar.

## 10. Definition of Done

- [ ] US-0401 a US-0406 cumplen sus criterios.
- [ ] Migraciones, índices únicos y restricciones revisados.
- [ ] Toda mutación de stock pasa por `InventoryMovementService`.
- [ ] Operaciones transaccionales, idempotentes y sin stock negativo.
- [ ] Movimientos confirmados inmutables; reversiones compensatorias y únicas.
- [ ] Autorización y aislamiento multiempresa probados.
- [ ] React con estados de carga, error y vacío.
- [ ] Pruebas de API y UI pasan; documentación actualizada.

## 11. Orden de implementación propuesto

1. Migraciones, modelos, factories y `InventoryMovementService` (entrada/salida).
2. US-0401 y US-0403 con pruebas de stock y saldo insuficiente.
3. US-0402 con idempotencia y pruebas de reintento.
4. US-0405 (ajustes) y US-0404 (devoluciones).
5. US-0406 (reversión) y pruebas de trazabilidad.
6. Frontend de stock, movimientos y formularios.
7. Cierre: README del Sprint 4, índice y `CHANGELOG`.

## 12. Pendientes y riesgos

| Riesgo / pendiente | Mitigación |
| --- | --- |
| Ratificación formal de DP-03 | Mantener 4 decimales/HALF_UP y registrar la decisión |
| Concurrencia real | Probar en MySQL con bloqueo de filas; en SQLite validar secuencialmente |
| Idempotencia entre procesos | Restricción única `(company_id, idempotency_key)` + transacción |
| Reversión de movimientos con posteriores | Aplicar la regla de compensación sin borrar historial |
| Ventas y compras | Mantener las salidas/entradas genéricas hasta Sprint 5 |
