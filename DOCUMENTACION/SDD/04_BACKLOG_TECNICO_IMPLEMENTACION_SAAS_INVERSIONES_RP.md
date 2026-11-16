# SaaS Inversiones R&P — Backlog técnico de implementación

**Documento:** 04 — Backlog técnico de implementación  
**Versión documental:** 1.1 (actualización propuesta)  
**Fecha:** 15 de noviembre de 2026  
**Fuente principal:** `BACKLOG_TECNICO_IMPLEMENTACION_SAAS_INVERSIONES_RP.pdf` (versión 1.0, noviembre de 2026).  
**Tecnologías previstas:** React, Laravel, MySQL, API REST/JSON, Docker, GitHub Actions, PHPUnit/Pest, Vitest, SonarQube.  
**Estado:** propuesta documental para conciliación con el repositorio de OpenCode.  
**Avance declarado por el responsable:** desarrollo trabajado hasta el Sprint 5. **No equivale a aceptación o verificación de todas sus historias.**

> **Control de cambios:** esta versión mantiene la estructura y los identificadores `EP-00…EP-09` y `US-0001…US-0904` del backlog original. Añade propuestas de experiencia del cliente (CX), experiencia de usuario (UX), interfaz (UI) y autenticación con Google/Facebook. Las incorporaciones se marcan como **NUEVO v1.1**; no se atribuyen al contenido original. La fuente documental no acredita el estado real del código.

## 1. Objetivo

Mantener un Product Backlog verificable y ejecutable mediante entregas verticales: **migración → modelo → servicio Laravel → endpoint API → pantalla React → pruebas**. Guiar la continuación desde el avance reportado en Sprint 5 sin reescribir retrospectivamente las actas de cierre ni alterar la lógica de inventario aprobada.

### 1.1 Alcance original del MVP

- Identidad, acceso, roles, permisos y aislamiento por empresa.
- Catálogo: categorías, marcas, productos, variantes y códigos.
- Unidades, conversiones y ubicaciones de almacén.
- Existencias, entradas, salidas, devoluciones, ajustes y reversiones.
- Proveedores, compras, costos históricos y traslados.
- Métricas y alertas de inventario.
- Conteos físicos, diferencias y auditoría.
- Chatbot de **solo lectura**, sujeto a permisos, con consulta de temporada.
- Pruebas, seguridad, despliegue y recuperación.

### 1.2 Exclusiones originales del MVP

POS completo, caja y pagos; facturación electrónica y contabilidad; aplicación móvil nativa; escritura desde chatbot; compras automatizadas; microservicios prematuros; cobros comerciales de suscripciones SaaS (salvo estructura aprobada para una fase posterior).

### 1.3 Ampliación propuesta de alcance — v1.1

1. Acceso tradicional por correo y contraseña, más **Google** y **Facebook**.
2. Vinculación segura de identidades externas, manejo de errores y desvinculación controlada.
3. Diseño de experiencia **CX/UX/UI** para flujos de los Sprints 1–5 y planificación de los Sprints 6–9.
4. Accesibilidad, diseño adaptable, estados de carga/error/vacío, validación de formularios y pruebas de usabilidad.

**Nota:** la integración social se aprueba como *requisito propuesto*; su prioridad, sprint de ejecución y aceptación deben ser confirmados por Product Owner. No se extiende el alcance a otros proveedores de identidad.

## 2. Arquitectura y principios que no deben romperse

| Capa | Enfoque original | Requisito de ampliación v1.1 |
|---|---|---|
| Frontend | React; formularios, navegación, tablas, consumo de API | Componentes accesibles, responsive y botones Google/Facebook |
| Backend | Laravel; reglas, validaciones, autorización | Servicio de identidad externa y validación segura del callback |
| Persistencia | MySQL; integridad, transacciones y aislamiento | Identidades sociales vinculadas a usuario, con restricciones e índices |
| Comunicación | API REST/JSON versionada | Contratos de inicio/callback, estado de sesión, error y desvinculación |
| QA | PHPUnit/Pest, Vitest, aceptación | Regresión + pruebas UX/accesibilidad + casos OAuth negativos |
| Seguridad | SonarQube, OWASP Dependency-Check | Estado OAuth, CSRF, secretos, cookies/sesiones, auditoría y permisos |
| Entrega | Docker, GitHub Actions | Variables de OAuth por ambiente, sin secretos comprometidos |

