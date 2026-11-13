# Plan de preparación — Sprint 1 «Identidad, acceso y multiempresa»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-01 — Identidad, acceso y multiempresa
**Sprint:** 1
**Versión del documento:** 1.0
**Fecha:** 2026-11-13
**Estado:** Planificación — **no habilita desarrollo funcional** hasta cerrar las decisiones de entrada
**Canvas de referencia:** [`DOCS_origen/CANVAS_SPRINT_1_IDENTIDAD_MULTIEMPRESA.md`](../DOCS_origen/CANVAS_SPRINT_1_IDENTIDAD_MULTIEMPRESA.md)
**Backlog:** [`DOCS_origen/BACKLOG_TECNICO_IMPLEMENTACION_MVP_INVERSIONES_RP (1).md`](<../DOCS_origen/BACKLOG_TECNICO_IMPLEMENTACION_MVP_INVERSIONES_RP (1).md>) §EP-01

---

## 1. Propósito y regla de entrada

Este documento **prepara** el Sprint 1: refina el backlog, propone el diseño
técnico y los contratos, y deja explícito qué debe aprobarse **antes** de escribir
código. No implementa funcionalidad.

> **Regla heredada del Sprint 0:** «No se habilita desarrollo funcional dependiente
> de decisiones aún abiertas» (canvas Sprint 0 §3, US-0002). Por lo tanto, las
> historias de EP-01 no se implementan hasta que **DD-01** y **DD-04** estén
> aprobadas y MySQL esté disponible.

## 2. Resumen del sprint

| Historia | Título | Prioridad | Decisión que la condiciona |
| --- | --- | --- | --- |
| US-0101 | Iniciar sesión y cerrar sesión | P0 | DD-01 · ADR-0001 (autenticación SPA) |
| US-0102 | Administrar usuarios y asignar roles | P0 | DD-01 · DD-04 · ADR-0003 |
| US-0103 | Aplicar permisos por módulo y acción | P0 | DD-04 · ADR-0003 |
| US-0104 | Aislar datos por empresa | P0 | Estrategia tenant (SDD-04) · ADR-0002 |

**Resultado esperado:** usuarios autenticados que acceden únicamente a funciones y
datos autorizados dentro de su ámbito empresarial.

## 3. Checklist de entrada (Definition of Ready del sprint)

Una historia entra al Sprint 1 solo cuando se cumplen estas condiciones:

| # | Condición de entrada | Estado | Responsable | ¿Bloqueante? |
| --- | --- | --- | --- | --- |
| E-01 | Aprobar **DD-01** (correo único global) | Pendiente | Product Owner | **Sí** (US-0101, US-0102) |
| E-02 | Aprobar **DD-04** (roles base globales + por empresa) | Pendiente | Product Owner | **Sí** (US-0102, US-0103) |
| E-03 | Ratificar la estrategia multiempresa de `SDD-04` | Propuesta lista | Product Owner / Líder técnico | **Sí** (US-0104) |
| E-04 | Resolver **ADR-0001** (autenticación SPA: cookie Sanctum vs token) | Borrador listo | Líder técnico | **Sí** (US-0101) |
| E-05 | Resolver **ADR-0002** (resolución de empresa activa) | Borrador listo | Líder técnico | **Sí** (US-0104) |
| E-06 | Resolver **ADR-0003** (modelo de permisos y `usuario_permiso`) | Borrador listo | Líder técnico | **Sí** (US-0103) |
| E-07 | MySQL disponible (local o Docker) para migraciones reales | Bloqueado | Desarrollo | **Sí** |
| E-08 | Contratos base de API (`SDD-05`) vigentes | Completo | Líder técnico | No |
| E-09 | DoD del sprint acordada (§12) | Propuesta lista | Equipo | No |
| E-10 | Datos de prueba: dos empresas y usuarios de prueba | Pendiente | Desarrollo | No |

> Los ADR-0001, ADR-0002 y ADR-0003 están en `DOCUMENTACION/SDD/ADR/` como
> **propuestas**; se cierran al aprobarse.

## 4. Alcance y exclusiones

