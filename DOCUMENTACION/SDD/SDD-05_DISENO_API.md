# SDD-05 — Diseño de API REST

**Proyecto:** SaaS Inversiones R&P
**Documento:** Contratos y convenciones de la API
**Versión:** 1.0 — Sprint 0
**Actualización:** v1.1 — 2026-11-15 (extensión propuesta de auth social; no implementada)
**Fecha:** 2026-11-13
**Estado:** Convenciones vigentes; endpoints funcionales marcados como implementados

---

## 1. Principios

1. React consume la API; no accede a MySQL.
2. Laravel valida, autoriza y ejecuta las reglas de negocio.
3. El `company_id` se resuelve desde el usuario autenticado, nunca desde el
   cuerpo de la solicitud.
4. Los contratos son versionados y compatibles hacia atrás dentro de una versión.
5. Los errores usan un contrato único y no exponen trazas internas.

## 2. Ruta base y versionado

| Aspecto | Convención |
| --- | --- |
| URL base local | `http://localhost:8000` |
| API versionada | `/api/v1/...` |
| Sonda operativa | `/api/health` (sin versión) |
| Formato | JSON UTF-8 (`Accept: application/json`) |
| Autenticación | Laravel Sanctum (cookie de SPA + CSRF; tokens Bearer para integraciones) |

**Implementado (Sprints 0 a 5, incremento v1.1 y Sprint 6):**