**Principios originales obligatorios:**

- Monolito modular para el MVP.
- Un solo servicio central Laravel responsable de toda mutación de stock.
- Movimientos confirmados inmutables: corrección mediante reversión o movimiento compensatorio.
- Transacciones y bloqueos para operaciones concurrentes que alteren existencias.
- Stock por empresa, variante y ubicación conforme al modelo aprobado.
- Control del `empresa_id`/tenant desde la arquitectura, nunca desde datos arbitrarios del cliente.
- Políticas de autorización del backend para cada operación; React no es autoridad de permisos.
- Chatbot solo de lectura, aislado por usuario/empresa/permisos.

**Principios nuevos v1.1:** identidad autenticada **no implica** afiliación a un tenant; el proveedor externo no determina roles; vincular cuentas exige prueba segura de control de ambas identidades; evitar guardar tokens de proveedor salvo necesidad documentada y protegida; no sobrescribir una cuenta por coincidencia simple de email.

## 3. Épicas originales y avance

| Épica | Nombre original | Sprint | Estado de referencia |
|---|---|---:|---|
| EP-00 | Fundación técnica y decisiones | 0 | Trabajado según usuario; pendiente de auditoría |
| EP-01 | Identidad, acceso y multiempresa | 1 | Trabajado; ampliación social propuesta |
| EP-02 | Catálogo de productos | 2 | Trabajado; mejoras UX/UI propuestas |
| EP-03 | Unidades y ubicaciones | 3 | Trabajado; mejoras UX/UI propuestas |
| EP-04 | Núcleo de inventario | 4 | Trabajado; regresión obligatoria |
| EP-05 | Compras y traslados | 5 | Avance reportado; revisar pruebas |
| EP-06 | Métricas y alertas | 6 | Previsto por backlog original; sin verificación |
| EP-07 | Conteos físicos y auditoría | 7 | Previsto por backlog original; sin verificación |
| EP-08 | Consulta inteligente | 8 | Previsto por backlog original; sin verificación |
| EP-09 | Calidad y salida a producción | 9 | Previsto por backlog original; sin verificación |

**Estados permitidos para la auditoría del repositorio:** `No auditado`, `Pendiente`, `En progreso`, `Implementado sin pruebas`, `Verificado`, `Bloqueado`. No marcar `Verificado` solo por existir un README o un plan de sprint.

## 4. Product Backlog original — inventario de historias

Se preservan los IDs, nombres, épicas y secuencia originales. El detalle íntegro de tareas y criterios de aceptación originales debe consultarse en el PDF fuente y cotejarse durante la auditoría; esta tabla funciona como inventario trazable, no como sustitución literal del texto fuente.

| ID | Historia original | Sprint | Prioridad operativa |
|---|---|---:|---|
| US-0001 | Acordar arquitectura y estructura del repositorio | 0 | P0 |
| US-0002 | Definir modelo de datos y estrategia multiempresa | 0 | P0 |
| US-0003 | Preparar entorno local y pipeline inicial | 0 | P0 |
| US-0004 | Cerrar decisiones de negocio que afectan el diseño | 0 | P0 |
| US-0101 | Iniciar sesión y cerrar sesión | 1 | P0 |
| US-0102 | Administrar usuarios y asignar roles | 1 | P0 |
| US-0103 | Aplicar permisos por módulo y acción | 1 | P0 |
| US-0104 | Aislar datos por empresa | 1 | P0 |
| US-0201 | Gestionar categorías y marcas | 2 | Según backlog original |
| US-0202 | Registrar y mantener productos | 2 | Según backlog original |
| US-0203 | Gestionar variantes y códigos | 2 | Según backlog original |
| US-0301 | Configurar unidades de medida | 3 | Según backlog original |
| US-0302 | Configurar conversiones de unidades | 3 | Según backlog original |
| US-0303 | Administrar ubicaciones de almacén | 3 | Según backlog original |
| US-0401 | Consultar existencias | 4 | Según backlog original |
| US-0402 | Registrar entradas de inventario | 4 | Según backlog original |
| US-0403 | Registrar salidas y validar stock disponible | 4 | Según backlog original |
| US-0404 | Registrar devoluciones | 4 | Según backlog original |
| US-0405 | Realizar ajustes autorizados | 4 | Según backlog original |
| US-0406 | Revertir movimientos confirmados | 4 | Según backlog original |
| US-0501 | Gestionar proveedores | 5 | Según backlog original |
| US-0502 | Registrar compras y costos históricos | 5 | Según backlog original |
| US-0503 | Trasladar existencias entre ubicaciones | 5 | Según backlog original |
| US-0601 | Visualizar indicadores de inventario | 6 | Según backlog original |
| US-0602 | Configurar alertas de stock mínimo e inactividad | 6 | Según backlog original |
| US-0603 | Programar tareas y notificaciones | 6 | Según backlog original |
| US-0701 | Ejecutar conteos físicos | 7 | Según backlog original |
| US-0702 | Revisar y aprobar diferencias | 7 | Según backlog original |
| US-0703 | Consultar trazabilidad y auditoría | 7 | Según backlog original |
| US-0801 | Consultar inventario mediante chatbot de solo lectura | 8 | Según backlog original |
| US-0802 | Consultar información de temporada | 8 | Según backlog original |
| US-0901 | Automatizar pruebas y controles de calidad | 9 | Según backlog original |
| US-0902 | Preparar despliegue productivo | 9 | P0 |
| US-0903 | Configurar copias de seguridad y recuperación | 9 | P0 |
| US-0904 | Ejecutar pruebas de aceptación y estabilización | 9 | P0 |