| Bloque | Incluido | Fuera de alcance |
| --- | --- | --- |
| Autenticación | Login, logout, protección de rutas, gestión segura de sesión/token | SSO, MFA, recuperación avanzada de cuentas |
| Usuarios | Registrar, editar, activar, desactivar y asignar roles | Gestión de RR. HH. |
| Autorización | Permisos por módulo y acción; validación en backend y control en interfaz | Permisos de módulos aún no implementados |
| Multiempresa | Resolver empresa activa y aislar lecturas/escrituras | Facturación y planes SaaS |
| Calidad | Pruebas de autenticación, permisos y aislamiento | Pruebas finales de todo el sistema |

## 5. Backlog refinado

### US-0101 — Iniciar y cerrar sesión

**Criterios de aceptación**

- El usuario puede iniciar y cerrar sesión.
- Las credenciales inválidas no permiten el acceso.
- Las rutas protegidas rechazan solicitudes no autenticadas.
- La sesión/token se gestiona de forma segura para la SPA.

**Tareas técnicas**

1. Migraciones y modelos de `companies`, `users`, `company_user`.
2. Autenticación con Laravel Sanctum (decisión de ADR-0001).
3. Endpoints `POST /api/v1/auth/login`, `POST /api/v1/auth/logout`, `GET /api/v1/auth/me`.
4. Pantalla React de acceso y guardia de rutas.
5. Pruebas de login válido/ inválido, ruta protegida y expiración de token.

**Dependencias:** DD-01, ADR-0001, MySQL (E-07).

### US-0102 — Administrar usuarios y asignar roles

**Criterios de aceptación**

- Se puede registrar, editar, activar y desactivar usuarios según permisos.
- Cada usuario pertenece al ámbito empresarial definido.
- No se permite asignar roles fuera del ámbito autorizado.

**Tareas técnicas**

1. Migraciones de `roles`, `permissions`, `permission_role`, `company_user`.
2. Modelos, relaciones, validaciones y políticas.
3. CRUD de usuarios y asignación de roles con `company_id` del contexto.
4. Pantallas React de gestión de usuarios.
5. Pruebas de acceso entre empresas y asignaciones no autorizadas.

**Dependencias:** DD-01, DD-04, ADR-0003.

### US-0103 — Aplicar permisos por módulo y acción

**Criterios de aceptación**

- El backend verifica permisos en cada endpoint protegido.
- La interfaz oculta o deshabilita acciones no autorizadas sin sustituir la validación del servidor.
- Los trabajadores sin permiso no pueden consultar costos/márgenes restringidos.

**Tareas técnicas**

1. Permisos y relaciones `permission_role` (y `user_permission` solo si se aprueba).
2. Middleware, policies y gates.
3. Protección de endpoints y de componentes de navegación.
4. Pruebas positivas y negativas por rol.

**Dependencias:** DD-04, ADR-0003.

### US-0104 — Aislar datos por empresa

**Criterios de aceptación**

- Toda consulta con ámbito de tenant aplica el contexto de empresa.
- Las operaciones de lectura y escritura no permiten cambiar `company_id` manipulando la petición.
- Las pruebas demuestran que un tenant no accede a otro.

**Tareas técnicas**

1. `TenantContext` (resolución de la empresa activa) según ADR-0002.
2. `TenantScope` global en modelos de dominio y políticas de acceso.
3. Validación de `company_id` en relaciones y operaciones compuestas.
4. Pruebas automatizadas de aislamiento (matriz §10).

**Dependencias:** estrategia tenant (SDD-04), ADR-0002.

## 6. Diseño técnico propuesto

> Propuestas sujetas a los ADR. Los nombres de clases y rutas son orientativos.

### 6.1 Autenticación (US-0101)

- **Laravel Sanctum**, ya instalado en el Sprint 0 (`HasApiTokens`).
- Dos modos posibles, a decidir en **ADR-0001**:
  - **A — Cookie de SPA (recomendado):** `statefulApi()`, cookies `HttpOnly`, CSRF con `XSRF-TOKEN`. Adecuado si frontend y backend comparten dominio.
  - **B — Token Bearer:** ideal si el frontend se despliega en otro dominio o como app móvil.
