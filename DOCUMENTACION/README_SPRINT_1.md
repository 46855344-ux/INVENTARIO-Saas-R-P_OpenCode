# README — Sprint 1 «Identidad y multiempresa»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-01 — Identidad y multiempresa
**Iteración:** Sprint 1
**Versión del documento:** 1.0
**Fecha:** 2026-11-13
**Estado:** Cerrado técnicamente — validación con MySQL/Docker pendiente
**Canvas de referencia:** [`DOCS_origen/CANVAS_SPRINT_1_IDENTIDAD_MULTIEMPRESA.md`](./DOCS_origen/CANVAS_SPRINT_1_IDENTIDAD_MULTIEMPRESA.md)
**Plan del sprint:** [`SDD/PLAN_SPRINT_1_IDENTIDAD_MULTIEMPRESA.md`](./SDD/PLAN_SPRINT_1_IDENTIDAD_MULTIEMPRESA.md)

---

## 1. Resumen ejecutivo

El Sprint 1 convirtió la base técnica del Sprint 0 en una **aplicación con
identidad, autorización y aislamiento por empresa**. Se completaron las cuatro
historias de la épica EP-01:

| Historia | Objetivo | Resultado |
| --- | --- | --- |
| US-0101 | Iniciar y cerrar sesión | **Completado** |
| US-0102 | Administrar usuarios y asignar roles | **Completado** |
| US-0103 | Aplicar permisos por módulo y acción | **Completado** |
| US-0104 | Aislar datos por empresa | **Completado** |

## 2. Contexto de entrada

- Sprint 0 con Laravel 13, React 19, Sanctum y la estructura modular.
- **DD-01** (correo único global + `company_user`) y **DD-04** (roles base
  globales + roles por empresa) aprobadas.
- **ADR-0001** (autenticación SPA con cookie + CSRF), **ADR-0002** (empresa
  activa por `X-Company-Id`) y **ADR-0003** (roles y permisos sin
  `user_permission`) aprobados.
- Las decisiones **DP-01..DP-10**, **DD-02**, **DD-03** y **DD-05** siguen
  como propuestas y no bloquean este sprint.

## 3. Arquitectura implementada

```text
backend/app/Modules/
├── Identity/
│   ├── Models/{Company,User,CompanyUser,Role,Permission}.php
│   ├── Http/Controllers/{AuthController,UserController,RoleController,PermissionController}.php
│   ├── Http/Middleware/ResolveTenant.php
│   ├── Http/Requests/...
│   ├── Http/Resources/...
│   ├── Policies/{UserPolicy,RolePolicy,PermissionPolicy}.php
│   ├── Exceptions/...
│   └── Services/{AuthenticationService,TenantContext,UserService,RoleService,PermissionService}.php
└── Shared/
    ├── Http/{ErrorResponse,PaginationRequest}.php
    └── Tenancy/{BelongsToCompany,TenantScope}.php
```

- La SPA usa la **cookie de sesión de Sanctum** y el token CSRF; el token
  Bearer queda disponible para integraciones futuras.
- La empresa activa viaja en `X-Company-Id` y se valida contra una pertenencia
  activa en `company_user`.
- La autorización combina el middleware `can:`, `Gate::before` para códigos
  `modulo.accion` y policies por recurso.
- `BelongsToCompany` + `TenantScope` filtran las consultas de modelos de dominio
  por la empresa activa, asignan `company_id` desde el contexto e impiden mover
  registros entre empresas.

## 4. Historias y criterios de aceptación

| Historia | Criterios cubiertos | Evidencia |
| --- | --- | --- |
| US-0101 | Login válido, credenciales inválidas, rutas protegidas, logout con invalidación de sesión | `tests/Feature/Auth/AuthenticationTest.php` |
| US-0102 | CRUD de usuarios, roles por empresa, activación/desactivación, rechazo de roles ajenos y escalada | `tests/Feature/Identity/UserManagementTest.php` |
| US-0103 | Permisos en backend, gating en React, permiso de costos a nivel de Gate | `tests/Feature/Identity/UserManagementTest.php` |
| US-0104 | Filtro global por empresa, `company_id` inmutable, relaciones cruzadas rechazadas | `tests/Feature/Tenancy/TenantIsolationTest.php` |

## 5. Entregables y evidencia

| Entregable | Ubicación | Estado |
| --- | --- | --- |
| Migraciones de identidad | `backend/database/migrations/` | Completo |
| API de autenticación, usuarios, roles y permisos | `backend/routes/api.php`, `app/Modules/Identity/` | Completo |
| Tenant y aislamiento | `app/Modules/Shared/Tenancy/`, `app/Modules/Identity/Services/TenantContext.php` | Completo |
| Pantallas React de acceso y usuarios | `frontend/src/features/auth/`, `frontend/src/features/identity/` | Completo |
| Seeder de demostración | `backend/database/seeders/{DatabaseSeeder,IdentitySeeder}.php` | Completo |
| Pruebas de API y UI | `backend/tests/`, `frontend/tests/` | Completo |
| Documentación | Plan, ADR y `SDD-05` | Completo |

## 6. Decisiones y ADR

| Decisión | Estado | Observaciones |
| --- | --- | --- |
| DD-01 — Correo único global + `company_user` | Aprobada | Base de la multipertenencia |
| DD-04 — Roles base globales + roles por empresa | Aprobada | Implementada en US-0102 |
| ADR-0001 — Autenticación SPA con Sanctum | Aprobada | Cookie + CSRF |
| ADR-0002 — Empresa activa por `X-Company-Id` | Aprobada | `TenantContext` y `TenantScope` |
| ADR-0003 — Roles y permisos sin `user_permission` | Aprobada | `permission_role` y `company_user.role_id` |