### 4.1 Validaciones esenciales para las historias originales

- **US-0101–0104:** autenticación, sesión segura, roles, autorización en servidor y separación efectiva de empresas.
- **US-0201–0203:** consistencia de catálogo, permisos y códigos únicos según la regla aprobada.
- **US-0301–0303:** precisión de conversiones, integridad de unidades y ubicaciones por empresa.
- **US-0401–0406:** control de stock, no sobreventa, trazabilidad y reversión sin borrar movimientos confirmados.
- **US-0501–0503:** recepción, costo histórico y traslados atómicos entre ubicaciones.
- **US-0601–0603:** indicadores calculados con definiciones aprobadas; umbrales y avisos sin exposición entre empresas.
- **US-0701–0703:** conteos conciliados, autorizaciones y bitácoras de auditoría.
- **US-0801–0802:** el chatbot nunca ejecuta escrituras ni expone datos fuera del ámbito permitido.
- **US-0901–0904:** pruebas, HTTPS, gestión de secretos, despliegue reversible, backups restaurables y aceptación documentada.

## 5. Historias nuevas — autenticación social (NUEVO v1.1)

> IDs `US-0111…US-0115` nuevos y reservados para esta actualización. **No modifican** `US-0101…US-0104` ni el acta histórica de Sprint 1. **Sprint propuesto:** bolsa de cambios posterior al Sprint 5, sujeto a planificación y capacidad.

### US-0111 — Iniciar sesión con Google

**Historia:** Como usuario autorizado, quiero ingresar con Google para acceder a mi cuenta del SaaS sin crear una contraseña adicional.

- **Prioridad propuesta:** P1; elevar a P0 solo si se aprueba como condición de salida.
- **Dependencias:** US-0101, US-0104, decisión de modelo de identidad, credenciales OAuth y URLs de callback por entorno.
- **Criterios de aceptación:**
  1. La interfaz ofrece `Continuar con Google` y mantiene correo/contraseña.
  2. Se valida en el servidor el flujo OpenID Connect, `state` y los identificadores/afirmaciones aplicables.
  3. Cuenta social no vinculada sigue un proceso seguro de alta o vinculación aprobado.
  4. Sin asociación válida a empresa/rol no se permite operar inventarios.
  5. Cancelación, proveedor no disponible y errores de callback generan mensajes seguros, sin filtrar secretos.
  6. Pruebas cubren éxito, cancelación, respuesta inválida, sesión expirada y aislamiento entre empresas.
- **Tareas:** configuración por ambiente; endpoint de inicio y callback; servicio de identidad; pantalla React; tests; documentación.

### US-0112 — Iniciar sesión con Facebook

**Historia:** Como usuario autorizado, quiero ingresar con Facebook para utilizar mi cuenta existente del sistema.

