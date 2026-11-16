# Plan — Sprint 7 «Conteos físicos y auditoría»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-07
**Fecha:** 2026-11-16
**Estado:** En implementación

## 1. Objetivo

Registrar conteos físicos por ubicación, comparar contra el stock teórico,
revisar diferencias y aprobar ajustes mediante `InventoryMovementService`.
Mantener una bitácora de auditoría consultable e inmutable.

## 2. Alcance

| Historia | Resultado |
| --- | --- |
| US-0701 | Conteo abierto con cantidades físicas, responsable y diferencias; no cambia stock |
| US-0702 | Revisión y aprobación con motivo; ajustes transaccionales y sin doble aprobación |
| US-0703 | Auditoría filtrable de creación y aprobación de conteos |

Fuera de alcance: ajustes automáticos al guardar, lectores móviles, aprobación
por línea y auditoría de todos los módulos (se amplía incrementalmente).

## 3. Decisiones

| Tema | Decisión |
| --- | --- |
| Estados | `open` → `reviewing` → `approved` |
| Stock teórico | Se congela al crear el conteo desde `stock_balances` |
| Ajuste | Solo al aprobar; una entrada/salida por línea con diferencia |
| Doble aprobación | Bloqueada por estado y transacción |
| Motivo | Obligatorio al enviar a revisión/aprobar |
| Ubicación | Un conteo pertenece a una ubicación de la empresa |
| Permisos | `counts.view`, `counts.manage`, `counts.approve`, `audit.view` |
| Auditoría | `audit_logs` append-only; sin tokens ni payloads sensibles |

## 4. Modelo

| Tabla | Campos |
| --- | --- |
| `stock_counts` | `id`, `company_id`, `location_id`, `status`, `user_id`, `reviewed_by`, `approved_by`, `counted_at`, `reviewed_at`, `approved_at`, `reason`, `notes`, timestamps |
| `stock_count_items` | `id`, `company_id`, `stock_count_id`, `variant_id`, `theoretical_quantity`, `counted_quantity`, `difference`, timestamps |
| `audit_logs` | `id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `context`, `ip`, `created_at` |

## 5. Contratos

| Método | Ruta | Permiso |
| --- | --- | --- |
| `GET` | `/api/v1/stock-counts` | `counts.view` |
| `POST` | `/api/v1/stock-counts` | `counts.manage` |
| `GET` | `/api/v1/stock-counts/{id}` | `counts.view` |
| `PATCH` | `/api/v1/stock-counts/{id}/status` | `counts.manage`/`counts.approve` |
| `GET` | `/api/v1/audit-events` | `audit.view` |

## 6. Flujo de aprobación

1. Validar estado `reviewing` y permiso `counts.approve`.
2. Bloquear el conteo dentro de una transacción.
3. Por cada diferencia distinta de cero, registrar ajuste con
   `reference_type = stock_count` y `reference_id`.
4. Marcar `approved`, usuario/fecha y auditoría.
5. Ante error, rollback completo; una segunda aprobación responde 409.

## 7. UI/UX

- Ruta `/counts` con pestañas **Conteos** y **Auditoría**.
- Listado filtrable, formulario con ubicación, variantes y cantidad física,
  diferencia calculada y confirmación.
- Detalle con comparativa teórico/físico y acciones enviar a revisión/aprobar.
- Estados de carga, vacío, error y permisos; tablas semánticas.
- Auditoría con filtros por acción, entidad y fecha.

## 8. Pruebas

- Crear conteo no modifica stock y congela teórico.
- Transiciones válidas e inválidas; doble aprobación 409.
- Aprobación genera ajustes correctos y usa el servicio central.
- Aislamiento por empresa y permisos.
- Auditoría inmutable, filtros y paginación.
- Frontend: formulario, diferencias, aprobación y estados.

## 9. Riesgos

| Riesgo | Mitigación |
| --- | --- |
| Diferencias por movimientos durante el conteo | Documentar foto al crear; revisar antes de aprobar |
| Doble ajuste | Estado + transacción + restricción de referencia |
| Auditoría sensible | Contexto mínimo, sin tokens |
| MySQL real | Validación pendiente de entorno |