| Método | Ruta | Autenticación | Permiso |
| --- | --- | --- | --- |
| `GET` | `/api/health` | Pública | — |
| `POST` | `/api/v1/auth/login` | Pública | — |
| `POST` | `/api/v1/auth/logout` | `auth:sanctum` | — |
| `GET` | `/api/v1/auth/me` | `auth:sanctum` | — |
| `GET` | `/api/v1/auth/social/providers` | Pública | — |
| `GET` | `/api/v1/auth/social/accounts` | `auth:sanctum` | — |
| `DELETE` | `/api/v1/auth/social/{provider}` | `auth:sanctum` | — |
| `GET` | `/api/v1/users` | `auth:sanctum` + `X-Company-Id` | `identity.users.view` |
| `POST` | `/api/v1/users` | `auth:sanctum` + `X-Company-Id` | `identity.users.manage` |
| `GET` | `/api/v1/users/{id}` | `auth:sanctum` + `X-Company-Id` | `identity.users.view` |
| `PUT` | `/api/v1/users/{id}` | `auth:sanctum` + `X-Company-Id` | `identity.users.manage` |
| `PATCH` | `/api/v1/users/{id}/status` | `auth:sanctum` + `X-Company-Id` | `identity.users.manage` |
| `PUT` | `/api/v1/users/{id}/role` | `auth:sanctum` + `X-Company-Id` | `identity.users.manage` |
| `GET` | `/api/v1/roles` | `auth:sanctum` + `X-Company-Id` | `identity.roles.view` |
| `GET` | `/api/v1/permissions` | `auth:sanctum` + `X-Company-Id` | `identity.roles.view` |
| `GET` | `/api/v1/categories` | `auth:sanctum` + `X-Company-Id` | `catalog.view` |
| `POST` | `/api/v1/categories` | `auth:sanctum` + `X-Company-Id` | `catalog.manage` |
| `GET` | `/api/v1/categories/{id}` | `auth:sanctum` + `X-Company-Id` | `catalog.view` |
| `PUT` | `/api/v1/categories/{id}` | `auth:sanctum` + `X-Company-Id` | `catalog.manage` |
| `PATCH` | `/api/v1/categories/{id}/status` | `auth:sanctum` + `X-Company-Id` | `catalog.manage` |
| `GET` | `/api/v1/brands` | `auth:sanctum` + `X-Company-Id` | `catalog.view` |
| `POST` | `/api/v1/brands` | `auth:sanctum` + `X-Company-Id` | `catalog.manage` |
| `GET` | `/api/v1/brands/{id}` | `auth:sanctum` + `X-Company-Id` | `catalog.view` |
| `PUT` | `/api/v1/brands/{id}` | `auth:sanctum` + `X-Company-Id` | `catalog.manage` |
| `PATCH` | `/api/v1/brands/{id}/status` | `auth:sanctum` + `X-Company-Id` | `catalog.manage` |
| `GET` | `/api/v1/products` | `auth:sanctum` + `X-Company-Id` | `catalog.view` |
| `POST` | `/api/v1/products` | `auth:sanctum` + `X-Company-Id` | `catalog.manage` |
| `GET` | `/api/v1/products/{id}` | `auth:sanctum` + `X-Company-Id` | `catalog.view` |
| `PUT` | `/api/v1/products/{id}` | `auth:sanctum` + `X-Company-Id` | `catalog.manage` |
| `PATCH` | `/api/v1/products/{id}/status` | `auth:sanctum` + `X-Company-Id` | `catalog.manage` |
| `GET` | `/api/v1/products/{id}/variants` | `auth:sanctum` + `X-Company-Id` | `catalog.view` |
| `POST` | `/api/v1/products/{id}/variants` | `auth:sanctum` + `X-Company-Id` | `catalog.manage` |
| `GET` | `/api/v1/variants/{id}` | `auth:sanctum` + `X-Company-Id` | `catalog.view` |
| `PUT` | `/api/v1/variants/{id}` | `auth:sanctum` + `X-Company-Id` | `catalog.manage` |
| `PATCH` | `/api/v1/variants/{id}/status` | `auth:sanctum` + `X-Company-Id` | `catalog.manage` |
| `GET` | `/api/v1/units` | `auth:sanctum` + `X-Company-Id` | `settings.view` |
| `POST` | `/api/v1/units` | `auth:sanctum` + `X-Company-Id` | `settings.manage` |
| `GET` | `/api/v1/units/{id}` | `auth:sanctum` + `X-Company-Id` | `settings.view` |
| `PUT` | `/api/v1/units/{id}` | `auth:sanctum` + `X-Company-Id` | `settings.manage` |
| `PATCH` | `/api/v1/units/{id}/status` | `auth:sanctum` + `X-Company-Id` | `settings.manage` |
| `GET` | `/api/v1/conversions` | `auth:sanctum` + `X-Company-Id` | `settings.view` |
| `POST` | `/api/v1/conversions` | `auth:sanctum` + `X-Company-Id` | `settings.manage` |
| `POST` | `/api/v1/conversions/simulate` | `auth:sanctum` + `X-Company-Id` | `settings.view` |
| `PUT` | `/api/v1/conversions/{id}` | `auth:sanctum` + `X-Company-Id` | `settings.manage` |
| `PATCH` | `/api/v1/conversions/{id}/status` | `auth:sanctum` + `X-Company-Id` | `settings.manage` |
| `GET` | `/api/v1/variants` | `auth:sanctum` + `X-Company-Id` | `catalog.view` |
| `GET` | `/api/v1/locations` | `auth:sanctum` + `X-Company-Id` | `settings.view` |
| `POST` | `/api/v1/locations` | `auth:sanctum` + `X-Company-Id` | `settings.manage` |
| `GET` | `/api/v1/locations/{id}` | `auth:sanctum` + `X-Company-Id` | `settings.view` |
| `PUT` | `/api/v1/locations/{id}` | `auth:sanctum` + `X-Company-Id` | `settings.manage` |
| `PATCH` | `/api/v1/locations/{id}/status` | `auth:sanctum` + `X-Company-Id` | `settings.manage` |
| `GET` | `/api/v1/inventory/stocks` | `auth:sanctum` + `X-Company-Id` | `inventory.view` |
| `GET` | `/api/v1/inventory/movements` | `auth:sanctum` + `X-Company-Id` | `inventory.view` |
| `GET` | `/api/v1/inventory/movements/{id}` | `auth:sanctum` + `X-Company-Id` | `inventory.view` |
| `POST` | `/api/v1/inventory/entries` | `auth:sanctum` + `X-Company-Id` | `inventory.move` |
| `POST` | `/api/v1/inventory/exits` | `auth:sanctum` + `X-Company-Id` | `inventory.move` |
| `POST` | `/api/v1/inventory/returns` | `auth:sanctum` + `X-Company-Id` | `inventory.move` |
| `POST` | `/api/v1/inventory/adjustments` | `auth:sanctum` + `X-Company-Id` | `inventory.adjust` |
| `POST` | `/api/v1/inventory/movements/{id}/reverse` | `auth:sanctum` + `X-Company-Id` | `inventory.approve` |
| `GET` | `/api/v1/suppliers` | `auth:sanctum` + `X-Company-Id` | `purchases.view` |
| `POST` | `/api/v1/suppliers` | `auth:sanctum` + `X-Company-Id` | `purchases.manage` |
| `GET` | `/api/v1/suppliers/{id}` | `auth:sanctum` + `X-Company-Id` | `purchases.view` |
| `PUT` | `/api/v1/suppliers/{id}` | `auth:sanctum` + `X-Company-Id` | `purchases.manage` |
| `PATCH` | `/api/v1/suppliers/{id}/status` | `auth:sanctum` + `X-Company-Id` | `purchases.manage` |
| `GET` | `/api/v1/purchases` | `auth:sanctum` + `X-Company-Id` | `purchases.view` |
| `POST` | `/api/v1/purchases` | `auth:sanctum` + `X-Company-Id` | `purchases.manage` |
| `GET` | `/api/v1/purchases/{id}` | `auth:sanctum` + `X-Company-Id` | `purchases.view` |
| `PUT` | `/api/v1/purchases/{id}` | `auth:sanctum` + `X-Company-Id` | `purchases.manage` |
| `POST` | `/api/v1/purchases/{id}/receive` | `auth:sanctum` + `X-Company-Id` | `purchases.receive` |
| `GET` | `/api/v1/transfers` | `auth:sanctum` + `X-Company-Id` | `transfers.view` |
| `POST` | `/api/v1/transfers` | `auth:sanctum` + `X-Company-Id` | `transfers.manage` |
| `GET` | `/api/v1/transfers/{id}` | `auth:sanctum` + `X-Company-Id` | `transfers.view` |
| `GET` | `/api/v1/metrics/overview` | `auth:sanctum` + `X-Company-Id` | `metrics.view` |
| `GET` | `/api/v1/metrics/stock-detail` | `auth:sanctum` + `X-Company-Id` | `metrics.view` |
| `GET` | `/api/v1/alerts` | `auth:sanctum` + `X-Company-Id` | `alerts.view` |
| `PATCH` | `/api/v1/alerts/{id}/status` | `auth:sanctum` + `X-Company-Id` | `alerts.manage` |
| `GET` | `/api/v1/alert-rules` | `auth:sanctum` + `X-Company-Id` | `alerts.view` |
| `POST` | `/api/v1/alert-rules` | `auth:sanctum` + `X-Company-Id` | `alerts.manage` |
| `PATCH` | `/api/v1/alert-rules/{id}` | `auth:sanctum` + `X-Company-Id` | `alerts.manage` |

