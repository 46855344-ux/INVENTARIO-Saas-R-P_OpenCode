# README — Sprint 4 «Núcleo de inventario»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-04 — Núcleo de inventario
**Iteración:** Sprint 4
**Versión del documento:** 1.0
**Fecha:** 2026-11-13
**Estado:** Cerrado técnicamente — validación con MySQL/Docker y ratificación formal de DP-03 pendientes
**Canvas de referencia:** [`DOCS_origen/CANVAS_SPRINT_4_INVENTARIO_CENTRAL.md`](./DOCS_origen/CANVAS_SPRINT_4_INVENTARIO_CENTRAL.md)
**Plan del sprint:** [`SDD/PLAN_SPRINT_4_INVENTARIO_CENTRAL.md`](./SDD/PLAN_SPRINT_4_INVENTARIO_CENTRAL.md)

---

## 1. Resumen ejecutivo

El Sprint 4 incorporó el **núcleo transaccional del inventario**: consulta de
existencias por variante y ubicación, y registro de entradas, salidas,
devoluciones, ajustes y reversiones. Todas las mutaciones de stock pasan por un
único servicio transaccional, con idempotencia, prevención de saldo negativo y
trazabilidad histórica.

| Historia | Objetivo | Resultado |
| --- | --- | --- |
| US-0401 | Consultar existencias | **Completado** |
| US-0402 | Registrar entradas | **Completado** |
| US-0403 | Registrar salidas | **Completado** |
| US-0404 | Registrar devoluciones | **Completado** |
| US-0405 | Ajustes autorizados | **Completado** |
| US-0406 | Revertir movimientos | **Completado** |

## 2. Contexto de entrada

- Sprint 3 cerrado: unidades, conversiones y ubicaciones operativas.
- Catálogo y variantes disponibles desde el Sprint 2; se añadió un listado
  global `GET /api/v1/variants` para alimentar los formularios de inventario.
- `TenantContext`, `TenantScope` y `BelongsToCompany` operativos desde el Sprint 1.
- La guardia de desactivación de ubicaciones con stock quedó activa al existir
  la tabla `stock_balances`.

## 3. Decisiones de entrada

| Tema | Decisión | Fundamento |
| --- | --- | --- |
| Precisión de cantidades | `DECIMAL(15,4)` | ESTANDAR §6.7 |
| Redondeo | `HALF_UP` a 4 decimales | ESTANDAR; DP-03 pendiente de ratificación formal |
| Ubicación en el movimiento | `location_id` obligatorio en toda línea que afecte stock (DD-03) | ESTANDAR §4.4 y SDD-04 |
| Nombres internos | `stock_balances`, `stock_movements`, `stock_movement_lines` | ESTANDAR §4.1–4.2 |
| Idempotencia | Cabecera `Idempotency-Key`, única por empresa | Evita duplicar stock en reintentos |
| Concurrencia | Transacción + bloqueo de la fila de existencia | Saldo consistente ante movimientos simultáneos |
| Reversión | Movimiento compensatorio enlazado; una sola reversión por movimiento | `reversal_of_movement_id` único |
| Devoluciones | Movimiento independiente con `original_movement_id` opcional | Trazabilidad sin alterar el histórico |
| Permisos | `inventory.view`, `inventory.move`, `inventory.adjust`, `inventory.approve` | Matriz del Sprint 1 |
| Fuera de alcance | Ventas (DP-01), recepción formal de compras y traslados | Delimitado para el Sprint 5 |

> **DP-03:** se mantiene factor y cantidades decimales con redondeo `HALF_UP` a
> 4 decimales conforme al ESTANDAR. La ratificación formal de DP-03 sigue
> pendiente del Product Owner.
>
> **DP-01 / DP-05:** la salida es genérica y no se vincula a ventas; el ajuste
> exige `inventory.adjust` y el flujo de escalado sin administrador queda para
> una decisión posterior.

## 4. Modelo de datos y arquitectura

| Tabla | Campos principales |
| --- | --- |
| `stock_balances` | `id`, `company_id`, `variant_id`, `location_id`, `quantity DECIMAL(15,4)`, `reserved_quantity DECIMAL(15,4)`, timestamps |
| `stock_movements` | `id`, `company_id`, `type`, `status`, `user_id`, `reason`, `document_reference`, `original_movement_id`, `reversal_of_movement_id`, `idempotency_key`, `confirmed_at`, timestamps |
| `stock_movement_lines` | `id`, `company_id`, `stock_movement_id`, `variant_id`, `location_id`, `unit_id`, `quantity`, `normalized_quantity`, `direction` (`in`/`out`), timestamps |

