# Registro de decisiones v1.1 — CX, UX, UI y autenticación social

**Proyecto:** SaaS Inversiones R&P
**Documento:** ADR-0004 a ADR-0007 (propuestas del incremento v1.1)
**Versión:** 1.0
**Fecha:** 2026-11-15
**Estado:** Propuestas documentales; ninguna decisión está implementada
**Regla:** este registro complementa `ADR-0001` a `ADR-0003` y
`REGISTRO_DECISIONES_SPRINT_0.md`; no los reemplaza.

---

## 1. Contexto

El solicitante incorporó requisitos de autenticación con Google/Facebook y
mejoras de CX/UX/UI. El código vigente ya tiene autenticación local con Sanctum,
multiempresa y permisos por rol. Las nuevas capacidades se tratan como un
**incremento posterior al Sprint 5**, sin reabrir cierres históricos ni asumir
que ya existen.

Evidencia verificada: no hay Laravel Socialite, configuración de proveedores,
tabla de identidades sociales ni botones sociales en React. Las pruebas del
Sprint 5 son 131/488 en backend y 56 en frontend.

---

## ADR-0004 — Autenticación social sobre Sanctum SPA

**Estado:** Aprobada e implementada (proveedores reales pendientes de credenciales)
**Fecha:** 2026-11-15
**Relacionados:** `ADR-0001`, `AUTH-01`, `SDD-05`, `SDD-01`

**Contexto.** Se requiere añadir Google y Facebook al login local. La aplicación
ya usa Sanctum con cookie de SPA y CSRF; el frontend es React separado.

**Decisión propuesta.**

1. Mantener `POST /api/v1/auth/login` para correo/contraseña.
2. Iniciar OAuth en rutas `web` (`/auth/social/{provider}/redirect` y
   `/auth/social/{provider}/callback`) para disponer de sesión y `state`.
3. Tras validar al proveedor, resolver la identidad interna y crear la sesión
   Sanctum existente; redirigir a React sin tokens en la URL.
4. Exponer en `/api/v1` solo las operaciones autenticadas de consulta,
   vinculación y desvinculación.
5. No introducir JWT, tokens en `localStorage` ni un segundo sistema de sesión.

**Alternativas.** (a) Token Bearer para sociales; descartada por exposición y
duplicación. (b) Callback directamente en `/api/v1`; descartada porque el
proveedor no envía `Origin` y el estado de sesión/CSRF no es fiable.
(c) Servicio de identidad externo (broker); no aprobado para el MVP.

**Consecuencias.** Nuevas rutas web y configuración de callbacks por ambiente;
pruebas de regresión del login local; dependencia de Google/Meta. La autorización
de empresa, rol y permisos no cambia.

**Criterios de aceptación.** Login local intacto; Google/Facebook vinculados
crean sesión Sanctum; callback inválido o `state` alterado se rechaza; ningún
token queda en URL, logs o React.

**Evidencia pendiente.** Configuración de proveedores, controladores, servicio
de identidad, pruebas y revisión de seguridad.

---

## ADR-0005 — Identidad social vinculada explícitamente

**Estado:** Aprobada e implementada (proveedores reales pendientes de credenciales)
**Fecha:** 2026-11-15
**Relacionados:** `AUTH-01`, `SDD-04`, `03_CANVAS_DISENO_DE_DATOS...`

**Contexto.** Un usuario puede usar varios métodos de acceso sin duplicar su
cuenta, roles, historial ni pertenencia empresarial. La coincidencia de correo
no prueba titularidad.

**Decisión propuesta.**

- Tabla `social_identities`: `id`, `user_id`, `provider`,
  `provider_user_id`, `provider_email` (opcional), `linked_at`, timestamps.
- `UNIQUE (provider, provider_user_id)`; índice por `user_id`.
- `user_id` referencia a `users`; **sin `company_id`**, porque la identidad es
  global y la autorización empresarial sigue en `company_user`.
- No imponer `UNIQUE (user_id, provider)` sin decisión de producto.
- No guardar tokens de acceso/refresh ni perfiles completos.
- Vinculación solo desde sesión autenticada y con prueba de control del
  proveedor; prohibido autovincular por email.
- Desvinculación bloqueada si dejaría al usuario sin método de acceso.

