# SDD-01 — Arquitectura general

**Proyecto:** SaaS Inversiones R&P
**Documento:** Diseño de arquitectura general
**Versión:** 1.0 — Sprint 0
**Actualización:** v1.1 — 2026-11-15 (integración documental CX/UX/UI y auth social; no implementada)
**Fecha:** 2026-11-12
**Estado:** Borrador aprobado para el MVP

---

## 1. Contexto

SaaS Inversiones R&P es un sistema SaaS de gestión de inventario y almacén
orientado a pequeñas y medianas empresas. El MVP debe cubrir autenticación,
catálogo, unidades y ubicaciones, núcleo de inventario, compras y traslados,
métricas y alertas, conteos físicos y consulta inteligente de solo lectura.

El alcance excluye punto de venta completo, facturación electrónica,
contabilidad, aplicación móvil nativa y microservicios.

## 2. Vista de arquitectura

```text
┌─────────────────────────────┐        HTTPS / JSON         ┌─────────────────────────────┐
│  Frontend — React 19 + Vite │  ───────────────────────▶   │  Backend — Laravel 13       │
│  TypeScript · React Router  │  ◀───────────────────────   │  Monolito modular · REST    │
└─────────────────────────────┘        API /api/v1          └──────────────┬──────────────┘
                                                                           │ PDO / SQL
                                                                           ▼
                                                                ┌────────────────────┐
                                                                │  MySQL 8.0         │
                                                                │  utf8mb4 · InnoDB  │
                                                                └────────────────────┘
```

Componentes:

| Capa | Tecnología | Responsabilidad |
| --- | --- | --- |
| Presentación | React + TypeScript + Vite | Formularios, tablas, navegación y consumo de API. No accede a MySQL. |
| Aplicación | Laravel 13 (PHP 8.3) | Reglas de negocio, validaciones, autorización, transacciones y API REST. |
| Persistencia | MySQL 8.0 | Integridad referencial, restricciones, índices y aislamiento por empresa. |
| Comunicación | REST / JSON | Contratos versionados y respuestas consistentes. |
| Pruebas | PHPUnit, Vitest, Cypress | Unitarias, integración, componentes y aceptación E2E. |
| Entrega | Docker + GitHub Actions | Entornos reproducibles y validación automatizada. |

## 3. Decisiones estructurales

1. **Monolito modular, no microservicios.** El MVP se implementa como una sola
   aplicación Laravel organizada por módulos de dominio.
2. **Separación estricta frontend/backend.** React consume la API; jamás abre
   conexiones a la base de datos.
3. **Fuente única de verdad.** `backend/database/migrations/` es la fuente
   oficial del esquema. `database/` en la raíz solo contiene documentación y
   scripts auxiliares.
4. **Multiempresa desde el Sprint 0.** Todas las tablas de dominio incluyen
   `company_id` y las consultas se acotan al tenant autenticado.
5. **Integridad de inventario.** Todo cambio de existencias pasa por un servicio
   central y se ejecuta dentro de una transacción.
6. **Operaciones confirmadas inmutables.** Se corrigen con reversiones o
   movimientos compensatorios auditables, nunca con borrados físicos.
7. **Configuración por ambiente.** Variables de entorno separadas para local,
   test y producción; secretos fuera del repositorio.
8. **API versionada.** Prefijo `/api/v1` a partir del Sprint 1. El endpoint de
   salud `/api/health` queda fuera del versionado como sonda operativa.

## 4. Módulos del backend

| Módulo | Ruta | Responsabilidad |
| --- | --- | --- |
| Identity | `app/Modules/Identity` | Empresas, usuarios, roles, permisos y autenticación. |
| Catalog | `app/Modules/Catalog` | Productos, categorías, marcas, variantes y códigos. |
| Inventory | `app/Modules/Inventory` | Existencias, movimientos, ajustes y reversiones. |
| Purchasing | `app/Modules/Purchasing` | Proveedores, compras, costos y traslados. |
| Metrics | `app/Modules/Metrics` | Indicadores operativos y alertas de stock. |
| Audit | `app/Modules/Audit` | Trazabilidad, auditoría y conteos. |

Servicios transversales en `app/Services`, validaciones en
`app/Http/Requests` y autorización en `app/Policies`.

## 5. Comunicación y contratos

- Formato: JSON UTF-8.
- Autenticación: Laravel Sanctum (cookie de SPA + CSRF; tokens Bearer
  disponibles para integraciones futuras, ver ADR-0001).
- Autorización: verificada en el backend con Policies y middleware; el frontend
  solo oculta opciones, nunca es la única barrera.
- Errores: contrato único `{ "error": { "code", "message", "details", "trace_id" } }`
  (ver `Reglas/ESTANDAR.md`, sección 6.8).
- Paginación: `data` + `meta` (`current_page`, `last_page`, `per_page`, `total`).
- Versionado: `/api/v1/...`; los cambios incompatibles crean una nueva versión.

## 6. Ambientes

