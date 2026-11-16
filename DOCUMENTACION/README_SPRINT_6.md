# README — Sprint 6 «Métricas y alertas»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-06 — Métricas y alertas
**Iteración:** Sprint 6
**Versión del documento:** 1.0
**Fecha:** 2026-11-16
**Estado:** Cerrado técnicamente — validación con MySQL y valorización DP-02 pendientes
**Canvas de referencia:** [`DOCS_origen/CANVAS_SPRINT_6_METRICAS_ALERTAS.md`](./DOCS_origen/CANVAS_SPRINT_6_METRICAS_ALERTAS.md)
**Plan del sprint:** [`SDD/PLAN_SPRINT_6_METRICAS_ALERTAS.md`](./SDD/PLAN_SPRINT_6_METRICAS_ALERTAS.md)

---

## 1. Resumen ejecutivo

El Sprint 6 incorporó indicadores operativos, detalle de stock y alertas de
stock mínimo e inactividad. Las reglas se configuran por variante + ubicación,
las alertas se deduplican y un comando programado las evalúa a diario. Las
notificaciones son **solo in-app**; no se envía correo externo.

| Historia | Objetivo | Resultado |
| --- | --- | --- |
| US-0601 | Indicadores de inventario | **Completado** |
| US-0602 | Alertas de mínimo e inactividad | **Completado** |
| US-0603 | Tareas programadas y trazabilidad | **Completado** |

## 2. Decisiones del sprint

| Tema | Decisión |
| --- | --- |
| DD-02 | Regla y umbral por **variante + ubicación** |
| DP-02 | «Valor total» se muestra como pendiente; no se calcula cifra oficial |
| DP-09 | No se exponen márgenes |
| Notificaciones | Solo in-app; sin correo externo |
| Periodo | 30 días por defecto (1–365) |
| Agotados | Existencia 0 en filas registradas; no se inventan combinaciones |
| Más salidas | Movimientos `exit` confirmados; traslados internos excluidos |
| Sin movimiento | Existencia > 0 sin movimientos en el periodo |
| Deduplicación | `UNIQUE(company_id, dedup_key)`; una alerta activa por regla |
| Permisos | `metrics.view`, `alerts.view`, `alerts.manage` |

## 3. Modelo de datos

| Tabla | Campos principales |
| --- | --- |
| `alert_rules` | `id`, `company_id`, `type`, `variant_id`, `location_id`, `threshold`, `inactivity_days`, `status` |
| `stock_alerts` | `id`, `company_id`, `alert_rule_id`, `type`, `variant_id`, `location_id`, `message`, `status`, `detected_at`, `resolved_at`, `dedup_key` |
| `alert_evaluation_runs` | `id`, `company_id`, `started_at`, `finished_at`, `status`, `evaluated`, `generated`, `resolved`, `error_summary` |

## 4. Contratos API

| Método | Ruta | Permiso |
| --- | --- | --- |
| `GET` | `/api/v1/metrics/overview` | `metrics.view` |
| `GET` | `/api/v1/metrics/stock-detail` | `metrics.view` |
| `GET` | `/api/v1/alerts` | `alerts.view` |
| `PATCH` | `/api/v1/alerts/{id}/status` | `alerts.manage` |
| `GET` | `/api/v1/alert-rules` | `alerts.view` |
| `POST` | `/api/v1/alert-rules` | `alerts.manage` |
| `PATCH` | `/api/v1/alert-rules/{id}` | `alerts.manage` |

Todas las rutas requieren `auth:sanctum` y `X-Company-Id`.

## 5. UI/UX aplicada

- Ruta `/metrics` con pestañas **Indicadores** y **Alertas**.
- Tarjetas en el orden solicitado, con estado pendiente para valorización.
- Tablas semánticas de más salidas, inactividad y detalle paginado.
- Alertas con filtros por estado/tipo y acciones resolver/reabrir.
- Reglas con formulario por variante + ubicación y edición.
- Estados de carga, vacío, error y sin permisos; sin cifras ficticias.
- Se reutilizan estilos y patrones existentes; no se rediseñó el sistema global.

## 6. Evaluación y automatización

- Comando `php artisan alerts:evaluate` (opción `--company=`).
- Programado a diario a las 06:00 con `withoutOverlapping()`.
- Cada corrida registra empresa, fechas, estado, evaluadas, generadas, resueltas
  y error resumido.
- Si la condición continúa, la alerta se mantiene; si desaparece, se resuelve.
- Reabrir una condición resuelta reutiliza la misma alerta (sin duplicar).

## 7. Evidencia

| Verificación | Resultado |
| --- | --- |
| Pruebas backend | 147 pruebas, 562 aserciones — OK |
| Pint | Sin diferencias — OK |
| Pruebas frontend | 71 pruebas — OK |
| Lint frontend | Sin errores (3 advertencias de efectos) — OK |
| Build frontend | Compilado — OK |
| Migraciones demo | `alert_rules`, `stock_alerts`, `alert_evaluation_runs` aplicadas |

**Recorrido real:** regla de mínimo `CAF-500` en `ALM-CENTRAL` con umbral 25;
la existencia 20 generó una alerta y la corrida registró
`evaluadas=2, generadas=1`. El panel mostró 6 tarjetas, stock mínimo 1 y el
estado pendiente de valorización.

## 8. Guion de demostración

1. Ingresar con `admin@empresa-demo.test` / `password`.
2. Abrir **Métricas → Indicadores** y revisar las tarjetas y el detalle.
3. Verificar que «Valor total» indica pendiente por DP-02.
4. Abrir **Alertas** y ver la alerta de stock mínimo de `CAF-500`.
5. Resolver la alerta y comprobar el cambio de estado.
6. Crear una regla nueva por variante + ubicación y ejecutar
   `php artisan alerts:evaluate`; ver la alerta generada.
7. Repetir el comando y comprobar que no se duplica.

## 9. Pendientes y bloqueos

| Pendiente | Motivo |
| --- | --- |
| Validar con MySQL real | Entorno no instalado |
| Ratificar DP-02 | Valorización oficial no aprobada |
| Notificaciones externas | Solo in-app en este sprint |
| Sistema de diseño completo | Aplicación incremental; rediseño transversal pendiente |
| Sprint 7 | Conteos físicos y auditoría integral |

## 10. Commits del sprint

```text
22a2504 docs(sprint-6): planificar metricas y alertas
aede531 feat(metrics): metricas y alertas de inventario (US-0601 a US-0603)
```

## 11. Próximo sprint

**Sprint 7 — Conteos y auditoría (EP-07):** conteos físicos, diferencias,
aprobación de ajustes y bitácora de auditoría.
