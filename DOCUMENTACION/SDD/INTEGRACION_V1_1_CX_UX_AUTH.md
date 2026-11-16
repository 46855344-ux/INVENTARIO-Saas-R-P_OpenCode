# Integración documental v1.1 — CX, UX, UI y autenticación social

**Proyecto:** SaaS Inversiones R&P
**Documento:** Comparación de fuentes, conflictos y plan de actualización incremental
**Versión:** 1.0
**Fecha:** 2026-11-15
**Estado:** Plan de integración documental aplicado; **ninguna funcionalidad nueva implementada**
**Rama de trabajo:** `docs/integracion-v1-1-cx-auth`
**Respaldo:** etiqueta `respaldo-pre-integracion-v1.1` sobre el estado previo de `main`

---

## 1. Propósito

Incorporar a la documentación canónica los requisitos nuevos de **CX, UX, UI y
autenticación con Google/Facebook** sin borrar ni reemplazar los SDD, ADR y
cierres de Sprint 0 a 5. Las fuentes nuevas se conservan como **documentos de
entrada**; los documentos existentes se amplían de forma aditiva.

Esta fase es **solo documental**: no se modificó código, no se ejecutaron
migraciones y no se alteró la base de datos.

## 2. Fuentes analizadas

### 2.1 Documentos canónicos existentes

- `SDD/SDD-01_ARQUITECTURA_GENERAL.md`
- `SDD/SDD-04_DISENO_BASE_DATOS.md`
- `SDD/SDD-05_DISENO_API.md`
- `SDD/ADR/ADR-0001_ESTRATEGIA_AUTENTICACION_SPA.md`
- `SDD/ADR/ADR-0002_ESTRATEGIA_RESOLUCION_TENANT.md`
- `SDD/ADR/ADR-0003_MODELO_PERMISOS.md`
- `DOCUMENTACION/README.md`, `CHANGELOG.md`
- `TESTING/PLAN_PRUEBAS_SPRINT_0.md`
- `DEVSECOPS/CI_CD.md`
- Cierres `README_SPRINT_0.md` a `README_SPRINT_5.md`

### 2.2 Documentos de entrada nuevos

| Documento | Propósito | Tratamiento |
| --- | --- | --- |
| `Docs/01_DOCUMENTO_MAESTRO_ANALISIS_REQUISITOS...` | Requisitos maestros v1.1 | Referencia; no reemplaza requisitos canónicos |
| `Docs/05_PROJECT_CANVAS_RESUMEN_EJECUTIVO...` | Canvas ejecutivo v1.1 | Referencia; no reemplaza cierres ni planes |
| `SDD/03_CANVAS_DISENO_DE_DATOS...` | Canvas de datos v1.1 | Referencia; solo se integra `social_identities` como propuesta |
| `SDD/04_BACKLOG_TECNICO_IMPLEMENTACION...` | Backlog v1.1 | Referencia; nuevas historias quedan como propuesta |
| `SDD/ADR/ADR-01_DECISIONES_ARQUITECTONICAS.md` | Registro ADR propuesto | Referencia; se integra en un registro canónico nuevo |
| `SDD/SDD-05_DISENO_API_REST...` | API REST propuesta | Referencia; **no sustituye** a `SDD-05_DISENO_API.md` |
| `SEGURIDAD/AUTH-01_AUTENTICACION_SOCIAL.md` | Diseño de auth social | Documento de seguridad propuesto; se enlaza desde los SDD |
| `TESTING/QA-01_PLAN_DE_PRUEBAS_CX_UX_UI_AUTH.md` | Plan QA propuesto | Plan de pruebas del incremento; no ejecutado |
| `CX_UX_UI/CX-01`, `UX-01`, `UI-01` | Experiencia y sistema de diseño | Requisitos propuestos; se enlazan desde el índice y ADR |

### 2.3 Evidencia del código real (verificada el 2026-11-15)

- Backend Laravel con módulos `Identity`, `Catalog`, `Inventory`, `Purchasing`,
  `Metrics`, `Audit` y `Shared`.
