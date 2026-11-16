# README — Sprint 7 «Conteos físicos y auditoría»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-07
**Fecha:** 2026-11-16
**Estado:** Cerrado técnicamente — validación con MySQL pendiente
**Plan:** [`SDD/PLAN_SPRINT_7_CONTEOS_AUDITORIA.md`](./SDD/PLAN_SPRINT_7_CONTEOS_AUDITORIA.md)

## 1. Resumen

Se implementaron conteos físicos por ubicación, comparación contra el stock
teórico, revisión y aprobación de diferencias, y auditoría append-only.

| Historia | Resultado |
| --- | --- |
| US-0701 | Conteo abierto con foto teórica; no modifica stock |
| US-0702 | Revisión y aprobación con ajustes transaccionales |
| US-0703 | Auditoría filtrable de creación, revisión y aprobación |

## 2. Modelo y contratos

| Tabla | Uso |
| --- | --- |
| `stock_counts` | Cabecera, ubicación, estados, responsables y fechas |
| `stock_count_items` | Teórico, físico y diferencia por variante |
| `audit_logs` | Eventos append-only con contexto mínimo |

| Método | Ruta | Permiso |
| --- | --- | --- |
| `GET` | `/api/v1/stock-counts` | `counts.view` |
| `POST` | `/api/v1/stock-counts` | `counts.manage` |
| `GET` | `/api/v1/stock-counts/{id}` | `counts.view` |
| `PATCH` | `/api/v1/stock-counts/{id}/status` | `counts.manage`/`counts.approve` |
| `GET` | `/api/v1/audit-events` | `audit.view` |

## 3. Flujo

1. Crear conteo: congela el stock teórico y registra el físico; no ajusta stock.
2. Enviar a revisión con motivo.
3. Aprobar con permiso `counts.approve`: por cada diferencia se registra un
   ajuste con `reference_type = stock_count` vía `InventoryMovementService`.
4. Una segunda aprobación responde 409. Todo queda en auditoría.

## 4. UI/UX

- Ruta `/counts` con pestañas **Conteos** y **Auditoría**.
- Formulario con ubicación y líneas de variante/cantidad física.
- Tabla con teórico, físico y diferencia; acciones Revisar/Aprobar.
- Auditoría filtrable por acción.
- Estados de carga, vacío, error y permisos; tablas semánticas.

## 5. Evidencia

| Verificación | Resultado |
| --- | --- |
| Backend | 151 pruebas, 588 aserciones — OK |
| Pint | Sin diferencias — OK |
| Frontend | 76 pruebas — OK |
| Lint / build | OK (advertencias de efectos, sin errores) |
| Migraciones demo | `stock_counts`, `stock_count_items`, `audit_logs` aplicadas |

## 6. Pendientes

- Validación con MySQL real.
- Aprobación por línea y conteos multiubicación.
- Auditoría de todos los módulos (se amplía incrementalmente).
- Sistema de diseño completo.

## 7. Commits

```text
c9c5f03 docs(sprint-7): planificar conteos fisicos y auditoria
```

## 8. Próximo sprint

**Sprint 8 — Chatbot de solo lectura**, sujeto a políticas de datos y permisos.