- **Prioridad propuesta:** P1.
- **Dependencias:** US-0101, US-0104 y configuración oficial de Meta.
- **Criterios de aceptación:**
  1. Botón `Continuar con Facebook` accesible en login.
  2. Backend valida el flujo OAuth y obtiene la identidad mediante mecanismos oficiales apropiados.
  3. No se presupone que Facebook suministre un email ni que esté verificado.
  4. No se crea acceso a otro tenant ni roles por el hecho de autenticarse.
  5. Se manejan permisos denegados, cancelación y errores del proveedor.
  6. Pruebas cubren respuestas incorrectas y protección de sesión.
- **Tareas:** registro de app Meta; configuración segura; controlador de callback; servicio; UI; pruebas.

### US-0113 — Vincular y desvincular identidades sociales

**Historia:** Como usuario autenticado, quiero gestionar mis métodos de acceso sin perder mi cuenta, mis permisos ni mi historial.

- **Prioridad propuesta:** P1.
- **Criterios de aceptación:**
  1. Una identidad externa única (`provider`, `provider_user_id`) no puede estar ligada a dos usuarios locales.
  2. No se vinculan cuentas existentes por coincidencia simple del email.
  3. La vinculación requiere una sesión autenticada y confirmación de control del proveedor, con comprobaciones antisuplantación.
  4. No se permite dejar al usuario sin método de acceso válido por una desvinculación.
  5. Los cambios quedan auditados sin guardar tokens secretos en bitácoras.
- **Tareas:** diseño de tabla e índices; servicio de vinculación; interfaz de cuenta; pruebas de colisión y concurrencia.

### US-0114 — Proteger el flujo OAuth y las sesiones

**Historia:** Como responsable de seguridad, quiero que el acceso social resista solicitudes forjadas, confusión de cuentas y filtración de secretos.

- **Prioridad propuesta:** P0 (seguridad transversal).
- **Criterios de aceptación:** `state` y protección CSRF donde aplique; validación de callback/redirecciones; HTTPS de producción; secretos fuera de Git; cookies seguras y reglas de expiración; separación multiempresa; ausencia de exposición de datos del proveedor en logs.
- **Tareas:** revisión de arquitectura real de sesión (por ejemplo, Sanctum si ya existe), pruebas negativas, configuración del ambiente y revisión de dependencias.

### US-0115 — Experiencia UX/UI del acceso y recuperación

**Historia:** Como usuario, quiero una pantalla de acceso clara, accesible y coherente con los tres métodos disponibles.

- **Prioridad propuesta:** P1.
- **Criterios de aceptación:** botones visibles y correctamente rotulados; navegación por teclado; estados de carga/error; diseño móvil; mensajes que no revelan si existe una cuenta; recuperación de contraseña tradicional preservada; componentes documentados.

## 6. Historias nuevas — CX, UX y UI (NUEVO v1.1)

> IDs `US-UX01…US-UX07` son identificadores de propuesta para no colisionar con los IDs numéricos existentes. Podrán renumerarse únicamente si el sistema de gestión de historias confirma disponibilidad.

| ID | Historia / propósito | Sprint de referencia | Prioridad sugerida | Evidencia mínima |
|---|---|---|---|---|
| US-UX01 | Sistema de diseño React: tipografía, colores, componentes y patrones accesibles | Transversal | P1 | Componentes, guía UI, pruebas |
| US-UX02 | Mejorar flujos de catálogo: crear/editar categorías, productos y variantes | Revisar S2 | P1 | Tareas completas sin regresiones |
| US-UX03 | Mejorar unidades, conversiones y ubicaciones | Revisar S3 | P1 | Errores claros, validaciones y accesibilidad |
| US-UX04 | Optimizar entradas/salidas y confirmaciones críticas de inventario | Revisar S4 | P0 en flujos críticos | Exactitud, advertencias y pruebas concurrentes |
| US-UX05 | Mejorar recepción de compras y traslados | Revisar S5 | P1 | Resumen de operación, errores y reversión según reglas |
| US-UX06 | Diseñar métricas, alertas, tablas y estados sin carga confusa | S6 | P1 | Definición de indicadores y pruebas de accesibilidad |
| US-UX07 | Definir experiencia CX: incorporación, ayuda, soporte y retroalimentación | Transversal / estabilización | P2 | Mapa de experiencia y validación con usuarios |

### Criterios de aceptación transversales UX/UI