- Autenticación vigente: **Sanctum 4.4 con cookie de SPA y CSRF** (ADR-0001);
  no hay `Laravel\Socialite`, configuración Google/Facebook ni tabla de
  identidades sociales.
- Rutas vigentes bajo `/api/v1`; login local en `POST /api/v1/auth/login`,
  perfil en `GET /api/v1/auth/me`.
- Usuario global (`users.email` único) y pertenencia por empresa en
  `company_user`; roles y permisos globales/por empresa (ADR-0002/0003).
- Estado de pruebas al cierre del Sprint 5: **131 pruebas / 488 aserciones** en
  backend y **56 pruebas** en frontend.
- `frontend/src/features/auth/LoginPage.tsx` solo ofrece correo y contraseña.

## 3. Comparación por documento

| Fuente nueva | Coincide con lo existente | Aporte nuevo compatible | Diferencia o conflicto | Decisión |
| --- | --- | --- | --- | --- |
| `AUTH-01` | Correo/contraseña, autorización interna, aislamiento | Google OIDC, Facebook, vinculación explícita, estados UX | Rutas sin `/v1`, sesión “por definir”, tabla `social_identities` | Se integra como **propuesta**; se fija `/api/v1` y Sanctum |
| `ADR-01` | Monolito modular, multiempresa, ADR-0001..0003 | ADR de auth social, identidad externa, UI | Duplica el registro ADR y propone IDs nuevos | Se crea registro canónico v1.1; el archivo nuevo queda como referencia |
| `SDD-05 REST` | `/api/v1`, JSON, autorización, inventario | Endpoints sociales, estados de error, checklist | Envoltura `success`, rutas `/api/auth`, `DELETE /products` | No se adopta como canónico; solo se integran extensiones compatibles |
| `03 Canvas datos` | Multiempresa, integridad, historial | `social_identities`, rules RN-AUTH | Nombres en español y `empresa_id`; ejemplo `user_social_identities` | Se integra `social_identities` con nombres ingleses y sin `company_id` |
| `04 Backlog` | US-0101..0503, prioridades | US-0111..0115, US-UX01..07 | Abre un “cambio nuevo” sin sprint asignado | Se registra como incremento v1.1 pendiente de planificación |
| `CX-01/UX-01/UI-01` | Estados de carga/error/vacío, permisos, confirmaciones | WCAG 2.2 AA, sistema de diseño, flujos de login | No existe un documento CX/UX/UI canónico previo | Se conservan como referencia y se enlazan; no se implementan |
| `Docs/01` y `Docs/05` | Visión, alcance, sprints 0–5 | Requisitos de experiencia y auth | Declaran avance “hasta Sprint 5” sin auditoría | Se usa el estado verificado del repositorio, no se reemplazan cierres |
| `QA-01` | Estrategia PHPUnit/Vitest, regresión | Matriz AUTH/UX, criterios de salida | No ejecutado; asume escenarios futuros | Se conserva como plan propuesto y se enlaza |

## 4. Conflictos detectados y resolución

