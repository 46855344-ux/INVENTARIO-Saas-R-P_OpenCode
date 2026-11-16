# Changelog

Todos los cambios relevantes de este proyecto se documentan en este archivo.

El formato sigue las convenciones de [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/)
y el versionado semántico de [SemVer](https://semver.org/lang/es/).

## [No publicado]

### Añadido

- **Sprint 0 — Fundación técnica**
  - Backend Laravel 13.42 con PHP 8.3, Laravel Sanctum 4.4 y PHPUnit 12.5.
  - Frontend React 19.3 + TypeScript 7 + Vite 8.3, con Vitest 5.1 y Testing Library.
  - Cypress 16.3 configurado en la raíz (`tests/e2e`, `tests/fixtures`, `tests/support`).
  - Endpoint `GET /api/health` y prueba de integración asociada.
  - Estructura modular del backend: `Identity`, `Catalog`, `Inventory`, `Purchasing`, `Metrics`, `Audit`.
  - Estructura por dominio del frontend: `auth`, `products`, `inventory`, `movements`, `suppliers`, `metrics`, `alerts`.
  - Docker: `Dockerfile.backend`, `Dockerfile.frontend`, `php.ini`, `entrypoint.backend.sh`.
  - `docker-compose.yml` con backend, frontend y MySQL 8.0 (`utf8mb4`).
  - Virtual host de referencia para Apache y guía de entorno XAMPP.
  - Pipeline CI con trabajos de backend (PHP 8.3, Pint, PHPUnit) y frontend (Node 22, lint, test, build).
  - Plantillas `.env.example` (raíz y backend) y `.dockerignore`.
  - `tsconfig.json` raíz y `@types/node` para compilar las pruebas Cypress.
  - Scripts de preparación (`setup.ps1`, `setup.sh`), respaldo y restauración de MySQL.
  - Documentación: `SDD-01`, `SDD-04`, `SDD-09`, registro de decisiones DP/DD, acta de cierre del Sprint 0, manual de instalación, plan de pruebas y guía de CI/CD.
  - Script SQL de inicialización `database/scripts/01-init.sql`.
  - **Cierre documental del Sprint 0:**
    - `DOCUMENTACION/README_SPRINT_0.md`: README de cierre con entregables, evidencias, DoD, demo, pendientes y guía de iteraciones.
    - `DOCUMENTACION/SDD/SDD-05_DISENO_API.md`: contratos, versionado, autenticación, tenant y error de la API.
    - `database/diagrams/DER_LOGICO.md`: diagrama entidad-relación lógico y clasificación de tablas.
    - `database/dictionaries/DICCIONARIO_DATOS.md`: diccionario de datos con tipos, claves, índices y matriz de ámbito.
    - `REGISTRO_DECISIONES_SPRINT_0.md` ampliado con la plantilla de cierre (alternativas, fundamento, aprobador, migraciones/API afectadas y enlace ADR).
  - **Planificación del Sprint 1 (EP-01):**
    - `DOCUMENTACION/SDD/PLAN_SPRINT_1_IDENTIDAD_MULTIEMPRESA.md`: checklist de entrada, backlog refinado, diseño, contratos API, matriz de permisos y matriz de pruebas.
    - `DOCUMENTACION/SDD/ADR/ADR-0001_ESTRATEGIA_AUTENTICACION_SPA.md`: propuesta de autenticación SPA.
    - `DOCUMENTACION/SDD/ADR/ADR-0002_ESTRATEGIA_RESOLUCION_TENANT.md`: propuesta de resolución de la empresa activa.
    - `DOCUMENTACION/SDD/ADR/ADR-0003_MODELO_PERMISOS.md`: propuesta de roles y permisos.
  - **Sprint 1 — US-0101 (inicio y cierre de sesión):**
    - Migraciones `companies`, `company_user` y ampliación de `users` (`status` y borrado lógico).
    - Modelos `App\Modules\Identity\Models\{Company,User}` con pertenencia multiempresa y fábricas asociadas.
    - Autenticación SPA con Sanctum (cookie + CSRF): `POST /api/v1/auth/login`, `POST /api/v1/auth/logout`, `GET /api/v1/auth/me`.
    - Contrato de error único con `trace_id` (`VALIDATION_ERROR`, `INVALID_CREDENTIALS`, `ACCOUNT_INACTIVE`, `UNAUTHENTICATED`).
    - Pantalla React de acceso, contexto de autenticación, guardia de rutas y cierre de sesión desde el layout.
    - Seeder de demostración con dos empresas y dos usuarios.
    - Pruebas backend (12), frontend (2), lint, build y smoke Cypress verificados.
  - **Sprint 1 — US-0102 (administración de usuarios y roles):**
    - Migraciones `permissions`, `roles`, `permission_role` y `company_user.role_id`.
    - Roles base globales y catálogo de permisos `modulo.accion` con `IdentitySeeder`.
    - `TenantContext` y middlewares `tenant`/`permission` con `X-Company-Id` validado.
    - Endpoints `/api/v1/users` (listar, crear, ver, editar, estado y rol), `/api/v1/roles` y `/api/v1/permissions`.
    - Reglas de ámbito: recurso de otra empresa → 404, rol ajeno → 422, escalada de privilegios → 403.
    - Pantalla React de usuarios con filtros, paginación, alta, edición, estado y asignación de rol.
    - Pruebas de identidad (15) y del servicio frontend (6); smoke Cypress ajustado a 15 s.
  - **Sprint 1 — US-0103 (permisos por módulo y acción):**
    - `Gate::before` para resolver códigos `modulo.accion` y policies por recurso (`UserPolicy`, `RolePolicy`, `PermissionPolicy`).
    - Rutas con middleware `can:` y controladores con `$this->authorize(...)`.
    - Errores 403/404 unificados con el contrato de error.
    - Componente React `Can` y gating de navegación y acciones de usuarios.
    - Pruebas de autorización positivas/negativas y del permiso `metrics.costs.view` (T-06 a nivel de Gate).
  - **Sprint 1 — US-0104 (aislamiento por empresa):**
    - Trait `BelongsToCompany` y `TenantScope` global para modelos de dominio.
    - `company_id` asignado desde `TenantContext` al crear e inmutable al actualizar.
    - Helper `belongsToSameCompany` para validar relaciones compuestas.
    - Pruebas de aislamiento con modelo de fixture (lectura, alta, actualización y relación cruzada).
  - **Sprint 2 — US-0201 (categorías y marcas):**
    - Migraciones `categories` y `brands` con `company_id`, estado y unicidad por empresa.
    - Modelos `Category`/`Brand` con `BelongsToCompany`; políticas y `CatalogService`.
    - Endpoints `/api/v1/categories` y `/api/v1/brands` (listar, crear, ver, editar y estado).
    - Permisos `catalog.view`/`catalog.manage` en `IdentitySeeder`.
    - Pantalla React «Catálogo» con pestañas, filtros, alta, edición y desactivación.
    - Pruebas de API (12) y de frontend para el catálogo.
  - **Sprint 2 — US-0202 (productos):**
    - Migración `products` con categoría/marca y unicidad por empresa.
    - Modelo `Product`, `ProductResource`, `ProductPolicy` y métodos en `CatalogService`.
    - Endpoints `/api/v1/products` (listar, crear, ver, editar y estado).
    - Validación de categoría/marca de la misma empresa.
    - Pestaña React «Productos» con filtros, formulario y desactivación.
    - Pruebas de API (10) y de frontend para productos.
  - **Sprint 2 — US-0203 (variantes y códigos):**
    - Migración `product_variants` con SKU y código de barras únicos por empresa.
    - Modelo, resource, policy y métodos de `CatalogService`.
    - Endpoints `/api/v1/products/{id}/variants` y `/api/v1/variants/{id}`.
    - UI de variantes por producto con alta, edición y estado.
    - Pruebas de API (10) y de frontend para variantes.
  - **Sprint 3 — US-0301 a US-0303 (unidades, conversiones y ubicaciones):**
    - Migraciones `units`, `unit_conversions` y `locations`.
    - Modelos, validaciones, policies y servicios de configuración.
    - `UnitConversionService` con factor decimal, inversa automática y redondeo `HALF_UP` a 4 decimales.
    - Endpoints `/api/v1/units`, `/api/v1/conversions` y `/api/v1/locations`.
    - Guardia preparada para no desactivar ubicaciones con stock (Sprint 4).
    - Pantalla React «Configuración» con pestañas, simulador y CRUD.
    - Pruebas de API (28) y de frontend para configuración.
  - **Sprint 4 — US-0401 a US-0406 (núcleo de inventario):**
    - Migraciones `stock_balances`, `stock_movements` y `stock_movement_lines`.
    - `InventoryMovementService` transaccional con idempotencia, saldo no negativo y reversión compensatoria.
    - Endpoints `/api/v1/inventory/stocks`, `/movements`, `/entries`, `/exits`, `/returns`, `/adjustments` y `/movements/{id}/reverse`.
    - Pantalla React «Inventario» con existencias, movimientos y formularios.
    - Pruebas de API (14) y de frontend para inventario.
  - **Sprint 5 — US-0501 a US-0503 (compras y traslados):**
    - Migraciones `suppliers`, `purchases`, `purchase_items`, `transfers` y `transfer_items`.
    - Referencia de origen (`reference_type`/`reference_id`) en `stock_movements`.
    - CRUD de proveedores con desactivación que preserva compras históricas.
    - Compras en borrador, edición y costos históricos por línea; recepción total idempotente vía `InventoryMovementService`.
    - Traslados atómicos con `transfer_out`/`transfer_in`, validación de saldo y saldo global cero.
    - Endpoints `/api/v1/suppliers`, `/purchases`, `/purchases/{id}/receive` y `/transfers`.
    - Pantalla React «Compras y traslados» con proveedores, compras y traslados.
    - Pruebas de API (24) y de frontend para compras y traslados.
  - **Sprint 6 — US-0601 a US-0603 (métricas y alertas):**
    - Migraciones `alert_rules`, `stock_alerts` y `alert_evaluation_runs`.
    - `InventoryMetricsService` con indicadores, detalle, más salidas e inactividad.
    - `InventoryAlertService` con reglas por variante + ubicación, deduplicación, resolución y corridas.
    - Comando `alerts:evaluate` programado a diario; notificaciones solo in-app.
    - Endpoints `/api/v1/metrics/overview`, `/metrics/stock-detail`, `/alerts` y `/alert-rules`.
    - Pantalla React «Métricas y alertas» con tarjetas, tablas, filtros y estados.
    - Pruebas de API (7) y de frontend para métricas y alertas.
  - **Sprint 7 — US-0701 a US-0703 (conteos y auditoría):**
    - Migraciones `stock_counts`, `stock_count_items` y `audit_logs`.
    - Conteos con stock teórico congelado, diferencias y estados open/reviewing/approved.
    - Aprobación transaccional con ajustes vía `InventoryMovementService` y bloqueo de doble aprobación.
    - Auditoría append-only de creación, revisión y aprobación.
    - Endpoints `/api/v1/stock-counts` y `/api/v1/audit-events`.
    - Pantalla React «Conteos y auditoría» con formulario, revisión, aprobación y filtros.
    - Pruebas de API (4) y de frontend para conteos y auditoría.
  - **Sprint 8 — US-0801 (chatbot de solo lectura):**
    - `ChatAssistantService` determinista con intenciones de stock, agotados, mínimos, salidas y resumen.
    - Endpoints `/api/v1/assistant/query` y `/assistant/suggestions`.
    - Rechazo de escritura, SQL y prompt injection; sin LLM externo.
    - Registro mínimo en `chat_queries` sin preguntas ni respuestas.
    - Pantalla React «Asistente» con sugerencias, estados y aviso de solo lectura.
    - US-0802 diferida por falta de fuente verificable de temporada.
    - Pruebas de API (4) y de frontend para el asistente.
  - **Documentación de cierre de sprints:**
    - `DOCUMENTACION/README_SPRINT_1.md`: cierre del Sprint 1 (identidad, usuarios, permisos y aislamiento).
    - `DOCUMENTACION/README_SPRINT_2.md`: cierre del Sprint 2 (catálogo de productos y variantes).
    - `DOCUMENTACION/README_SPRINT_3.md`: cierre del Sprint 3 (unidades, conversiones y ubicaciones).
  - **Planificación del Sprint 4 (EP-04):**
    - `DOCUMENTACION/SDD/PLAN_SPRINT_4_INVENTARIO_CENTRAL.md`: alcance US-0401 a US-0406, decisiones, modelo, contratos, reglas transaccionales, matriz de pruebas y DoR/DoD.

### Incremento v1.1 — autenticación social y experiencia (2026-11-15)

- Dependencia `laravel/socialite` 5.32 y configuración Google/Facebook por ambiente.
- Migración `social_identities` con `UNIQUE(provider, provider_user_id)` y `UNIQUE(user_id, provider)`.
- Servicio `SocialAuthService`, rutas web de redirect/callback/link y API de proveedores, cuentas y desvinculación.
- Sesión Sanctum reutilizada; el login social no concede empresa, rol ni permisos.
- Login React con botones condicionados a proveedores configurados.
- Página «Mi cuenta» para vincular, desvincular y consultar métodos de acceso.
- Errores seguros `SOCIAL_AUTH_FAILED`, `SOCIAL_IDENTITY_NOT_LINKED`, `SOCIAL_IDENTITY_CONFLICT` y `LAST_AUTH_METHOD`.
- Pruebas backend (140/523) y frontend (65), Pint, lint y build correctos.
- Validación con Google/Meta reales pendiente de credenciales; pruebas con proveedores simulados.

### Documentación — integración v1.1 (2026-11-15)

Integración documental de los requisitos de CX, UX, UI y autenticación con
Google/Facebook. **No se modificó código ni se ejecutaron migraciones.**

- Nuevo `DOCUMENTACION/SDD/INTEGRACION_V1_1_CX_UX_AUTH.md` con comparación de
  fuentes, conflictos C-01 a C-12, resoluciones y archivos afectados.
- Nuevo `DOCUMENTACION/SDD/PLAN_INCREMENTO_V1_1_AUTH_CX.md` con alcance,
  decisiones a ratificar, arquitectura, fases y pruebas del incremento v1.1.
- Nuevo `DOCUMENTACION/SDD/ADR/REGISTRO_DECISIONES_V1_1_CX_UX_AUTH.md` con
  ADR-0004 a ADR-0007 en estado «propuesta».
- `SDD-01` ampliado con la arquitectura de auth social y la capa CX/UX/UI.
- `SDD-04` ampliado con la propuesta `social_identities` (sin migración).
- `SDD-05` ampliado con endpoints sociales propuestos, errores y estados UX.
- `ADR-0001` a `ADR-0003` ampliados con notas de compatibilidad; decisiones
  originales conservadas.
- Nuevos índices `DOCUMENTACION/Docs/README.md` y
  `DOCUMENTACION/CX_UX_UI/README.md` que distinguen referencia de documento
  canónico.
- `DOCUMENTACION/README.md` actualizado con la sección de integración v1.1.
- `DEVSECOPS/CI_CD.md` ampliado con controles propuestos para secretos OAuth y
  pruebas con mocks.
- Documentos de entrada conservados sin reemplazar los SDD existentes.
- Cierres de Sprint 0 a 5 sin cambios.

### Cambiado

- `README.md` actualizado con stack, estructura, inicio rápido, scripts y estado del Sprint 0.
- `.env.example` alineado con MySQL 8, zona horaria UTC y variables de Docker.
- `.gitignore` ampliado para entorno, dependencias, reportes y respaldos.
- `composer.json` del backend con Sanctum y Laravel Boost como dependencia de desarrollo.
- `phpunit.xml` configurado con SQLite en memoria para pruebas sin MySQL.
- `config/sanctum.php` y `config/cors.php` con dominios stateful y CORS con credenciales para la SPA.
- `.env.example` del backend con `FRONTEND_URL` y `SANCTUM_STATEFUL_DOMAINS`.
- `config/auth.php` y las fábricas apuntan al modelo de usuario del módulo `Identity`.
- El layout protegido incorpora navegación, selector de empresa activa y cabecera `X-Company-Id` en el cliente HTTP.
- Las respuestas de identidad usan `API Resources` y paginación estándar.

### Corregido

- Codificación UTF-8 y acentos en la documentación y archivos de configuración.
- Prueba de componentes del frontend ajustada para evitar coincidencias múltiples de texto.
- Estilo de `bootstrap/app.php` normalizado con Pint.
- Configuración de TypeScript para que Cypress compile los specs (`TsConfigNotFoundError` resuelto).

### Eliminado

- Archivos de ejemplo del template de Vite (`App.css`, `index.css`, logos e íconos sin uso).
- `AGENTS.md` permanece como guía de Laravel Boost; no se reinstala.

---

## [0.1.0] - 2026-11-12

### Añadido

- Versión inicial del proyecto.
- `Reglas/ESTANDAR.md`: reglas obligatorias de programación (ISO 25001/25010/27001/19119/29119, convenciones de nombres, SOLID, BDD, multiempresa, integridad de inventario, seguridad, pruebas y checklist de cierre), con glosario bilingüe español → inglés.
- Estructura inicial del repositorio (`frontend`, `backend`, `database`, `tests`, `infrastructure`).
- Carpetas de documentación oficial (`DOCUMENTACION/`).
- Scripts de automatización (`scripts/`).
- Pipeline CI/CD base en `.github/workflows/`.
- Plantilla de variables de entorno `.env.example`.
- Configuración de `.gitignore`.
- `docker-compose.yml` base para el entorno de desarrollo.