- Flujos principales compatibles con teclado y tecnologías de asistencia cuando corresponda.
- Objetivo de accesibilidad: **WCAG 2.2 nivel AA**, validación automatizada complementada con pruebas manuales.
- Formularios con mensajes junto al campo, estados de envío y prevención de duplicidad.
- Tablas con búsqueda y filtros consistentes; manejo de vacío, error y carga.
- Confirmaciones específicas para salidas, ajustes, compras y traslados; no ocultar errores de autorización.
- Diseños adaptables al tamaño de pantalla sin exigir app móvil nativa.
- No revelar costos/márgenes a usuarios sin permisos, incluso en indicadores o vistas resumidas.
- Medir éxito de tareas y errores con usuarios reales antes de fijar metas cuantitativas definitivas.

## 7. Propuesta de planificación sin reabrir Sprints 0–5

| Bloque | Qué hacer | Condición de entrada | Resultado |
|---|---|---|---|
| Auditoría post-Sprint 5 | Contrastar planes, código, migraciones, endpoints, pantallas y pruebas | Repositorio disponible | Matriz real de estados y brechas |
| Cambio de identidad | Implementar US-0111 a US-0115 | Arquitectura y decisiones aprobadas | Login social seguro y probado |
| Mejoras UX/UI | Priorizar US-UX01 a US-UX05 por impacto y esfuerzo | Revisión de interfaz existente | Flujos mejorados sin romper negocio |
| Sprint 6 original | US-0601–0603, métricas y alertas, junto con UX aplicable | Decisiones de valoración y umbrales | Dashboard y alertas verificadas |
| Sprint 7 original | Conteos y auditoría | Política de conteo concurrente | Conciliación y trazabilidad |
| Sprint 8 original | Chatbot solo lectura | Política de datos y permisos | Consultas autorizadas |
| Sprint 9 original | QA, despliegue, backups, aceptación | Criterios de salida aprobados | MVP listo según aceptación |

**No se asigna artificialmente una fecha o un sprint definitivo a Google/Facebook**: el Product Owner debe priorizarlo frente al Sprint 6. Una alternativa es crear un sprint de cambios claramente identificado, sin renumerar los sprints cerrados.

## 8. Dependencias técnicas de base de datos

**Secuencia original de migraciones:**

1. Empresa.
2. Usuario, rol, permiso y pivotes.
3. Categoría, marca, producto, variante y códigos.
4. Unidad de medida y conversiones.
5. Ubicaciones.
6. Existencias.
7. Movimientos y detalles.
8. Proveedores, compras y detalles.
9. Traslados y detalles.
10. Conteos y detalles.
11. Auditoría y alertas.
12. Plan y suscripción solo si se aprueba fase comercial posterior.

**Ampliación v1.1:** evaluar migración incremental para `social_identities` o equivalente, sin asumir nombre físico definitivo. Campos sugeridos: identificador local, usuario local, proveedor, ID de usuario del proveedor, metadatos mínimos y marcas temporales; índice único por `(provider, provider_user_id)`. Respetar nombres, claves y estrategia de tenancy **reales** del repositorio. Evitar columnas de tokens innecesarias.

## 9. Servicios técnicos y contratos

**Servicios originales previstos:** `Auth / Access Policies`, `TenantContext / TenantScope`, `CatalogService`, `UnitConversionService`, `InventoryMovementService`, `PurchaseReceivingService`, `TransferService`, `InventoryMetricsService`, `InventoryAlertService`, `StockCountService`, `AuditService / Domain Events`, `ChatAssistantService`.

**Ampliaciones v1.1 propuestas:**

- Servicio de identidad federada integrado con el mecanismo de autenticación Laravel existente.
- Servicio de vinculación/desvinculación con comprobaciones de identidad y auditoría.
- Endpoints o rutas de inicio/callback por proveedor; deben respetar el contrato/API y el tipo de cliente realmente implementado.
- Pantalla y componentes de inicio de sesión React con estados consistentes.
- Pruebas unitarias, integración y E2E para éxito y rechazo, sin requerir secretos reales en CI.

**No** sustituir el sistema de autenticación actual ni generar APIs duplicadas sin evaluar la arquitectura existente.

## 10. Definition of Ready (DoR) — original más ampliación

Una historia está lista cuando tiene valor de negocio claro, criterios verificables, dependencias de datos/API/permisos/interfaz identificadas, decisiones necesarias resueltas, alcance estimable y diseños/contratos disponibles.