- Rutas protegidas con middleware `auth:sanctum`.

### 6.2 Resolución de la empresa activa (US-0104)

- La empresa activa **nunca** se toma del cuerpo de la petición.
- Alternativas a decidir en **ADR-0002**: cabecera validada `X-Company-Id`, empresa activa en la sesión, o subdominio.
- El backend valida pertenencia (`company_user`) y estado de la empresa antes de operar.

### 6.3 Aislamiento (`TenantScope`)

- Trait `BelongsToCompany` con:
  - Evento `creating`: asigna `company_id` desde el `TenantContext`.
  - Global scope: filtra `WHERE company_id = ?`.
  - Guardia: impide cambiar `company_id` en `updating`.
- Aplica a todos los modelos de dominio (Sprint 2 en adelante).

### 6.4 Roles y permisos (US-0102, US-0103)

- Roles base globales: `owner`, `admin`, `operator`, `viewer` (`roles.company_id = NULL`).
- Roles personalizados por empresa (`roles.company_id` no nulo).
- Permisos con convención `modulo.accion` (`inventory.approve`, `identity.users.manage`).
- Verificación en backend con `Gate`/`Policy`; la interfaz solo oculta/deshabilita.

### 6.5 Estructura de módulos (backend)

```text
backend/app/Modules/
├── Identity/
│   ├── Models/{Company,User,Role,Permission}.php
│   ├── Http/Controllers/{AuthController,UserController,RoleController}.php
│   ├── Http/Requests/...
│   ├── Policies/...
│   └── Services/{AuthService,TenantContext}.php
└── Shared/                # Trait BelongsToCompany, TenantScope
```

## 7. Contratos API propuestos

Bajo `/api/v1` (adoptado con US-0101), conforme a `SDD-05`.

| Método | Ruta | Descripción | Permiso |
| --- | --- | --- | --- |
| `POST` | `/api/v1/auth/login` | Iniciar sesión | Público |
| `POST` | `/api/v1/auth/logout` | Cerrar sesión | Autenticado |
| `GET` | `/api/v1/auth/me` | Perfil y permisos del usuario | Autenticado |
| `GET` | `/api/v1/companies` | Empresas del usuario | Autenticado |
| `GET` | `/api/v1/users` | Listar usuarios de la empresa activa | `identity.users.view` |
| `POST` | `/api/v1/users` | Crear usuario | `identity.users.manage` |
| `GET` | `/api/v1/users/{id}` | Ver usuario | `identity.users.view` |
| `PUT` | `/api/v1/users/{id}` | Editar usuario | `identity.users.manage` |
| `PATCH` | `/api/v1/users/{id}/status` | Activar/desactivar | `identity.users.manage` |
| `GET` | `/api/v1/roles` | Listar roles disponibles | `identity.roles.view` |
| `PUT` | `/api/v1/users/{id}/role` | Asignar rol | `identity.users.manage` |
| `GET` | `/api/v1/permissions` | Catálogo de permisos | `identity.roles.view` |

**Ejemplo — login**

```http
POST /api/v1/auth/login
Content-Type: application/json

{ "email": "admin@empresa-a.test", "password": "••••••••" }
```

```json
{
  "data": {
    "user": { "id": 1, "name": "Administrador A", "email": "admin@empresa-a.test" },
    "companies": [ { "id": 12, "name": "Empresa A" } ],
    "permissions": ["identity.users.manage", "inventory.view"]
  }
}
```

**Errores** conforme a `SDD-05` §5: `401 INVALID_CREDENTIALS`, `403 FORBIDDEN_TENANT`,
`422 VALIDATION_ERROR`.

## 8. Matriz de permisos propuesta

| Permiso | owner | admin | operator | viewer |
| --- | :---: | :---: | :---: | :---: |
| `identity.users.view` | ✔ | ✔ | — | — |
| `identity.users.manage` | ✔ | ✔ | — | — |
| `identity.roles.view` | ✔ | ✔ | — | — |
| `identity.roles.manage` | ✔ | — | — | — |
| `inventory.view` | ✔ | ✔ | ✔ | ✔ |
| `inventory.move` | ✔ | ✔ | ✔ | — |
| `inventory.adjust` | ✔ | ✔ | — | — |
| `inventory.approve` | ✔ | ✔ | — | — |
| `metrics.costs.view` | ✔ | ✔ | — | — |
| `audit.view` | ✔ | ✔ | — | — |

