# Plan — Sprint 8 «Chatbot de solo lectura»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-08
**Fecha:** 2026-11-16
**Estado:** En implementación

## 1. Objetivo

Permitir consultas de inventario en lenguaje natural con respuestas fundamentadas,
estrictamente de lectura, filtradas por empresa y permisos.

## 2. Alcance

| Historia | Resultado |
| --- | --- |
| US-0801 | Consultas de stock, agotados, mínimos, más salidas y resumen |
| US-0802 | **Fuera de alcance**: no hay fuente verificable de temporada; no se simulan datos |

## 3. Decisiones

| Tema | Decisión |
| --- | --- |
| LLM externo | No se integra proveedor en este sprint; se usa un **parser determinista** con intenciones permitidas |
| Escritura | Prohibida: el asistente no crea, ajusta, revierte ni confirma |
| SQL libre | Prohibido; solo servicios de lectura existentes |
| Prompt injection | Detección de verbos de escritura, SQL y “ignora permisos”; respuesta de rechazo |
| Permisos | `assistant.use` + permiso del dominio (`inventory.view`/`metrics.view`) |
| Persistencia | Solo metadatos en `chat_queries`; no se guardan preguntas ni respuestas completas |
| Temporada | Diferida hasta contar con fuente aprobada |

## 4. Contratos

| Método | Ruta | Permiso |
| --- | --- | --- |
| `POST` | `/api/v1/assistant/query` | `assistant.use` |
| `GET` | `/api/v1/assistant/suggestions` | `assistant.use` |

Respuesta: `{ data: { intent, answer, evidence, generated_at, read_only } }`.

## 5. Intenciones permitidas

| Intención | Consulta | Permiso |
| --- | --- | --- |
| `stock_lookup` | Stock por producto/SKU | `inventory.view` |
| `out_of_stock` | Agotados | `inventory.view` |
| `low_stock` | Bajo mínimo | `metrics.view` |
| `top_outflows` | Más salidas | `metrics.view` |
| `summary` | Resumen operativo | `metrics.view` |
| `help` | Ayuda y ejemplos | — |

## 6. Modelo

`chat_queries`: `id`, `company_id`, `user_id`, `intent`, `status`, `duration_ms`, `created_at`.
Sin texto de la pregunta ni de la respuesta.

## 7. UI/UX

- Ruta `/assistant` con historial local de la sesión, sugerencias y aviso de solo lectura.
- Estados de carga, error, sin resultados y sin permisos.
- Mensajes claros; no se muestran datos de otras empresas ni detalles internos.
- Navegación por teclado y etiquetas accesibles.

## 8. Pruebas

- Consultas válidas por intención y permisos.
- Rechazo de escritura, SQL, prompt injection y consultas cruzadas.
- Aislamiento por empresa.
- Registro mínimo sin preguntas/respuestas.
- Frontend: envío, estados, sugerencias y modo lectura.

## 9. Riesgos

| Riesgo | Mitigación |
| --- | --- |
| Respuestas inventadas | Parser determinista y evidencia desde servicios reales |
| Fuga entre empresas | TenantContext y permisos en cada intención |
| Prompt injection | Lista negra y rechazo sin ejecutar consultas |
| Costos/privacidad de LLM | Sin proveedor externo en este sprint |