**Propuesto para sprints siguientes:** conteos físicos, auditoría integral y chatbot.

### 2.1 Autenticación social — propuesta v1.1 (no implementada)

> **Estado:** contrato implementado en el incremento v1.1 (2026-11-15) con
> proveedores simulados en pruebas. La validación contra Google/Meta reales
> queda pendiente de credenciales. Ver ADR-0004/0005 y
> [`AUTH-01`](../SEGURIDAD/AUTH-01_AUTENTICACION_SOCIAL.md).

**Flujo.** El inicio y el callback OAuth viven en rutas `web` para disponer de
sesión y `state`; después Laravel crea la sesión Sanctum existente y redirige a
React. Las rutas de gestión de métodos viven en `/api/v1` y usan CSRF.

| Método | Ruta propuesta | Ámbito | Propósito |
| --- | --- | --- | --- |
| `GET` | `/auth/social/{provider}/redirect` | `web` | Iniciar OAuth; `provider` en `google,facebook`; genera `state` |
| `GET` | `/auth/social/{provider}/callback` | `web` | Validar `state` y respuesta; crear sesión o volver con error controlado |
| `GET` | `/auth/social/{provider}/link` | `web` autenticada | Iniciar vinculación desde una sesión válida |
| `GET` | `/api/v1/auth/social/providers` | API pública | Listar proveedores configurados para el login |
| `GET` | `/api/v1/auth/social/accounts` | API autenticada | Listar métodos vinculados, sin secretos |
| `DELETE` | `/api/v1/auth/social/{provider}` | API autenticada + CSRF | Desvincular sin dejar al usuario sin acceso |

Reglas de contrato:

- No se acepta `provider_user_id` ni correo enviados por React como prueba de
  identidad.