| ID | Conflicto | Resolución propuesta | Justificación | Documentos afectados |
| --- | --- | --- | --- | --- |
| C-01 | Rutas sociales sin versión (`/api/auth/...`) frente a `/api/v1/...` | Usar `/api/v1/auth/social/...` para API y rutas `web` para redirect/callback | Mantiene versionado y permite sesión/CSRF en el callback | SDD-05, ADR-0004 |
| C-02 | Callback OAuth en rutas API sin sesión stateful | Redirect/callback en `routes/web.php`; retorno controlado a React; API solo para consultar/vincular | Sanctum SPA necesita sesión; el callback del proveedor no envía `Origin` | ADR-0004, AUTH-01 |
| C-03 | “Sesión por definir” frente a Sanctum SPA ya implementado | Reutilizar Sanctum cookie + CSRF; no introducir JWT ni tokens en `localStorage` | Evita duplicar autenticación y mantiene ADR-0001 | ADR-0001, ADR-0004, SDD-05 |
| C-04 | `social_identities` vs `user_social_identities` vs `identidad_social` | Nombre canónico propuesto: `social_identities` | Convención inglesa del ESTANDAR y AUTH-01 | SDD-04, ADR-0005 |
| C-05 | `empresa_id`/`usuario_id` frente a `company_id`/`user_id` | Mantener `company_id`, `user_id`, tablas en inglés | Esquema y código reales; el glosario ya fija esa convención | SDD-04, ADR-0005 |
| C-06 | Envoltura `success: true` y rutas `DELETE /products` | Conservar contrato `data`/`meta` y desactivación por `PATCH .../status` | No romper frontend ni pruebas del Sprint 2–5 | SDD-05 |
| C-07 | Códigos de permiso `products.read` frente a `catalog.view` | Mantener los códigos implementados (`catalog.*`, `inventory.*`, etc.) | Matriz vigente y pruebas de autorización | SDD-05, ADR-0006 |
| C-08 | Auth social como “Sprint 1” o “cambio nuevo” | Incremento v1.1 sin reabrir Sprint 1; sprint a definir por el PO | Los cierres 0–5 son históricos y no deben reescribirse | Backlog v1.1, ADR-0007, README |
| C-09 | `UNIQUE(user_id, provider)` opcional | No imponerla; solo `UNIQUE(provider, provider_user_id)` | Permite varias identidades del mismo proveedor si el negocio lo aprueba | SDD-04, ADR-0005 |
| C-10 | Vincular por correo | Prohibir autovinculación por email; exigir sesión y prueba de control | Previene suplantación y cuentas duplicadas | AUTH-01, SDD-04, ADR-0005 |
| C-11 | Google no estaba en el backlog original | Aceptar Google como ampliación aprobada por el solicitante | AUTH-01 lo declara ampliación; no cambia la autorización interna | ADR-0004, README |
| C-12 | El nuevo SDD-05 duplica el nombre y propósito del canónico | Mantener el canónico `SDD-05_DISENO_API.md`; el nuevo queda como entrada/referencia | Evita dos contratos vigentes y migraciones de consumidores | Docs/README, README |

## 5. Requisitos adoptados en la documentación

### 5.1 Autenticación social

- Mantener correo/contraseña y añadir Google y Facebook como métodos
  **propuestos**, sin implementar todavía.
- La identidad externa autentica, pero **no otorga empresa, rol ni permisos**.
- Identidad externa única por `(provider, provider_user_id)`.
- Vinculación solo con sesión válida, confirmación y prueba de control del
  proveedor; nunca por coincidencia de correo.
- Desvinculación que no deje al usuario sin método de acceso.
- `state`, PKCE cuando corresponda, validación de callback, HTTPS, cookies
  seguras, rate limiting y secretos solo en servidor.
- No almacenar tokens del proveedor ni datos de perfil innecesarios.

### 5.2 CX / UX / UI

- Objetivo de accesibilidad **WCAG 2.2 AA**.
- Login con tres métodos visibles, etiquetados y navegables por teclado.
- Estados explícitos de carga, vacío, error, éxito y sin permisos.
- Confirmación y resumen antes de operaciones críticas; sin duplicar envíos.
- No mostrar costos/márgenes a roles sin permiso.
- Componentes reutilizables, mensajes junto al campo y diseño responsive.
- Métricas CX/UX propuestas, sin metas numéricas aprobadas.

### 5.3 Fuera de esta integración

- Implementación de OAuth, migraciones, endpoints o pantallas nuevas.
- Autoregistro, planes, pagos, soporte y analítica de clientes.
- Cambios en reglas de stock, permisos o aislamiento multiempresa.

## 6. Archivos afectados por la integración

