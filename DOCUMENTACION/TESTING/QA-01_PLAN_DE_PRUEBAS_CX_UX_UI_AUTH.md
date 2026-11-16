# QA-01 — Plan de pruebas para CX/UX/UI y autenticación social

**Proyecto:** SaaS Inversiones R&P | **Versión:** 1.0 | **Estado:** plan propuesto, ejecución sin verificar  
**Base:** backlog Sprints 0–9; usuario reporta avance hasta Sprint 5; nuevos US-0111…0115, US-UX01…07.

## 1. Objetivos

Validar que incorporar Google/Facebook y mejoras de interfaz **no rompa** las funciones ya desarrolladas de usuarios, productos, unidades, ubicaciones, entradas/salidas, compras y traslados, ni vulneren reglas de inventario o aislamiento empresarial.

## 2. Alcance y límites

**Prioridad inmediata:** regresión de Sprints 0–5, login social, flujos UX, permisos y seguridad. **Más adelante:** métricas/alertas (S6), conteos/auditoría (S7), chatbot lectura (S8), preparación productiva (S9). No certificar las funciones futuras como probadas actualmente.

## 3. Estrategia de niveles

| Nivel | Tecnología o técnica orientativa | Qué valida |
|---|---|---|
| Unitario backend | PHPUnit o Pest según repo | servicios, políticas y reglas |
| Unitario frontend | Vitest según repo | formularios, estados y componentes |
| Integración API/BD | entorno MySQL de pruebas | permisos, migraciones, transacciones |
| E2E | herramienta existente o seleccionada | operación React↔Laravel |
| Seguridad | tests negativos, revisión manual y SAST | auth, OAuth, tenant, privilegios |
| Accesibilidad | axe u otra + teclado/lector | WCAG 2.2 AA objetivo |
| Usabilidad | sesiones con usuarios representativos | éxito, errores y comprensión |
| CX | evaluación de onboarding/soporte | facilidad de incorporación y resolución |

No imponer herramientas nuevas si el repositorio ya tiene equivalentes aceptables.

## 4. Matriz de regresión Sprints 0–5

| ID | Caso | Resultado esperado |
|---|---|---|
| REG-01 | Login correo/contraseña | mantiene acceso autorizado |
| REG-02 | Usuario sin permiso intenta acción restringida | backend rechaza |
| REG-03 | Consulta de empresa A con sesión empresa B | no expone datos |
| REG-04 | Crear/editar producto/variante y códigos | integridad y mensajes correctos |
| REG-05 | Conversión de unidad | cantidad y precisión según política aprobada |
| REG-06 | Ubicación inválida/otro tenant | rechazo sin alteración |
| REG-07 | Registrar entrada | existencia y movimiento coherentes |
| REG-08 | Salida mayor que stock | rechazo y stock sin cambios |
| REG-09 | Dos salidas concurrentes sobre stock limitado | sin sobreventa |
| REG-10 | Reversión de movimiento | compensación con trazabilidad, sin borrado |
| REG-11 | Compra con varios ítems/costos | historial preservado y permisos respetados |
| REG-12 | Traslado entre ubicaciones | consistencia origen/destino y auditoría |

## 5. Matriz de autenticación social

| ID | Escenario | Resultado esperado | Prioridad |
|---|---|---|---|
| AUTH-T01 | Google vinculado: flujo válido | misma identidad interna, sesión propia | P0 |
| AUTH-T02 | Facebook vinculado: flujo válido | misma identidad, permisos intactos | P0 |
| AUTH-T03 | `state` ausente o alterado | rechazo, sin sesión | P0 |
| AUTH-T04 | Callback URL no permitida | rechazo | P0 |
| AUTH-T05 | Proveedor cancela | retorno seguro y sin sesión nueva | P1 |
| AUTH-T06 | Facebook no suministra email | no inventa correo ni fuerza fusión | P0 |
| AUTH-T07 | Email coincidente, identidad nueva | solicita vinculación segura | P0 |
| AUTH-T08 | Proveedor+ID externo ligado a otro usuario | rechazo y unicidad | P0 |
| AUTH-T09 | Usuario social sin membresía al tenant | acceso inventario denegado | P0 |
| AUTH-T10 | Usuario suspendido | acceso denegado | P0 |
| AUTH-T11 | Desvincular último método | rechazo | P1 |
| AUTH-T12 | Expiración sesión y logout | sesión inválida, sin permisos residuales | P0 |
| AUTH-T13 | OAuth con dos pestañas/intentos | no confundir sesiones/cuentas | P1 |
| AUTH-T14 | Logs y errores | sin tokens/secretos/PII excesiva | P0 |

Usar **mocks/fixtures** para pruebas automatizadas; configurar una app real de pruebas únicamente en entorno controlado y con cuentas autorizadas.

## 6. Matriz UX/UI