- Restricciones: `UNIQUE (company_id, variant_id, location_id)` en existencias;
  `UNIQUE (company_id, idempotency_key)` y `UNIQUE (reversal_of_movement_id)`
  en movimientos.
- `InventoryMovementService` concentra toda mutación de stock dentro de
  `DB::transaction`, bloquea la fila de existencia y aplica `HALF_UP` con
  `Brick\Math\BigDecimal`.
- Movimientos confirmados inmutables: no se editan ni se eliminan; las
  correcciones se hacen con reversión compensatoria.
- `reserved_quantity` queda preparado para reservas del Sprint 5.

## 5. Historias y criterios de aceptación

| Historia | Criterios cubiertos | Evidencia |
| --- | --- | --- |
| US-0401 | Stock de la empresa activa, filtros por variante/ubicación y búsqueda, paginación, aislamiento | `tests/Feature/Inventory/InventoryStockTest.php` |
| US-0402 | Entrada válida, idempotencia por cabecera, cantidad decimal, aislamiento de variante y ubicación | `tests/Feature/Inventory/InventoryMovementTest.php` |
| US-0403 | Salida con descuento correcto, rechazo 409 `INSUFFICIENT_STOCK`, saldo intacto | `tests/Feature/Inventory/InventoryMovementTest.php` |
| US-0404 | Devolución como movimiento independiente con referencia de origen | `tests/Feature/Inventory/InventoryMovementTest.php` |
| US-0405 | Ajuste con sentido `in`/`out`, motivo obligatorio y permiso `inventory.adjust` | `tests/Feature/Inventory/InventoryMovementTest.php` |
| US-0406 | Reversión compensatoria enlazada, 409 `ALREADY_REVERSED` y 409 `NOT_REVERSIBLE` | `tests/Feature/Inventory/InventoryMovementTest.php` |

## 6. Contratos API

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
| `GET` | `/api/v1/variants` | `catalog.view` |

Todos los endpoints requieren `auth:sanctum` y `X-Company-Id`. Las operaciones
de escritura exigen la cabecera `Idempotency-Key`.

Errores de dominio del sprint:

| Código | HTTP | Situación |
| --- | --- | --- |
| `INSUFFICIENT_STOCK` | 409 | La salida o el ajuste negativo supera el saldo |
| `ALREADY_REVERSED` | 409 | El movimiento ya tiene una reversión |
| `NOT_REVERSIBLE` | 409 | Se intenta revertir una reversión |
| `VALIDATION_ERROR` | 422 | Datos inválidos o falta `Idempotency-Key` |
| `FORBIDDEN` | 403 | Falta el permiso requerido |
| `NOT_FOUND` | 404 | Variante, ubicación o movimiento de otra empresa |

## 7. Entregables y evidencia

| Entregable | Ubicación | Estado |
| --- | --- | --- |
| Migraciones de inventario | `backend/database/migrations/2026_11_13_09*.php` | Completo |
| Modelos, policies, requests, resources y service | `backend/app/Modules/Inventory/` | Completo |
| API de existencias y movimientos | `backend/routes/api.php` | Completo |
| Listado global de variantes | `backend/app/Modules/Catalog/` | Completo |
| Pantalla React «Inventario» | `frontend/src/features/inventory/` | Completo |
| Servicio frontend y tipos | `frontend/src/services/inventory.ts`, `frontend/src/types/` | Completo |
| Pruebas de API y UI | `backend/tests/Feature/Inventory/`, `frontend/tests/` | Completo |

## 8. Evidencia de pruebas

| Verificación | Comando | Resultado |
| --- | --- | --- |
| Pruebas backend | `php artisan test` | 107 pruebas, 370 aserciones — OK |
| Estilo backend | `vendor/bin/pint --test` | Sin diferencias — OK |
| Pruebas frontend | `npm run test` | 48 pruebas — OK |
| Lint frontend | `npm run lint` | Sin errores — OK |
| Build frontend | `npm run build` | Compilado — OK |

