# SDD-05 — Diseño de API REST

**Proyecto:** SaaS Inversiones R&P
**Documento:** Contratos y convenciones de la API
**Versión:** 1.0 — Sprint 0
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
| Autenticación | Laravel Sanctum (token Bearer o cookie de SPA) |

**Implementado (Sprint 0 y Sprint 1):**

| Método | Ruta | Autenticación | Permiso |
| --- | --- | --- | --- |
| `GET` | `/api/health` | Pública | — |
| `POST` | `/api/v1/auth/login` | Pública | — |
| `POST` | `/api/v1/auth/logout` | `auth:sanctum` | — |
| `GET` | `/api/v1/auth/me` | `auth:sanctum` | — |
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

**Propuesto para sprints siguientes:** unidades, ubicaciones, inventario,
compras, métricas y auditoría.

## 3. Recursos previstos

| Dominio | Recurso | Operaciones |
| --- | --- | --- |
| Identidad | `/api/v1/auth/login`, `/logout`, `/me` | Iniciar/cerrar sesión, perfil |
| Identidad | `/api/v1/users`, `/roles`, `/permissions` | CRUD controlado y asignaciones |
| Catálogo | `/api/v1/categories`, `/brands`, `/products`, `/variants` | CRUD y consulta |
| Unidades | `/api/v1/units`, `/unit-conversions` | CRUD y conversión |
| Ubicaciones | `/api/v1/locations` | CRUD jerárquico |
| Inventario | `/api/v1/stock-balances` | Consulta de saldos |
| Inventario | `/api/v1/stock-movements` | Alta, consulta y reversión |
| Compras | `/api/v1/suppliers`, `/purchases`, `/purchases/{id}/receive` | CRUD y recepción |
| Traslados | `/api/v1/transfers`, `/transfers/{id}/execute` | Alta y ejecución |
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