**Añadidos v1.1:** para login social, acuerdos sobre creación/vinculación, tenencia empresarial, proveedores y callback; para UX/UI, flujo actual documentado, casos de error, criterios de accesibilidad, mockup o especificación de comportamiento.

## 11. Definition of Done (DoD) — original más ampliación

Para considerar una historia terminada:

- Se cumplen y demuestran los criterios de aceptación.
- Migraciones, índices, integridad y relaciones revisadas.
- Reglas de negocio en Laravel, autorización aplicada en servidor.
- React utiliza contratos API y contempla estados de carga/error/vacío.
- Pruebas pertinentes exitosas en CI; revisión de seguridad y dependencias.
- Documentación técnica/API actualizada, revisión del código y aceptación del Product Owner.

**Añadidos v1.1:** evaluación de accesibilidad relevante, pruebas de integración del proveedor simuladas, pruebas de vinculación de cuentas y aislamiento entre tenants; evidencia de regresión de las funcionalidades de Sprints 0–5.

## 12. Decisiones pendientes del backlog original

Estas decisiones figuran como **pendientes en el documento fuente**, pero su resolución actual debe comprobarse en los ADR y el repositorio. No se deben reabrir automáticamente si ya cuentan con aprobación documentada.

| ID | Decisión original |
|---|---|
| DP-01 | Quién registra las salidas por venta |
| DP-02 | Método de valorización de inventario |
| DP-03 | Conversiones y redondeo |
| DP-04 | Conteos físicos simultáneos con movimientos |
| DP-05 | Quién autoriza en ausencia del administrador |
| DP-06 | Retención de datos y recuperación |
| DP-07 | Disponibilidad y rendimiento |
| DP-08 | Límites y reglas de planes SaaS |
| DP-09 | Precios y márgenes por variante |
| DP-10 | Importación de inventario histórico |
| DD-01 | Unicidad del email global o por empresa |
| DD-02 | Nivel de configuración de stock mínimo |
| DD-03 | Ubicación en detalle de movimiento |
| DD-04 | Roles/permisos globales o por empresa |
| DD-05 | Borrado lógico y relaciones de auditoría |

### 12.1 Decisiones nuevas v1.1 que requieren ADR

| ID nuevo | Decisión | Impacto |
|---|---|---|
| AD-01 | Estrategia de vinculación de cuentas sociales y alta inicial | Seguridad de identidad |
| AD-02 | Comportamiento cuando proveedor no entrega correo o no lo verifica | Identidad y UX |
| AD-03 | Relación entre usuario global, pertenencias a empresas y rol activo | Aislamiento multiempresa |
| AD-04 | Política de desvinculación cuando no queda método de ingreso | Continuidad de acceso |
| AD-05 | Flujo de sesión frontend/backend y configuración por dominio | OAuth, CSRF, cookies |
| AD-06 | Prioridad de nuevas historias respecto al Sprint 6 | Planificación y alcance |
| AD-07 | Definición y métricas de experiencia CX/UX/UI | Aceptación de diseño |

## 13. Riesgos y mitigaciones

| Riesgo | Mitigación |
|---|---|
| Exposición de datos entre empresas | Scopes backend y pruebas negativas entre tenants |
| Stock inconsistente por cambios de UI | Mantener servicio central, transacciones y pruebas de regresión |
| Doble descuento/concurrencia en salidas o traslados | Bloqueos, idempotencia cuando corresponda, pruebas concurrentes |
| Asociar incorrectamente cuentas por email | Vinculación explícita y validación de propiedad de ambas identidades |
| Configuración OAuth insegura | Validar estado, callback, redirección, sesiones y secretos por ambiente |
| Falta de correo/permisos del proveedor | Flujo alternativo seguro y mensajes comprensibles |
| Regresión al modificar pantallas de Sprints 1–5 | Pruebas de componente, integración y E2E en operaciones críticas |
| Métricas de valoración incorrectas | Cerrar DP-02 y DP-09 antes de publicar valores como definitivos |
| Conteos inconsistentes | Resolver DP-04 antes de Sprint 7 |
| Chatbot con acceso excesivo | Solo lectura, ámbito empresarial y permisos |
| Respaldos sin restauración validada | Simulacro y política de retención antes de producción |
| Crecimiento descontrolado del alcance | Control de cambios y prioridades P0/P1/P2 |

## 14. Matriz de trazabilidad y evidencia para OpenCode