| ID | Flujo/interfaz | Verificación |
|---|---|---|
| UX-T01 | Login por teclado | botones, foco y etiquetas accesibles |
| UX-T02 | Login móvil | contenido usable sin desbordes |
| UX-T03 | Productos/categorías | buscar, crear, editar y preservar información |
| UX-T04 | Unidades/ubicaciones | conversión y validaciones visibles |
| UX-T05 | Salida de stock | resumen y confirmación; errores preservan datos |
| UX-T06 | Compra | costos mostrados solo a roles permitidos |
| UX-T07 | Traslado | origen/destino y estado comprensibles |
| UX-T08 | Formularios en error | campo asociado y foco apropiado |
| UX-T09 | Tabla vacía/cargando/error | mensajes comprensibles, no spinner permanente |
| UX-T10 | Zoom/contraste/lector de pantalla | auditoría WCAG 2.2 AA objetivo |

## 7. Pruebas de CX

- Medir si el administrador comprende el inicio y el acceso mediante invitación.
- Observar si el operador encuentra cómo registrar entrada/salida sin ayuda no prevista.
- Validar mensajes ante proveedor caído, cuenta no vinculada y permisos insuficientes.
- Analizar feedback de soporte sin recopilar datos innecesarios.
- Registrar línea base antes de usar metas como SUS ≥80 o éxito ≥95%.

## 8. Casos BDD de referencia

```gherkin
Característica: Aislamiento empresarial después de OAuth
  Escenario: Acceso social sin permiso a otra empresa
    Dado que el usuario tiene una identidad Google válida y acceso a empresa A
    Cuando solicita existencias de empresa B
    Entonces el backend rechaza la solicitud
    Y no devuelve existencias de empresa B

Característica: Salida de inventario
  Escenario: Rechazar salida superior al stock
    Dado que existe una variante con 3 unidades disponibles
    Cuando un usuario autorizado solicita retirar 5 unidades
    Entonces no se confirma el movimiento
    Y la existencia permanece sin cambios
```

## 9. Entorno y datos de prueba

- Base de datos de test aislada por entorno, con empresas A y B y usuarios de roles diferentes.
- Datos representativos: variantes similares, múltiples unidades, ubicaciones y costos históricos.
- Ninguna prueba automatizada sobre producción; migraciones y operaciones peligrosas solo en ambiente controlado.
- Variables de proveedor mediante secretos seguros, nunca archivos versionados.

## 10. Criterios de entrada

- Requisitos, flujos y ADR pertinentes identificados.
- Funcionalidad bajo prueba disponible en ambiente seguro.
- Datos y usuarios de prueba preparados.
- Criterios de aceptación y trazabilidad definidos.

## 11. Criterios de salida propuestos

- Cero defectos críticos abiertos de aislamiento, autorización, stock o OAuth.
- Regresión de funciones afectadas sin fallos bloqueantes.
- Evidencias adjuntas a cada historia implementada.
- Accesibilidad revisada con pruebas automáticas y manuales, con excepciones documentadas.
- Aprobación explícita antes de declarar terminado un incremento.

## 12. Registro de defectos y evidencias

| Campo | Uso |
|---|---|
| ID del defecto | identificador único |
| Historia/caso | US-..., AUTH-T..., REG-... |
| Ambiente y versión | commit/tag, sistema y navegador |
| Reproducción | pasos y datos ficticios |
| Resultado real/esperado | diferencias observadas |
| Severidad y estado | crítico/alto/medio/bajo; abierto/cerrado |
| Evidencia | captura, log depurado o reporte test |
| Responsable | asignación verificada |

## 13. Matriz de trazabilidad

| Historia | Documento | Casos |
|---|---|---|
| US-0111 | AUTH-01 | AUTH-T01, T03, T04, T07, T09 |
| US-0112 | AUTH-01 | AUTH-T02, T03, T06, T09 |
| US-0113 | AUTH-01 | AUTH-T07, T08, T11 |
| US-0114 | AUTH-01/SEGURIDAD | AUTH-T03, T04, T10, T12–T14 |
| US-0115 | UX-01/UI-01 | UX-T01, T02, T08 |
| US-UX02…05 | UX-01 | UX-T03…07 y REG-04…12 |
| US-UX06 | UI-01 | UX-T09 y métricas Sprint 6 |

## 14. Ejecución sugerida en OpenCode

1. Descubrir tests que ya existen y ejecutarlos sin alterar datos reales.
2. Reportar línea base de fallos antes de cambios.
3. Agregar pruebas negativas de identidad y tenant.
4. Incorporar pruebas de componentes y accesibilidad.
5. Implementar solo funcionalidades aprobadas.
6. Comparar resultados antes/después; documentar cobertura real, defectos y riesgos.

## 15. Checklist de aceptación

- [ ] Evidencia de auditoría del repositorio.
- [ ] Regresión Sprint 0–5 ejecutada.
- [ ] Autenticación social validada en condiciones normales y negativas.
- [ ] Multiempresa aislada en todas las rutas afectadas.
- [ ] UX y accesibilidad con evidencia.
- [ ] Documentación y backlog sincronizados.