**Alternativas.** Columnas `google_id`/`facebook_id` en `users`; descartadas por
rigidez. Usuarios separados por proveedor; descartados por duplicar identidad y
romper historial.

**Consecuencias.** Migración aditiva y reversible cuando se apruebe; pruebas de
colisión, concurrencia y aislamiento. No cambia el esquema de los Sprints 0–5.

**Evidencia pendiente.** Migración, modelo, servicio, pruebas y revisión de
privacidad.

---

## ADR-0006 — Sistema de diseño y accesibilidad incremental

**Estado:** Aprobada — base implementada, migración de pantallas en curso
**Fecha:** 2026-11-15
**Relacionados:** `UI-01`, `UX-01`, `CX-01`, `SDD-01`

**Contexto.** La interfaz actual usa clases CSS propias y componentes por
feature. Se solicita consistencia visual, accesibilidad y mejores estados de
interacción sin alterar reglas de negocio.

**Decisión propuesta.**

- Adoptar un conjunto mínimo de tokens y componentes reutilizables React
  (botón, campo, tabla, alerta, diálogo, paginación) de forma incremental.
- Objetivo **WCAG 2.2 AA**: teclado, foco visible, etiquetas, contraste,
  mensajes asociados y zoom.
- Todo formulario crítico define estados de carga, vacío, error, éxito y sin
  permisos; las operaciones de stock muestran confirmación y referencia.
- No mostrar costos/márgenes sin permiso.
- Validar contraste y regresión visual antes de reemplazar estilos globales.

**Alternativas.** Mantener estilos locales por pantalla; descartado por
divergencia. Adoptar una librería UI completa; no decidido y no necesario para
el MVP.

**Consecuencias.** Trabajo transversal de componentes y pruebas; no cambia API
ni base de datos. Los temas comerciales futuros deberán preservar contraste.

**Criterios de aceptación.** Login accesible con tres métodos; componentes
documentados; pruebas de teclado y anchos móvil/escritorio; sin regresión en
catálogo, inventario, compras ni traslados.

---

## ADR-0007 — Incorporación como incremento v1.1, sin reabrir Sprints 0–5

**Estado:** Aprobada — incremento ejecutado antes del Sprint 6
**Fecha:** 2026-11-15
**Relacionados:** `04_BACKLOG_TECNICO...`, `QA-01`, `Docs/05`

**Contexto.** Los cierres 0–5 están documentados y probados. Las historias
US-0111..0115 y US-UX01..07 son nuevas y no existían en esos sprints.

**Decisión propuesta.**

- Tratar auth social y CX/UX/UI como **incremento v1.1** con backlog propio.
- No renumerar sprints cerrados ni afirmar que las historias nuevas ya estaban
  en el Sprint 1.
- El Product Owner decide si se ejecuta antes del Sprint 6 (métricas/alertas) o
  en un sprint transversal claramente identificado.
- Mantener la regresión de Sprints 0–5 como condición de entrada y salida.

**Consecuencias.** Planificación y capacidad adicionales; trazabilidad separada;
los documentos históricos permanecen intactos.

**Evidencia pendiente.** Asignación de sprint, estimación, criterios de salida y
aprobación del PO.

---

## 2. Matriz de estado

| ADR | Tema | Estado | Implementado |
| --- | --- | --- | --- |
| ADR-0004 | Login social sobre Sanctum | Aprobada | Sí, con mocks |
| ADR-0005 | Identidad social | Aprobada | Sí |
| ADR-0006 | UI/accesibilidad | Aprobada | Parcial |
| ADR-0007 | Incremento v1.1 | Aprobada | Sí |

## 3. Decisiones que requieren aprobación

1. Invitación obligatoria frente a autorregistro social.
2. Sprint/incremento y esfuerzo.
3. Una o varias identidades del mismo proveedor por usuario.
4. Política de vinculación, desvinculación y recuperación.
5. Retención de correo/metadatos del proveedor.
6. Sistema de diseño definitivo y validación de accesibilidad.
7. Metas CX/UX y método de medición.

## 4. Proceso de actualización

Toda decisión aprobada deberá registrar fecha, responsable, evidencia de código
y pruebas, y un ADR que reemplace al anterior si cambia el sentido. No se borra
historia.
