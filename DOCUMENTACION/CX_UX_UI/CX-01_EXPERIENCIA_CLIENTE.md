# CX-01 — Experiencia del cliente del SaaS

**Proyecto:** SaaS Inversiones R&P | **Versión:** 1.0 | **Estado:** estrategia propuesta  
**Fuente de contexto:** Canvas Ejecutivo y Documento Maestro v1.1.  
**Nota:** métricas, canales y acuerdos comerciales de este documento son **propuestas para validar**, no condiciones reales ya implementadas.

## 1. Objetivo

Diseñar la experiencia de una empresa cliente **antes, durante y después** de usar la plataforma de inventarios, desde el primer contacto hasta la permanencia en el SaaS. A diferencia de UX (tareas dentro de la aplicación), CX comprende atención, incorporación, confianza, soporte, capacitación y continuidad.

## 2. Contexto y públicos

El piloto descrito en los documentos es Inversiones R&P: tienda de manualidades/pasamanería/bisutería/textiles, con tienda y almacén separados y control inicialmente manual. La futura comercialización a terceros es una **proyección del modelo SaaS**, no evidencia de clientes activos.

**Interesados:** propietario/administrador de empresa, personal de almacén/ventas, equipo de soporte del SaaS y responsables de seguridad y continuidad.

## 3. Mapa de experiencia (Customer Journey)

| Fase | Objetivo del cliente | Punto de contacto | Fricción potencial | Acción CX propuesta |
|---|---|---|---|---|
| Descubrimiento | Entender utilidad | información comercial/demostración | beneficios vagos | explicar stock por variante y ubicación |
| Evaluación | Saber si encaja | demo, caso piloto | confusión de alcance | mostrar capacidades y exclusiones MVP |
| Alta/invitación | Acceder seguro | correo, Google, Facebook | cuentas duplicadas | elegir método e informar vínculo requerido |
| Onboarding | Crear primeros datos | asistente y ayuda | categorías/ubicaciones confusas | guía gradual por roles |
| Uso habitual | Operar inventario | React web | errores de cantidad/unidad | flujos claros y confirmaciones |
| Incidencia | Recuperar operación | ayuda/soporte | falta de trazabilidad | mostrar código, estado y proceso de atención |
| Seguimiento | Evaluar beneficios | métricas y reportes | datos no comprendidos | explicar indicadores y períodos |
| Renovación | Decidir continuidad | plan/suscripción | precio/alcance incierto | comunicar plan, límites y cambios |
| Baja/exportación | Controlar continuidad | soporte y configuración | posible pérdida de datos | procedimiento documentado por definir |

## 4. Experiencia de primer acceso

**Secuencia propuesta:** invitación o alta aprobada → elección de método de login → resolución de identidad interna → pertenencia a empresa/rol → bienvenida contextual → configuración del catálogo/unidades/ubicaciones → primera operación de inventario.

**Decisión pendiente:** si usuarios nuevos pueden crear una empresa sin invitación. Google/Facebook **no crean acceso a inventarios automáticamente**. El primer acceso debe explicar esa situación sin revelar información de otras empresas.

## 5. Onboarding por rol

- **Administrador:** empresa, usuarios autorizados, parámetros, unidades, ubicaciones y políticas de stock.
- **Almacén/caja:** localización de variantes, entrada, salida y traslado mediante ejemplos supervisados.
- **Vendedor:** consulta de existencias y ubicación; no revelar márgenes si carece de permiso.

La capacitación puede usar datos ficticios y entorno de pruebas; evitar ajustes sobre stock real sin autorización.

## 6. Soporte y comunicación

**Canales a evaluar:** centro de ayuda dentro del SaaS, correo de soporte y guía descargable. No inventar números ni prometer SLA aún no aprobados.

**Clasificación propuesta:** bloqueo de acceso; stock inconsistente; operación rechazada; error de interfaz; consulta de uso. Cada caso debe incluir `tenant_id` interno de forma segura, operación/fecha y evidencia sin tokens ni datos sensibles. Soporte nunca debe consultar datos de otros tenants sin autorización auditada.