| Cambio / requisito | Diseño que debe actualizarse | Código por revisar | Pruebas / evidencia |
|---|---|---|---|
| US-0111 Google | SDD-01, SDD-05, AUTH-01 | Login React, auth Laravel, configuración | OAuth simulado, login, aislamiento |
| US-0112 Facebook | SDD-01, SDD-05, AUTH-01 | Login React, auth Laravel, configuración | Cancelación, respuesta inválida |
| US-0113 Vinculación | SDD-04, AUTH-01, SEGURIDAD | Migraciones y servicios de identidad | Duplicados, colisión, desvinculación |
| US-0114 Seguridad social | ADR, SEGURIDAD, DEVSECOPS | Cookies/sesiones, secretos, callbacks | CSRF, redirect, tenant, regresión |
| US-0115 UX de login | UX/UI, MANUALES | Pantalla y componentes React | Accesibilidad, errores, móvil |
| US-UX02–05 Mejoras existentes | UX/UI y planes de Sprint 2–5 | Formularios, tablas, inventario, compras | E2E y no regresión |
| US-UX06 Métricas | Plan Sprint 6 / SDD | Dashboard y API de indicadores | Exactitud, roles, UX |
| US-UX07 CX | CX y MANUALES | Onboarding y soporte definidos | Validación de usuarios |

## 15. Procedimiento de auditoría de Sprint 0–5 en OpenCode

1. Inventariar código, migraciones, rutas, modelos, servicios, componentes, pipelines y tests.
2. Localizar `README_SPRINT_0…README_SPRINT_5`, planes de sprint y actas, sin tomarlos como prueba suficiente de implementación.
3. Para cada `US-0001…US-0503`, registrar archivo(s), endpoint(s), prueba(s), estado, incidencias y evidencia.
4. Verificar los invariantes de stock, aislamiento empresarial, autorización y reversiones.
5. Contrastar versiones del Documento Maestro, SDD-01, SDD-04, SDD-05 y este backlog.
6. Elaborar lista de cambios, migraciones seguras, pruebas de regresión y riesgo antes de editar código.
7. Someter nuevas historias al Product Owner y planificar su ejecución sin alterar el cierre histórico de los sprints.

### Plantilla de evidencia por historia

```markdown
| Historia | Estado real | Rutas/código | Pruebas ejecutadas | Riesgo | Responsable |
|---|---|---|---|---|---|
| US-0101 | No auditado | Por localizar | Sin evidencias verificadas | Por evaluar | Por asignar |
```

## 16. Entregables de gestión

- Product Backlog actualizado con prioridades, estados, dependencias y responsable.
- Sprint Backlog para la siguiente iteración **aprobada**.
- Matriz de trazabilidad y auditoría del progreso hasta Sprint 5.
- Modelo ER y migraciones revisadas, más diseño de identidades sociales.
- Contrato API de autenticación y demás cambios.
- Documento de CX/UX/UI y sistema de diseño.
- Casos de prueba unitarios, integración, seguridad, accesibilidad y E2E.
- Pipeline CI/CD, guía de despliegue, variables por ambiente y plan de rollback.
- ADR y registro de decisiones pendientes.
- Changelog documental y evidencia de aceptación.

## 17. Dónde guardar este documento

**Ruta recomendada:** `SDD/04_BACKLOG_TECNICO_IMPLEMENTACION_SAAS_INVERSIONES_RP.md` si el repositorio conserva los planes bajo `SDD/`; alternativamente `docs/07-scrum/` si se decide centralizar artefactos Scrum. Elegir **una sola ubicación canónica** y enlazar desde los README, evitando duplicados editables.

Mantener intacto el PDF original en `DOCS_origen/`. Este Markdown es una actualización propuesta y deberá versionarse cuando OpenCode contraste los datos reales del repositorio.

## 18. Registro de cambios

| Versión | Fecha | Descripción |
|---|---|---|
| 1.0 | Noviembre 2026 | Backlog técnico original, Sprints 0–9, fuente PDF |
| 1.1 propuesta | 2026-11-15 | Inventario trazable del backlog original; nuevas historias OAuth Google/Facebook y CX/UX/UI; protocolo de auditoría post-Sprint 5; decisiones y pruebas adicionales |

---

**Regla final:** la planificación documental no certifica que el software esté implementado. Solo pruebas, revisión del repositorio y aceptación formal permiten cerrar una historia.
