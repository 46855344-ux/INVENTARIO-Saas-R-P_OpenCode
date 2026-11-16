# README — Sprint 8 «Chatbot de solo lectura»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-08
**Fecha:** 2026-11-16
**Estado:** Cerrado técnicamente — US-0802 diferida y MySQL pendiente
**Plan:** [`SDD/PLAN_SPRINT_8_CHATBOT_LECTURA.md`](./SDD/PLAN_SPRINT_8_CHATBOT_LECTURA.md)

## 1. Resumen

Se incorporó un asistente determinista de solo lectura para consultar stock,
agotados, mínimos, salidas y resumen. No usa LLM externo, SQL libre ni permite
mutaciones. Las respuestas se calculan con servicios existentes y respetan
empresa y permisos.

| Historia | Resultado |
| --- | --- |
| US-0801 | Consultas de inventario en lenguaje natural con evidencia y fecha |
| US-0802 | **Diferida**: sin fuente verificable de temporada; no se simulan datos |

## 2. Contratos

| Método | Ruta | Permiso |
| --- | --- | --- |
| `POST` | `/api/v1/assistant/query` | `assistant.use` |
| `GET` | `/api/v1/assistant/suggestions` | `assistant.use` |

Respuesta: `intent`, `answer`, `evidence`, `status`, `read_only` y `generated_at`.
Permisos de dominio: `inventory.view` para stock/agotados y `metrics.view` para
mínimos, salidas y resumen.

## 3. Seguridad

- Solo lectura; no crea, ajusta, revierte ni confirma.
- Rechaza verbos de escritura, SQL y “ignora permisos/otra empresa”.
- Sin acceso directo del modelo a MySQL.
- `chat_queries` guarda solo intención, estado, duración, actor y fecha; **no**
  preguntas ni respuestas.
- Aislamiento por `TenantContext`.

## 4. UI/UX

- Ruta `/assistant`, aviso de solo lectura, sugerencias e historial de sesión.
- Estados de carga, error y sin permisos; mensajes con `aria-live`.
- Navegación por teclado y etiquetas accesibles.

## 5. Evidencia

| Verificación | Resultado |
| --- | --- |
| Backend | 155 pruebas, 608 aserciones — OK |
| Pint | Sin diferencias — OK |
| Frontend | 78 pruebas — OK |
| Lint / build | OK (advertencias de efectos, sin errores) |
| Migración demo | `chat_queries` aplicada |

## 6. Pendientes

- Validación con MySQL real.
- US-0802 (temporada) requiere fuente aprobada.
- Integración futura de LLM con gateway intercambiable y evaluación de costos.
- Sistema de diseño completo.

## 7. Commits

```text
1e5233d docs(sprint-8): planificar chatbot de solo lectura
```

## 8. Próximo paso

**Sprint 9 — Estabilización y producción**, sujeto a criterios de aceptación,
respaldos, seguridad y despliegue.