> Sujeta a aprobación (DD-04 y ADR-0003). El `owner` es el único que puede
> administrar roles.

## 9. Migraciones previstas (aún no creadas)

Orden conforme a `SDD-04` §6:

1. `companies`
2. `users` y `company_user`
3. `roles`, `permissions`, `permission_role` (y `user_permission` si se aprueba)

Cada migración se crea **solo tras** aprobar DD-01/DD-04 y contar con MySQL (E-07).

## 10. Matriz de pruebas (T-01..T-10)

| ID | Escenario | Resultado esperado |
| --- | --- | --- |
| T-01 | Inicio de sesión con credenciales válidas | Acceso autorizado |
| T-02 | Credenciales inválidas | Acceso rechazado (401) |
| T-03 | Endpoint protegido sin autenticación | Solicitud rechazada (401) |
| T-04 | Cierre de sesión y reutilización de credenciales invalidadas | Acceso rechazado |
| T-05 | Usuario sin permiso intenta crear otro usuario | Rechazado por backend (403) |
| T-06 | Usuario sin permiso consulta costos/márgenes | Datos restringidos no disponibles |
| T-07 | Admin de empresa A asigna rol fuera de su ámbito | Rechazado |
| T-08 | Usuario de empresa A consulta registro de empresa B | No visible (404/403) |
| T-09 | Petición modifica `company_id` manualmente | No permite cruzar ámbito |
| T-10 | React oculta una acción pero se llama su API directamente | Laravel deniega igualmente |

## 11. Riesgos y controles

| Riesgo | Control |
| --- | --- |
| Exposición de información entre empresas | `TenantScope` + pruebas negativas en CI (T-08, T-09) |
| Permisos aplicados solo en React | Verificación obligatoria en cada endpoint (T-10) |
| Asignaciones de roles fuera de ámbito | Validación de `company_id` en backend (T-07) |
| Gestión insegura de sesión SPA | ADR-0001 define cookies/token explícitamente |
| Ambigüedad de correos y roles | Cerrar DD-01 y DD-04 antes del diseño |

## 12. Definition of Done del sprint

- [ ] US-0101 a US-0104 cumplen sus criterios de aceptación.
- [ ] Migraciones, relaciones, índices y restricciones implementados y revisados.
- [ ] Las reglas residen en Laravel; React no duplica autorización.
- [ ] Validaciones y autorizaciones de backend activas.
- [ ] React consume la API con estados de carga, error y vacío.
- [ ] Pruebas de autenticación, permisos y aislamiento pasan en CI.
- [ ] Sin vulnerabilidades críticas conocidas ni defectos bloqueantes.
- [ ] Documentación técnica y de API actualizada.
- [ ] Código revisado y el Product Owner acepta el incremento.

## 13. Demo y criterios de salida

1. Acceder correctamente y cerrar sesión.
2. Demostrar rechazo de credenciales inválidas y de rutas sin autenticación.
3. Crear, editar y activar/desactivar un usuario desde un administrador autorizado.
4. Asignar un rol válido y rechazar una asignación no autorizada.
5. Demostrar una acción bloqueada por permisos en frontend y backend.
6. Con dos empresas de prueba, verificar que ninguna consulta cruza sus límites.
7. Mostrar pruebas automatizadas y evidencia de CI.

## 14. Decisiones requeridas y documentos relacionados

