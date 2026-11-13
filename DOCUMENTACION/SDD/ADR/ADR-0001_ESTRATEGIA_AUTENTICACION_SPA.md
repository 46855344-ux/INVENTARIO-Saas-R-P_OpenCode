# ADR-0001 — Estrategia de autenticación para la SPA

**Proyecto:** SaaS Inversiones R&P
**Estado:** Aprobada — implementada en US-0101
**Fecha:** 2026-11-13
**Sprint:** 1 (EP-01)
**Historias afectadas:** US-0101, US-0102, US-0103, US-0104
**Documentos relacionados:** `SDD-05_DISENO_API.md`, `PLAN_SPRINT_1_IDENTIDAD_MULTIEMPRESA.md`

---

## 1. Contexto

El frontend es una SPA React que consume una API Laravel. Laravel Sanctum 4.4 ya
está instalado desde el Sprint 0. El canvas de Sprint 1 exige «definir el flujo
CSRF/cookies o token según despliegue» y controlar el riesgo de «gestión insegura
de sesión SPA».

## 2. Alternativas evaluadas

| Alternativa | Descripción | Ventajas | Desventajas |
| --- | --- | --- | --- |
| **A. Cookie de SPA (Sanctum stateful)** | Cookies `HttpOnly` + CSRF (`XSRF-TOKEN`); `statefulApi()` | Token no expuesto a JavaScript; protección CSRF; sesión revocable en servidor | Requiere dominio compartido o configurar CORS/`SANCTUM_STATEFUL_DOMAINS` |
| **B. Token Bearer (personal access token)** | El cliente guarda el token y lo envía en `Authorization` | Simple; funciona entre dominios y apps móviles | Mayor exposición si se guarda en `localStorage`; revocación manual |
| **C. Sesión clásica con Blade** | Sesión de servidor con vistas Blade | Sencillo | No aplica: el frontend es una SPA React separada |

## 3. Decisión propuesta

**Adoptar la alternativa A — Cookie de SPA con Laravel Sanctum** como modo
principal, manteniendo la **alternativa B** disponible para integraciones futuras
(app móvil o frontend en otro dominio).

Condiciones:

1. Frontend y backend en el mismo sitio o con `SANCTUM_STATEFUL_DOMAINS` configurado.
2. Cookies `HttpOnly`, `Secure` en producción y `SameSite=Lax`.
3. Protección CSRF activa (`VerifyCsrfToken`) para peticiones con cookies.
4. `POST /api/v1/auth/logout` invalida la sesión en el servidor.

## 4. Consecuencias

- **Positivas:** el token nunca se expone a JavaScript; CSRF cubierto; revocación centralizada.
- **Negativas:** la configuración de CORS/dominios debe cuidarse en despliegue.
- **Neutras:** el contrato de la API (`SDD-05`) no cambia; solo el mecanismo de credenciales.

## 5. Criterios de aceptación de la decisión

- [x] El Líder técnico aprueba la alternativa A como modo principal.
- [x] Se documenta la configuración de `SANCTUM_STATEFUL_DOMAINS` y CORS.
- [x] El flujo de logout invalida la sesión y las pruebas T-03/T-04 pasan.

## 6. Estado

Aprobada el **2026-11-13** e implementada en **US-0101**:

- `statefulApi()` en `backend/bootstrap/app.php`.
- Cookie CSRF en `<origen>/sanctum/csrf-cookie` y cabecera `X-XSRF-TOKEN`.
- CORS con credenciales y `SANCTUM_STATEFUL_DOMAINS` para `http://localhost:5173`.
- `POST /api/v1/auth/logout` invalida la sesión y rota el token CSRF.
- Pruebas `T-01` a `T-04` cubiertas en `tests/Feature/Auth/AuthenticationTest.php`.