Matriz del sprint: T-04-01 a T-04-16 cubiertos, incluidos idempotencia,
`INSUFFICIENT_STOCK`, doble reversión, reversión de reversión, aislamiento
multiempresa y cantidades decimales.

## 9. Definition of Done — estado

- [x] US-0401 a US-0406 cumplen sus criterios de aceptación.
- [x] Migraciones, índices únicos y restricciones implementados.
- [x] Toda mutación de stock pasa por `InventoryMovementService`.
- [x] Operaciones transaccionales, idempotentes y sin stock negativo.
- [x] Movimientos confirmados inmutables; reversiones compensatorias y únicas.
- [x] Autorización y aislamiento multiempresa probados.
- [x] React maneja carga, error, vacío y bloqueo de doble envío.
- [x] Pruebas de API y UI pasan; documentación actualizada.
- [ ] Ratificación formal de DP-03 — pendiente del Product Owner.
- [ ] Validación contra MySQL real y `docker compose` — pendiente por entorno.

## 10. Guion de demostración

1. Iniciar sesión con `admin@empresa-demo.test` / `password`.
2. Abrir **Inventario → Existencias** y comprobar el saldo por variante y ubicación.
3. Registrar una **entrada** de `10` unidades de una variante en `ALM-01`.
4. Abrir **Movimientos** y verificar tipo, usuario, motivo, líneas y fecha.
5. Registrar una **salida** de `4` unidades y comprobar el saldo en `6.0000`.
6. Intentar una salida de `7` unidades y ver el rechazo por stock insuficiente.
7. Registrar un **ajuste** negativo con motivo y comprobar el nuevo saldo.
8. Registrar una **devolución** y verificar que es un movimiento independiente.
9. **Revertir** el movimiento original y comprobar la compensación y el enlace.
10. Intentar revertir dos veces y ver el conflicto `ALREADY_REVERSED`.

## 11. Pendientes y bloqueos

| Pendiente | Motivo | Próximo paso |
| --- | --- | --- |
| Ratificar DP-03 | Decisión de negocio sobre precisión/redondeo | Validar la escala de 4 decimales con el PO |
| Validar MySQL/Docker | Entorno no instalado | Instalar Docker o MySQL |
| Concurrencia real | SQLite valida secuencialmente | Probar bloqueo de filas en MySQL |
| Reservas de stock | `reserved_quantity` aún sin uso | Implementar con compras/traslados |
| Sprint 5 | Compras y traslados | Recepción de compras y transferencias entre ubicaciones |

## 12. Documentos generados

| Documento | Contenido |
| --- | --- |
| [`SDD/PLAN_SPRINT_4_INVENTARIO_CENTRAL.md`](./SDD/PLAN_SPRINT_4_INVENTARIO_CENTRAL.md) | Plan, decisiones, modelo, contratos y pruebas |
| [`SDD/SDD-04_DISENO_BASE_DATOS.md`](./SDD/SDD-04_DISENO_BASE_DATOS.md) | Modelo de datos y nota de implementación |
| [`SDD/SDD-05_DISENO_API.md`](./SDD/SDD-05_DISENO_API.md) | Contratos de la API actualizados |
| [`README_SPRINT_4.md`](./README_SPRINT_4.md) | Este documento de cierre |

## 13. Commits del sprint

```text
186103a docs(sprint-4): planificar el nucleo de inventario
b6c8af7 feat(inventory): nucleo de inventario y movimientos (US-0401 a US-0406)
```

## 14. Próximo sprint

**Sprint 5 — Compras y traslados (EP-05):** recepción de compras, traslados entre
ubicaciones y uso de reservas de stock. Ver el canvas
[`DOCS_origen/CANVAS_SPRINT_5_COMPRAS_TRASLADOS.md`](./DOCS_origen/CANVAS_SPRINT_5_COMPRAS_TRASLADOS.md).

## 15. Documentos relacionados

- [`README_SPRINT_0.md`](./README_SPRINT_0.md) — cierre del Sprint 0.
- [`README_SPRINT_1.md`](./README_SPRINT_1.md) — cierre del Sprint 1.
- [`README_SPRINT_2.md`](./README_SPRINT_2.md) — cierre del Sprint 2.
- [`README_SPRINT_3.md`](./README_SPRINT_3.md) — cierre del Sprint 3.
- [`README.md`](./README.md) — índice general de la documentación.
- [`../CHANGELOG.md`](../CHANGELOG.md) — historial de cambios.