| Documento | Tema |
| --- | --- |
| [`ADR/ADR-0001_ESTRATEGIA_AUTENTICACION_SPA.md`](./ADR/ADR-0001_ESTRATEGIA_AUTENTICACION_SPA.md) | Cookie de SPA vs token Bearer |
| [`ADR/ADR-0002_ESTRATEGIA_RESOLUCION_TENANT.md`](./ADR/ADR-0002_ESTRATEGIA_RESOLUCION_TENANT.md) | Cómo se resuelve la empresa activa |
| [`ADR/ADR-0003_MODELO_PERMISOS.md`](./ADR/ADR-0003_MODELO_PERMISOS.md) | Roles, permisos y `user_permission` |
| [`ADR/REGISTRO_DECISIONES_SPRINT_0.md`](./ADR/REGISTRO_DECISIONES_SPRINT_0.md) | DP/DD pendientes de aprobación |
| [`SDD-04_DISENO_BASE_DATOS.md`](./SDD-04_DISENO_BASE_DATOS.md) | Estrategia multiempresa y migraciones |
| [`SDD-05_DISENO_API.md`](./SDD-05_DISENO_API.md) | Contratos y convenciones de API |
| [`../README_SPRINT_0.md`](../README_SPRINT_0.md) | Cierre del Sprint 0 y guía de iteraciones |

## 15. Estado de implementación

### US-0101 — Iniciar y cerrar sesión

**Completado el 2026-11-13.**

- Migraciones `companies`, `company_user` y ampliación de `users` (`status`, `deleted_at`).
- Modelos `App\Modules\Identity\Models\{Company,User}` con pertenencia multiempresa.
- Endpoints `POST /api/v1/auth/login`, `POST /api/v1/auth/logout`, `GET /api/v1/auth/me`.
- Autenticación SPA con Sanctum (cookie + CSRF) y logout que invalida la sesión.
- Pantalla React de acceso, contexto de autenticación y guardia de rutas.
- Pruebas T-01 a T-04 en `backend/tests/Feature/Auth/AuthenticationTest.php`.

### US-0102 — Administrar usuarios y asignar roles

**Completado el 2026-11-13.**

- Migraciones `permissions`, `roles`, `permission_role` y `company_user.role_id`.
- Modelos `Role`, `Permission` y `CompanyUser`; roles base globales (`owner`,
  `admin`, `operator`, `viewer`) y permisos `modulo.accion` (ADR-0003).
- `TenantContext` y middleware `tenant`: empresa activa validada por
  `X-Company-Id`; middleware `permission` para autorizar por permiso.
- Endpoints `/api/v1/users` (listar, crear, ver, editar, cambiar estado y
  asignar rol), `/api/v1/roles` y `/api/v1/permissions`.
- Reglas: pertenencia activa, recurso de otra empresa → `404 NOT_FOUND`, rol de
  otra empresa → `422`, escalada de privilegios → `403 FORBIDDEN`.
- Pantalla React de usuarios con filtros, paginación, alta, edición, cambio de
  estado, asignación de rol y selector de empresa activa.
- Pruebas T-05, T-07, T-08, T-09 y T-10 (ámbito de identidad) en
  `backend/tests/Feature/Identity/UserManagementTest.php`.
- Evidencia: backend 27 pruebas / 96 aserciones OK, Pint OK, frontend 15
  pruebas OK, lint OK, build OK, Cypress 1 prueba OK.

### US-0103 — Aplicar permisos por módulo y acción

**Completado el 2026-11-13.**

- `Gate::before` resuelve cualquier código `modulo.accion` contra los permisos
  del rol activo en la empresa de la petición.
- Policies `UserPolicy`, `RolePolicy` y `PermissionPolicy` para autorizar
  recursos y pertenencia al tenant.
- Rutas protegidas con el middleware `can:` y controladores con
  `$this->authorize(...)` como segunda barrera.
- Errores de autorización normalizados a `403 FORBIDDEN`; recurso de otra
  empresa → `404 NOT_FOUND`.
- Componente React `Can` para ocultar navegación y acciones sin permiso, sin
  sustituir la validación del servidor.
- Pruebas positivas y negativas por rol, incluida la verificación del permiso
  `metrics.costs.view` (T-06 a nivel de Gate).

### Pendiente

US-0104 (aislamiento global con `TenantScope` para modelos de dominio). El
endpoint de métricas/costos se implementará en el sprint de métricas; el permiso
`metrics.costs.view` ya se aplica en el Gate. La validación contra MySQL real
requiere Docker o MySQL instalado (E-07).