## 7. Evidencia de pruebas

| Verificación | Comando | Resultado |
| --- | --- | --- |
| Pruebas backend | `php artisan test` | 33 pruebas, 113 aserciones — OK |
| Estilo backend | `vendor/bin/pint --test` | Sin diferencias — OK |
| Pruebas frontend | `npm run test` | 17 pruebas — OK |
| Lint frontend | `npm run lint` | Sin errores — OK |
| Build frontend | `npm run build` | Compilado — OK |
| Smoke E2E | `npx cypress run` | 1 prueba — OK |

Matriz del sprint: T-01 a T-04 (autenticación), T-05/T-07/T-08/T-09 (usuarios y
aislamiento) y T-10 (permisos) cubiertos. T-06 (costos/márgenes) se validó a
nivel de Gate; el endpoint de métricas queda para el sprint de métricas.

## 8. Definition of Done — estado

- [x] US-0101 a US-0104 cumplen sus criterios de aceptación.
- [x] Migraciones, relaciones, índices y restricciones implementados.
- [x] Las reglas residen en Laravel; React no duplica autorización.
- [x] Validaciones y autorizaciones de backend activas.
- [x] React consume la API con estados de carga, error y vacío.
- [x] Pruebas de autenticación, permisos y aislamiento pasan en local.
- [x] Documentación técnica y de API actualizada.
- [ ] Validación contra MySQL real y `docker compose` — pendiente por entorno.

## 9. Guion de demostración

1. Iniciar sesión con `admin@empresa-demo.test` / `password`.
2. Mostrar el perfil y las empresas activas; cambiar de empresa con el selector.
3. Abrir **Usuarios**, crear un usuario, editar su rol y desactivarlo.
4. Cerrar sesión y entrar con `operador@empresa-demo.test` / `password`.
5. Comprobar que el operador no ve «Usuarios» y que la API responde `403`.
6. Mostrar que un recurso de otra empresa responde `404`.

## 10. Pendientes y bloqueos

| Pendiente | Motivo | Próximo paso |
| --- | --- | --- |
| Validar migraciones contra MySQL real | MySQL/Docker no instalados | Instalar Docker o MySQL |
| Endpoint de métricas/costos | Pertenece al sprint de métricas | Mantener el permiso `metrics.costs.view` en el Gate |
| DD-05 (borrado lógico/auditoría) | Sigue como propuesta | Resolver antes del catálogo/auditoría |
| Sprint 2 | Catálogo de productos | Planificar y ejecutar US-0201..US-0203 |

## 11. Documentos generados

| Documento | Contenido |
| --- | --- |
| [`SDD/PLAN_SPRINT_1_IDENTIDAD_MULTIEMPRESA.md`](./SDD/PLAN_SPRINT_1_IDENTIDAD_MULTIEMPRESA.md) | Plan, contratos y matriz de pruebas |
| [`SDD/ADR/ADR-0001_ESTRATEGIA_AUTENTICACION_SPA.md`](./SDD/ADR/ADR-0001_ESTRATEGIA_AUTENTICACION_SPA.md) | Autenticación SPA |
| [`SDD/ADR/ADR-0002_ESTRATEGIA_RESOLUCION_TENANT.md`](./SDD/ADR/ADR-0002_ESTRATEGIA_RESOLUCION_TENANT.md) | Empresa activa |
| [`SDD/ADR/ADR-0003_MODELO_PERMISOS.md`](./SDD/ADR/ADR-0003_MODELO_PERMISOS.md) | Roles y permisos |
| [`SDD/SDD-05_DISENO_API.md`](./SDD/SDD-05_DISENO_API.md) | Contratos de API actualizados |
| [`SDD/ADR/REGISTRO_DECISIONES_SPRINT_0.md`](./SDD/ADR/REGISTRO_DECISIONES_SPRINT_0.md) | Aprobaciones registradas |

## 12. Commits del sprint

```text
d614ebc feat(identity): implementar inicio y cierre de sesion (US-0101)
ebd0648 docs(sprint-1): registrar aprobaciones y evidencia de US-0101
abf8610 fix(auth): usar rutas versionadas /api/v1 en la SPA
d774792 feat(identity): administrar usuarios y asignar roles (US-0102)
85ba17e docs(sprint-1): documentar usuarios, roles y permisos (US-0102)
0f2d6b2 feat(identity): aplicar permisos por modulo y accion (US-0103)
b389054 docs(sprint-1): documentar permisos por modulo y accion (US-0103)
a54bc64 feat(tenancy): aislar datos por empresa con TenantScope (US-0104)
f7d5216 docs(sprint-1): documentar aislamiento por empresa (US-0104)
```

## 13. Próximo sprint

**Sprint 2 — Catálogo de productos (EP-02):** categorías, marcas, productos,
variantes y códigos. Ver [`README_SPRINT_2.md`](./README_SPRINT_2.md).

## 14. Documentos relacionados

- [`README_SPRINT_0.md`](./README_SPRINT_0.md) — cierre del Sprint 0.
- [`README_SPRINT_2.md`](./README_SPRINT_2.md) — cierre del Sprint 2.
- [`README.md`](./README.md) — índice general de la documentación.
- [`../CHANGELOG.md`](../CHANGELOG.md) — historial de cambios.