| Ambiente | Uso | Configuración |
| --- | --- | --- |
| Local | Desarrollo diario | Docker Compose o XAMPP; `.env` local |
| Test | CI y pruebas automatizadas | SQLite en memoria para PHPUnit; variables en `phpunit.xml` |
| Producción | Despliegue | Variables inyectadas por el orquestador; secretos gestionados |

## 7. Requisitos no funcionales iniciales

| Atributo | Objetivo inicial |
| --- | --- |
| Disponibilidad | 99.5 % mensual (pendiente DP-07) |
| Tiempo de respuesta API | p95 < 500 ms en operaciones de lectura |
| Seguridad | OWASP Top 10, control de acceso por rol y tenant |
| Auditoría | Registro de operaciones que afectan existencias |
| Respaldo | Respaldo diario de MySQL y prueba de restauración (pendiente DP-06) |
| Calidad | Cobertura mínima según `Reglas/ESTANDAR.md`, sección 8.3 |

## 8. Riesgos arquitectónicos

| Riesgo | Impacto | Mitigación |
| --- | --- | --- |
| Fuga de datos entre empresas | Crítico | `company_id` obligatorio, scopes globales y pruebas de aislamiento |
| Concurrencia en movimientos | Alto | Transacciones, bloqueo de filas y validación de stock |
| Decisiones DP/DD abiertas | Alto | Registro de decisiones con estado y bloqueo de historias dependientes |
| Crecimiento del monolito | Medio | Módulos por dominio y límites claros de servicios |
| Dependencia de Docker | Bajo | Alternativa documentada con XAMPP/PHP local |

## 9. Documentos relacionados

- `SDD-02_ARQUITECTURA_FRONTEND.md` *(pendiente)*
- `SDD-03_ARQUITECTURA_BACKEND.md` *(pendiente)*
- `SDD-04_DISENO_BASE_DATOS.md`
- `SDD-05_DISENO_API.md` *(pendiente)*
- `SDD-06_SEGURIDAD.md` *(pendiente)*
- `SDD-08_INFRAESTRUCTURA.md` *(pendiente)*
- `SDD-09_ESTRUCTURA_REPOSITORIO.md`
- `ADR/REGISTRO_DECISIONES_SPRINT_0.md`

## 10. Actualización v1.1 — autenticación social y experiencia

> **Estado:** propuesta documental. No hay código, migraciones ni pantallas
> nuevas de autenticación social al 2026-11-15. Esta sección amplía el diseño;
> no reemplaza las decisiones de los Sprints 0–5.

### 10.1 Autenticación con Google y Facebook

Se incorporan Google (OpenID Connect) y Facebook (flujo oficial de Meta) como
métodos adicionales al login local. La identidad externa **solo autentica**:
la empresa activa, el rol y los permisos se siguen resolviendo en la base
interna (`company_user`, `roles`, `permissions`).

Flujo propuesto:

1. React muestra «Continuar con Google» y «Continuar con Facebook».
2. Laravel inicia OAuth en rutas `web` con `state` de un solo uso.
3. El proveedor autentica y llama al callback; Laravel valida la respuesta.
4. Se busca la identidad en `social_identities` por
   `(provider, provider_user_id)`; nunca por correo.
5. Si está vinculada y el usuario está activo, se crea la sesión Sanctum
   existente y se redirige a React.
6. Si no está vinculada, se ofrece un flujo autorizado de invitación o
   vinculación; no se concede acceso al inventario.

Controles: HTTPS, callbacks registrados, PKCE cuando corresponda, cookies
`HttpOnly`/`Secure`/`SameSite`, rate limiting, secretos solo en servidor y
auditoría sin tokens. El detalle está en
[`SEGURIDAD/AUTH-01_AUTENTICACION_SOCIAL.md`](../SEGURIDAD/AUTH-01_AUTENTICACION_SOCIAL.md)
y en los ADR-0004/0005.

### 10.2 Capa de experiencia y sistema de diseño

Se adopta como referencia el sistema de diseño propuesto en
[`CX_UX_UI/UI-01_SISTEMA_DE_DISENO.md`](../CX_UX_UI/UI-01_SISTEMA_DE_DISENO.md)
y los flujos de
[`CX_UX_UI/UX-01_FLUJOS_DE_USUARIO.md`](../CX_UX_UI/UX-01_FLUJOS_DE_USUARIO.md).
Criterios compatibles con la arquitectura actual:

- Componentes React reutilizables y estados explícitos de carga, vacío, error,
  éxito y sin permisos.
- Objetivo WCAG 2.2 AA: teclado, foco visible, etiquetas persistentes,
  contraste y mensajes asociados.
- Confirmación y resumen antes de operaciones de stock; el backend sigue siendo
  la única autoridad de validación.
- No exponer costos/márgenes a roles sin `metrics.costs.view`.
- Diseño responsive sin depender de una app móvil nativa.

La implementación es incremental y no modifica reglas de inventario ni API.
Ver ADR-0006 y el registro
[`INTEGRACION_V1_1_CX_UX_AUTH.md`](./INTEGRACION_V1_1_CX_UX_AUTH.md).