- El callback no devuelve tokens; redirige a una ruta permitida de la SPA.
- La identidad se resuelve por `(provider, provider_user_id)`, nunca por email.
- El login social no otorga empresa, rol ni permisos.
- Los errores no revelan si un correo o una identidad ajena existe.

**Errores propuestos** (mantienen el contrato `error.code`/`message`):

| Código | HTTP | Situación |
| --- | --- | --- |
| `SOCIAL_AUTH_FAILED` | 401 | `state` inválido, callback manipulado o proveedor rechazó |
| `SOCIAL_IDENTITY_NOT_LINKED` | 403 | Identidad válida sin vínculo interno; ofrecer invitación/vinculación |
| `SOCIAL_IDENTITY_CONFLICT` | 409 | Identidad ya vinculada a otro usuario |
| `LAST_AUTH_METHOD` | 409 | La desvinculación dejaría al usuario sin acceso |
| `SOCIAL_PROVIDER_UNAVAILABLE` | 503 | Proveedor no disponible; permitir login local |

**Estados UX propuestos:** acceso completado, acceso cancelado, proveedor no
disponible, cuenta no vinculada, empresa no permitida y cuenta inhabilitada.
La interfaz no muestra códigos OAuth, tokens ni mensajes internos.

> El contrato de éxito sigue siendo `data` + `meta` cuando corresponda; **no se
> adopta** la envoltura `success: true` de los documentos de entrada.

## 3. Recursos previstos

| Dominio | Recurso | Operaciones |
| --- | --- | --- |
| Identidad | `/api/v1/auth/login`, `/logout`, `/me` | Iniciar/cerrar sesión, perfil |
| Identidad | `/api/v1/users`, `/roles`, `/permissions` | CRUD controlado y asignaciones |
| Catálogo | `/api/v1/categories`, `/brands`, `/products`, `/variants` | CRUD y consulta |
| Unidades | `/api/v1/units`, `/unit-conversions` | CRUD y conversión |
| Ubicaciones | `/api/v1/locations` | CRUD jerárquico |
| Inventario | `/api/v1/inventory/stocks` | Consulta de saldos |
| Inventario | `/api/v1/inventory/movements` | Alta, consulta y reversión |
| Compras | `/api/v1/suppliers`, `/purchases`, `/purchases/{id}/receive` | CRUD y recepción |
| Traslados | `/api/v1/transfers` | Alta y ejecución atómica |
| Métricas | `/api/v1/metrics/*`, `/stock-alerts` | Indicadores y alertas |
| Conteos | `/api/v1/physical-counts`, `/{id}/close`, `/{id}/approve` | Conteo y aprobación |
| Auditoría | `/api/v1/audit-logs` | Consulta filtrada |
| Chatbot | `/api/v1/assistant/query` | Solo lectura |

## 4. Autenticación y contexto de empresa

1. La SPA inicia sesión contra `/api/v1/auth/login` y recibe la cookie de
   sesión de Sanctum (ADR-0001).
2. La empresa activa se envía en la cabecera `X-Company-Id` en cada petición
   de tenant.
3. El backend valida una pertenencia activa en `company_user` y el estado de
   la empresa; si no cumple, responde `403 FORBIDDEN_TENANT`.
4. Los recursos se filtran siempre por `company_id`.
5. El `company_id` enviado en el cuerpo se ignora.

La SPA envía la cookie de sesión y el token CSRF (`X-XSRF-TOKEN`). El token
Bearer queda disponible para integraciones futuras (app móvil u otro dominio).

La autorización se resuelve con `Gate` (códigos `modulo.accion`), `Policy` por
recurso y el middleware `can:` en las rutas. La interfaz solo oculta acciones;
el backend siempre vuelve a validar.

```http
Accept: application/json
X-Company-Id: 12
```

## 5. Contrato de error

```json
{
  "error": {
    "code": "INSUFFICIENT_STOCK",
    "message": "Existencias insuficientes para completar la salida",
    "details": {
      "variant_id": 45,
      "available": "3.000",
      "requested": "5.000"
    },
    "trace_id": "01J8Z9K3"
  }
}
```