| Archivo | Tipo de cambio | Contenido |
| --- | --- | --- |
| `SDD/INTEGRACION_V1_1_CX_UX_AUTH.md` | Nuevo | Este registro de comparación, conflictos y plan |
| `SDD/ADR/REGISTRO_DECISIONES_V1_1_CX_UX_AUTH.md` | Nuevo | ADR-0004 a ADR-0007 propuestos |
| `SDD/SDD-01_ARQUITECTURA_GENERAL.md` | Aditivo | Nota de auth vigente y sección v1.1 de auth/UX |
| `SDD/SDD-04_DISENO_BASE_DATOS.md` | Aditivo | Propuesta `social_identities`, sin migración |
| `SDD/SDD-05_DISENO_API.md` | Aditivo | Endpoints sociales propuestos y corrección de auth |
| `SDD/ADR/ADR-0001_ESTRATEGIA_AUTENTICACION_SPA.md` | Aditivo | Extensión de la decisión a login social |
| `SDD/ADR/ADR-0002_ESTRATEGIA_RESOLUCION_TENANT.md` | Aditivo | Nota: el login social no altera el tenant |
| `SDD/ADR/ADR-0003_MODELO_PERMISOS.md` | Aditivo | Nota: el login social no altera roles/permisos |
| `DOCUMENTACION/README.md` | Aditivo | Índice de documentos de entrada y decisiones v1.1 |
| `DEVSECOPS/CI_CD.md` | Aditivo | Controles propuestos para secretos y pruebas OAuth |
| `CHANGELOG.md` | Aditivo | Registro de la integración documental |
| `Docs/README.md` | Nuevo | Aclaración: documentos de entrada, no canónicos |
| `CX_UX_UI/README.md` | Nuevo | Índice y estado de los requisitos CX/UX/UI |

No se elimina ni se renombra ningún documento existente. Los documentos nuevos
aportados por el solicitante se conservan en su ubicación original.

## 7. Plan aplicado

1. [x] Crear rama `docs/integracion-v1-1-cx-auth` y etiqueta de respaldo.
2. [x] Inventariar y leer los documentos nuevos y los canónicos.
3. [x] Contrastar con rutas, modelos, migraciones y pruebas reales.
4. [x] Registrar conflictos y resoluciones en este documento.
5. [x] Crear el registro ADR v1.1.
6. [x] Ampliar SDD-01, SDD-04 y SDD-05 de forma aditiva.
7. [x] Ampliar ADR-0001 a ADR-0003 con notas de compatibilidad.
8. [x] Actualizar el índice documental y el CHANGELOG.
9. [x] Verificar que no se modificó código ni se ejecutaron migraciones.

## 8. Decisiones pendientes del Product Owner

| ID | Decisión | Impacto |
| --- | --- | --- |
| PD-01 | ¿Invitación obligatoria o autorregistro social? | Alta: flujo de primer acceso |
| PD-02 | ¿En qué incremento/sprint se implementa? | Planificación y capacidad |
| PD-03 | ¿Se permite más de una identidad del mismo proveedor? | Restricción de unicidad |
| PD-04 | Política de vinculación/desvinculación y recuperación | Seguridad y soporte |
| PD-05 | Retención de correo y metadatos del proveedor | Privacidad |
| PD-06 | Sistema de diseño definitivo y validación WCAG | UI y esfuerzo |
| PD-07 | Metas CX/UX y método de medición | Aceptación y analítica |

## 9. Trazabilidad

- Requisitos: `Docs/01` §4.1, §7.2, §8.2, §11, §12.1; `Docs/05` §5.2, §9.2,
  §13.2, §15.2, §17.3.
- Seguridad: `SEGURIDAD/AUTH-01`.
- UX/UI: `CX_UX_UI/UX-01`, `CX_UX_UI/UI-01`, `CX_UX_UI/CX-01`.
- Pruebas: `TESTING/QA-01`.
- Backlog: `SDD/04_BACKLOG_TECNICO_IMPLEMENTACION...` §5–§7.
- Decisiones: `SDD/ADR/REGISTRO_DECISIONES_V1_1_CX_UX_AUTH.md`.
- Código vigente: `backend/routes/api.php`, `backend/bootstrap/app.php`,
  `backend/config/sanctum.php`, `backend/app/Modules/Identity`,
  `frontend/src/features/auth/LoginPage.tsx`.