## 7. Experiencia ante fallos

| Incidente | Respuesta al usuario | Acción operativa |
|---|---|---|
| Fallo Google/Facebook | alternativa de login y mensaje claro | inspeccionar integración sin exponer secretos |
| Pérdida temporal de conexión | informar estado sin confirmar movimiento ficticio | comprobar operación antes de reintentar |
| Stock insuficiente | explicar cantidad/ubicación | corregir operación sin eludir backend |
| Usuario sin permisos | indicar acceso restringido | solicitar gestión de rol por canal formal |
| Errores de facturación/pagos | no aplicar flujo inexistente | fuera del alcance del MVP original |

## 8. Indicadores CX sugeridos

| Indicador | Definición propuesta | Frecuencia | Estado |
|---|---|---|---|
| CSAT | porcentaje de respuestas satisfechas tras soporte | mensual | meta por aprobar |
| Tiempo al primer valor | del acceso autorizado al primer movimiento válido | por incorporación | meta por aprobar |
| Activación | empresas que concluyen checklist inicial | mensual | meta por aprobar |
| Tasa de incidencias de login | errores reales/intentados, desglosados por método | semanal | línea base pendiente |
| Resolución en primer contacto | casos cerrados sin recontacto | mensual | línea base pendiente |
| Retención de empresas | empresas activas en períodos consecutivos | mensual | definir suscripción activa |

**No guardar información analítica identificable innecesaria**; definir consentimiento, privacidad y acceso a dashboards.

## 9. Alcance por etapa

**Sprints 0–5 (reportados):** auditar experiencia actual, documentar fricciones de acceso, catálogo, unidades, inventario, compra y traslado.  
**Sprints 6–9 (planificados):** incorporar ayudas contextuales en métricas, alertas, conteos y chatbot de solo lectura; completar pruebas de aceptación y guía de soporte.  
**Cambio nuevo:** autenticación Google y Facebook; gestionar como incremento nuevo, sin falsificar cierre de Sprint 1.

## 10. Historias y requisitos

| ID CX propuesto | Historia | Criterios de aceptación |
|---|---|---|
| CX-HU-01 | Como administrador nuevo quiero comprender los primeros pasos | guía por rol con estado de avance |
| CX-HU-02 | Como usuario quiero saber por qué mi login social no da acceso aún | mensaje de cuenta pendiente y canal de resolución |
| CX-HU-03 | Como operador quiero resolver un error de movimiento | mensaje accionable y referencia sin pérdida de datos |
| CX-HU-04 | Como cliente quiero conocer límites del plan | explicación coherente con plan aprobado |
| CX-HU-05 | Como empresa quiero recibir ayuda sin exponer mis datos | soporte sujeto a autorización y auditoría |

## 11. Plan de investigación

1. Entrevistar a administrador y personal de almacén/ventas del piloto, con permiso.
2. Observar una búsqueda, entrada, salida y traslado en el entorno real.
3. Registrar obstáculos, sin datos personales ni inventarios sensibles innecesarios.
4. Prototipar mejoras y compararlas en prueba moderada.
5. Definir línea base antes de aprobar metas numéricas.

## 12. Decisiones pendientes

- Canales de soporte efectivos, horarios y niveles de servicio.
- Política de invitación versus alta pública; aprobación de tenant.
- Recopilación y retención de feedback y eventos analíticos.
- Planes, tarifas, límites y cancelación; no inventar oferta comercial.
- Política de exportación/baja y tratamiento de datos.

## 13. Checklist OpenCode

- [ ] Verificar onboarding y mensajes reales.
- [ ] Vincular fricciones a issues/historias.
- [ ] Redactar ayudas para roles existentes.
- [ ] Definir medición con privacidad.
- [ ] Validar con usuarios antes de marcar CX como cumplido.
