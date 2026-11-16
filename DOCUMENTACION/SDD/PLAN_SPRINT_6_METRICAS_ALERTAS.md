# Plan — Sprint 6 «Métricas y alertas»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-06 — Métricas y alertas
**Versión:** 1.0
**Fecha:** 2026-11-16
**Estado:** En implementación
**Decisiones ratificadas:** umbral por variante + ubicación; notificaciones solo in-app

---

## 1. Objetivo

Construir un panel de indicadores operativos y un sistema de alertas de stock
mínimo e inactividad, calculados desde datos persistidos, con aislamiento por
empresa, permisos, deduplicación y evaluación programada.

## 2. Alcance

| Historia | Prioridad | Resultado |
| --- | --- | --- |
| US-0601 | P1 | Indicadores, detalle de stock y listados operativos |
| US-0602 | P1 | Reglas por variante + ubicación, alertas y ciclo abierto/resuelto |
| US-0603 | P2 | Comando programado con corrida, conteos y errores; sin correo externo |

Fuera de alcance: valorización oficial (DP-02), márgenes (DP-09), notificaciones
por correo, conteos físicos y auditoría integral (Sprint 7).

## 3. Decisiones

| Tema | Decisión |
| --- | --- |
| DD-02 | Umbral y regla por **variante + ubicación** |
| DP-02 | «Valor total» se muestra como pendiente; no se calcula cifra oficial |
| DP-09 | No se exponen márgenes |
| Notificaciones | Solo in-app: la alerta visible en el listado |
| Periodo | 30 días por defecto, parametrizable entre 1 y 365 |
| Agotados | Filas de `stock_balances` con cantidad 0; no se inventan combinaciones |
| Más salidas | Movimientos `exit` confirmados; no se incluyen traslados internos |
| Sin movimiento | Existencia > 0 sin movimientos de esa variante/ubicación en el periodo |
| Deduplicación | `UNIQUE(company_id, dedup_key)`; una alerta activa por regla |
| Permisos | `metrics.view`, `alerts.view`, `alerts.manage` |

## 4. Modelo de datos

| Tabla | Campos |
| --- | --- |
| `alert_rules` | `id`, `company_id`, `type`, `variant_id`, `location_id`, `threshold`, `inactivity_days`, `status`, timestamps |
| `stock_alerts` | `id`, `company_id`, `alert_rule_id`, `type`, `variant_id`, `location_id`, `message`, `status`, `detected_at`, `resolved_at`, `dedup_key`, timestamps |
| `alert_evaluation_runs` | `id`, `company_id`, `started_at`, `finished_at`, `status`, `evaluated`, `generated`, `resolved`, `error_summary`, timestamps |

Restricciones: `UNIQUE(company_id, type, variant_id, location_id)` en reglas;
`UNIQUE(company_id, dedup_key)` en alertas; índices por estado/fecha y regla.

## 5. Contratos API

| Método | Ruta | Permiso |
| --- | --- | --- |
| `GET` | `/api/v1/metrics/overview` | `metrics.view` |
| `GET` | `/api/v1/metrics/stock-detail` | `metrics.view` |
| `GET` | `/api/v1/alerts` | `alerts.view` |
| `PATCH` | `/api/v1/alerts/{id}/status` | `alerts.manage` |
| `GET` | `/api/v1/alert-rules` | `alerts.view` |
| `POST` | `/api/v1/alert-rules` | `alerts.manage` |
| `PATCH` | `/api/v1/alert-rules/{id}` | `alerts.manage` |

Todas las rutas requieren `auth:sanctum` y `X-Company-Id`. Contrato de error
existente (`error.code`, `message`, `details`, `trace_id`).

## 6. Evaluación

1. Recorrer empresas activas y reglas `active`.
2. Para mínimo: comparar `stock_balances.quantity <= threshold`.
3. Para inactividad: buscar el último movimiento de la variante/ubicación;
   si no hay en `inactivity_days`, abrir alerta.
4. Si la condición sigue activa, mantener la alerta (no duplicar).
5. Si desaparece, resolver la alerta abierta.
6. Registrar la corrida en `alert_evaluation_runs` con conteos y error resumido.
7. Programar `alerts:evaluate` a diario; sin correo externo.

## 7. UI/UX aplicada

- Ruta `/metrics`, con pestañas **Indicadores** y **Alertas**.
- Tarjetas en el orden solicitado; «Valor total» muestra el estado pendiente.
- Tabla de detalle paginada y listados de agotados, mínimos, más salidas e inactivos.
- Alertas con filtros por tipo/estado, badges de estado y acción resolver/reabrir.
- Reglas con formulario por variante + ubicación, umbral o días.
- Estados de carga, vacío, error y sin permisos; sin cifras ficticias.
- Tablas semánticas, encabezados, botones con texto y confirmación al resolver.
- Sin rediseño global; se reutilizan estilos y patrones existentes.

## 8. Pruebas

- Agregaciones con empresas A/B y datos conocidos.
- Permisos negativos y aislamiento por tenant.
- Reglas: validación, unicidad y edición.
- Alertas: apertura, no duplicación, resolución y reapertura.
- Inactividad y mínimo con fechas controladas.
- Comando programado: conteos, corrida y error.
- Frontend: métricas, alertas, reglas, estados vacío/error y permisos.
- Regresión de Sprints 0–5 e incremento v1.1.

## 9. DoR / DoD

- [x] Decisiones DD-02, DP-02 y canal in-app delimitados.
- [x] Historias y contratos definidos.
- [x] Permisos propuestos.
- [ ] Implementación y pruebas completas.
- [ ] Validación con MySQL real (entorno pendiente).

## 10. Riesgos

| Riesgo | Mitigación |
| --- | --- |
| Cifras de valorización sin aprobar | Mostrar estado pendiente, no calcular |
| Duplicación de alertas | Clave única y transacción por regla |
| Crecimiento de consultas | Índices por empresa, estado y fecha; límites de periodo |
| Notificaciones repetidas | Solo in-app y deduplicación persistente |
| Diferencias SQLite/MySQL | Pruebas con SQLite y validación pendiente en MySQL |