| Campo | Tipo | Descripción |
| --- | --- | --- |
| `code` | string | Código estable en mayúsculas |
| `message` | string | Mensaje en español para el usuario |
| `details` | object/array | Datos de validación o contexto |
| `trace_id` | string | Identificador para soporte y logs |

### Códigos HTTP

| Código | Uso |
| --- | --- |
| 200 | Consulta o actualización exitosa |
| 201 | Recurso creado |
| 204 | Operación sin contenido (logout, borrado lógico) |
| 400 | Solicitud mal formada |
| 401 | No autenticado |
| 403 | Sin permiso o empresa distinta |
| 404 | Recurso inexistente o fuera del tenant |
| 409 | Conflicto de estado o concurrencia |
| 422 | Validación de campos |
| 429 | Demasiadas solicitudes |
| 500 | Error interno (sin detalles sensibles) |

Códigos de error usados por identidad: `VALIDATION_ERROR`, `UNAUTHENTICATED`,
`INVALID_CREDENTIALS`, `ACCOUNT_INACTIVE`, `FORBIDDEN`, `FORBIDDEN_TENANT` y
`NOT_FOUND`. Un recurso de otra empresa responde `404 NOT_FOUND` para no
revelar su existencia.

## 6. Listados, filtros y paginación

```http
GET /api/v1/products?page=2&per_page=25&sort=-name&filter[status]=active
```

```json
{
  "data": [
    { "id": 1, "name": "Producto A", "status": "active" }
  ],
  "meta": {
    "current_page": 2,
    "last_page": 8,
    "per_page": 25,
    "total": 187
  },
  "links": {
    "prev": "/api/v1/products?page=1",
    "next": "/api/v1/products?page=3"
  }
}
```

Reglas:

- `per_page` máximo 100 (valor por defecto 25).
- Ordenación permitida por lista blanca de columnas.
- Los filtros no permiten atravesar el ámbito de empresa.

## 7. Idempotencia y concurrencia

- Las operaciones críticas de inventario aceptan `Idempotency-Key`.
- La creación de movimientos usa transacciones y bloqueo de filas.
- Una salida sin stock responde `422 INSUFFICIENT_STOCK` y no altera saldos.
- Las reversiones son idempotentes: repetir la solicitud no duplica el efecto.

## 8. Ejemplos de contratos

### 8.1 Registrar entrada de inventario (propuesto)

```http
POST /api/v1/stock-movements
```

```json
{
  "type": "purchase_entry",
  "variant_id": 45,
  "location_id": 3,
  "quantity": "10.000",
  "unit_cost": "12.5000",
  "reference_type": "purchase",
  "reference_id": 88,
  "reason": "Recepción de compra"
}
```

Respuesta `201`:

```json
{
  "data": {
    "id": 901,
    "type": "purchase_entry",
    "variant_id": 45,
    "location_id": 3,
    "quantity": "10.000",
    "created_at": "2026-11-13T10:00:00Z"
  }
}
```

### 8.2 Consultar saldo (propuesto)

```http
GET /api/v1/stock-balances?variant_id=45&location_id=3
```

```json
{
  "data": [
    {
      "variant_id": 45,
      "location_id": 3,
      "quantity": "10.000",
      "reserved_quantity": "0.000"
    }
  ]
}
```

## 9. Seguridad

- HTTPS obligatorio fuera de local.
- Tokens con expiración y revocación.
- Rate limiting por usuario/IP.
- Validación de pertenencia empresarial en cada recurso.
- No registrar tokens, contraseñas ni datos sensibles en logs.
- El chatbot solo expone endpoints de lectura y respeta permisos.

## 10. Pendientes

| Tema | Estado |
| --- | --- |
| Especificación OpenAPI 3.1 | Pendiente (Sprint 1) |
| Versionado `/api/v1` | Propuesto; se adopta con US-0101 |
| Contratos de compras, conteos y chatbot | Pendientes por sprint |
| Pruebas de contrato automatizadas | Pendiente |

## 11. Documentos relacionados

- `DOCUMENTACION/SDD/SDD-01_ARQUITECTURA_GENERAL.md`
- `DOCUMENTACION/SDD/SDD-04_DISENO_BASE_DATOS.md`
- `Reglas/ESTANDAR.md` §4.5 (API) y §6.8 (errores)
- `DOCUMENTACION/SDD/ADR/REGISTRO_DECISIONES_SPRINT_0.md`
