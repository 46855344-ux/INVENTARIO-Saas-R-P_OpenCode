# Plan — Incremento v1.1 «Autenticación social y experiencia»

**Proyecto:** SaaS Inversiones R&P
**Documento:** Plan del incremento posterior al Sprint 5
**Versión:** 1.0
**Fecha:** 2026-11-15
**Estado:** Planificado — implementación bloqueada hasta ratificar PD-01 a PD-07
**Rama prevista:** `feat/incremento-v1-1-auth` (se creará al iniciar código)
**Referencias:** `INTEGRACION_V1_1_CX_UX_AUTH.md`, `REGISTRO_DECISIONES_V1_1_CX_UX_AUTH.md`, `SEGURIDAD/AUTH-01`, `TESTING/QA-01`

---

## 1. Objetivo

Incorporar Google y Facebook como métodos de acceso y aplicar los criterios de
CX/UX/UI compatibles con la arquitectura actual, sin reabrir los Sprints 0–5 ni
alterar las reglas de inventario, permisos o aislamiento multiempresa.

**Incremento demostrable:** un usuario existente con acceso local vincula su
cuenta de Google, cierra sesión, ingresa con Google y conserva su empresa, rol y
permisos; si intenta acceder sin vínculo, recibe un mensaje seguro sin acceso al
inventario.

## 2. Alcance

### 2.1 Incluido

| Historia | Resultado esperado |
| --- | --- |
| US-0111 | Login con Google (OIDC) para identidad previamente vinculada |
| US-0112 | Login con Facebook para identidad previamente vinculada |
| US-0113 | Vincular y desvincular identidades desde una sesión válida |
| US-0114 | Protección de OAuth, sesión, callbacks y secretos |
| US-0115 | Login accesible con tres métodos, estados y errores claros |
| US-UX01/UX04/UX05 | Componentes y confirmaciones en flujos críticos, sin regresión |

### 2.2 Fuera de alcance

- Autorregistro público de empresas o usuarios.
- Social-only accounts sin contraseña local.
- Google Workspace, Microsoft, Apple u otros proveedores.
- Tokens de proveedor, sincronización de perfiles o publicación en redes.
- Analítica CX con datos personales, planes, pagos o suscripciones.
- Cambios a reglas de stock, permisos o tenencia.

## 3. Decisiones a ratificar

| ID | Decisión | Recomendación | Impacto |
| --- | --- | --- | --- |
| PD-01 | Invitación o autorregistro social | **Solo invitación**: la identidad social no crea usuario ni empresa | Alto |
| PD-02 | Momento de ejecución | **Incremento v1.1 antes del Sprint 6** | Planificación |
| PD-03 | Varias identidades del mismo proveedor | **Una por proveedor y usuario** en el MVP; `UNIQUE(user_id, provider)` | Medio |
| PD-04 | Vinculación/desvinculación | Solo con sesión local válida; nunca por email; no dejar sin método | Alto |
| PD-05 | Datos del proveedor | Guardar solo `provider`, `provider_user_id`, `linked_at`; sin email ni tokens | Medio |
| PD-06 | Sistema de diseño | Tokens/componentes incrementales sobre el CSS actual; WCAG 2.2 AA | Medio |
| PD-07 | Métricas CX | Diferir metas numéricas y analítica; medir en piloto | Bajo |

> Si el PO no ratifica PD-01/PD-03/PD-06, no se inicia la implementación.

## 4. Arquitectura propuesta

```text
React Login/Account
        │
        ├── GET /auth/social/{provider}/redirect (web, state)
        │        └── proveedor → GET /auth/social/{provider}/callback (web)
        │                 └── valida, vincula/resuelve, crea sesión Sanctum
        │
        ├── GET /api/v1/auth/social/accounts (API autenticada)
        └── DELETE /api/v1/auth/social/{provider} (API + CSRF)
```

- **Laravel Socialite** como cliente OAuth; confirmar compatibilidad con Laravel 13
  antes de instalarlo.
- Rutas de inicio/callback en `routes/web.php` para disponer de sesión y `state`.
- La sesión final es la misma de Sanctum (cookie + CSRF, ADR-0001/0004).
- React solo muestra botones/estados; no intercambia códigos ni recibe tokens.
- Configuración por ambiente en `config/services.php` y `.env`; sin secretos en Git.
- Si un proveedor no está configurado, su botón no se muestra; el login local sigue.

## 5. Modelo de datos

Migración aditiva y reversible (al implementar):

| Tabla | Campos |
| --- | --- |
| `social_identities` | `id`, `user_id`, `provider`, `provider_user_id`, `linked_at`, timestamps |

Restricciones:

- `provider` en `google,facebook`.
- `UNIQUE (provider, provider_user_id)`.
- `UNIQUE (user_id, provider)` si se ratifica PD-03.
- FK `user_id` → `users` con `cascadeOnDelete`.
- Sin `company_id`: la identidad es global; la autorización sigue en `company_user`.
- Sin tokens ni email del proveedor en el MVP.

## 6. Contratos

| Método | Ruta | Ámbito | Uso |
| --- | --- | --- | --- |
| `GET` | `/auth/social/{provider}/redirect` | `web`, público con rate limit | Iniciar login |
| `GET` | `/auth/social/{provider}/callback` | `web` | Validar retorno y crear sesión |
| `GET` | `/auth/social/{provider}/link` | `web`, autenticado | Iniciar vinculación desde cuenta |
| `GET` | `/api/v1/auth/social/accounts` | API autenticada | Listar métodos vinculados |
| `DELETE` | `/api/v1/auth/social/{provider}` | API + CSRF | Desvincular |

Ajuste respecto del borrador de SDD-05: la **vinculación** se inicia por ruta web
autenticada (`GET`) para conservar `state`; la API solo lista y desvincula. El
contrato se actualizará al implementar y probar.

## 7. Seguridad

1. `state` de un solo uso ligado a la sesión; PKCE cuando el flujo lo permita.
2. Callback registrado por ambiente; sin redirecciones abiertas.
3. HTTPS en producción; cookies `HttpOnly`, `Secure` y `SameSite`.
4. Rate limiting en login local y rutas sociales.
5. Validar identidad con el mecanismo oficial; no confiar en datos de React.
6. Prohibida la vinculación automática por email.
7. El login social no concede empresa, rol ni permisos.
8. Auditoría de eventos sin tokens, códigos OAuth ni datos personales excesivos.
9. Pruebas con proveedores simulados; sin credenciales reales en CI.
10. Secretos solo en `.env`/gestor; `.env.example` con placeholders.

## 8. UX / UI

- Login: correo/contraseña + «Continuar con Google» + «Continuar con Facebook»,
  separador «O continúa con», estados de carga y error neutro.
- Botones visibles solo si el proveedor está configurado y siempre accesibles por
  teclado; marcas conforme a las guías de Google/Meta.
- Página «Mi cuenta»: método local, identidades vinculadas, acciones vincular y
  desvincular con confirmación y explicación de consecuencias.
- Mensajes: cuenta no vinculada, proveedor no disponible, identidad ya vinculada,
  último método y acceso denegado, sin revelar datos de otras cuentas.
- Estados `loading`, `empty`, `error`, `success` y `sin permisos` en todas las
  vistas tocadas.
- Objetivo WCAG 2.2 AA; validar teclado, foco, contraste y zoom.
- Sin cambios visuales globales sin prueba de regresión.

## 9. Pruebas

### 9.1 Backend

- Callback válido con identidad vinculada → sesión y perfil correctos.
- Cancelación, `state` inválido, callback manipulado y proveedor caído.
- Identidad no vinculada → 403 neutral, sin sesión.
- Identidad ya vinculada a otro usuario → 409.
- Google/Facebook sin email → funciona por ID externo.
- Vinculación con sesión válida; sin sesión → 401/redirect.
- Desvinculación con método alternativo; último método → 409.
- Empresa/rol/permisos intactos; aislamiento A/B; usuario suspendido.
- Regresión completa de Sprints 0–5.

### 9.2 Frontend

- Botones visibles solo con configuración; ocultos sin proveedor.
- Navegación por teclado y etiquetas accesibles.
- Estados de carga/error/cancelación y mensajes neutros.
- Página de cuenta: listar, vincular y desvincular con confirmación.
- Regresión de login local, layout y pantallas de inventario/compras.

### 9.3 Evidencia

- Backend `php artisan test`, Pint.
- Frontend `npm run test`, `npm run lint`, `npm run build`.
- Matriz AUTH-T01..T14 y UX-T01..T10 de `QA-01` con resultados reales.
- Pruebas de proveedor con mocks; ninguna credencial en CI.

## 10. Fases de implementación

| Fase | Entregable |
| --- | --- |
| 0 | Ratificación PD-01/PD-03/PD-06 y creación de rama |
| 1 | Dependencia Socialite, configuración y migración `social_identities` |
| 2 | Servicio de identidad, rutas web de redirect/callback y sesión Sanctum |
| 3 | API de cuentas/desvinculación y políticas de acceso |
| 4 | Login React, página «Mi cuenta» y estados UX |
| 5 | Pruebas de seguridad, regresión, accesibilidad y documentación |
| 6 | Cierre del incremento, README y actualización de SDD/ADR |

## 11. Definition of Ready

- [ ] PD-01, PD-03 y PD-06 ratificadas.
- [ ] Credenciales de aplicaciones Google/Meta de desarrollo disponibles o mocks definidos.
- [ ] Contrato de vinculación aprobado.
- [ ] Diseño de la página «Mi cuenta» acordado.
- [ ] Regresión de Sprints 0–5 como línea base.

## 12. Definition of Done

- [ ] US-0111 a US-0115 cumplen criterios.
- [ ] Login local intacto y sesión Sanctum reutilizada.
- [ ] Identidades únicas y vinculación explícita; sin auto-link por email.
- [ ] Empresa, rol y permisos no cambian por el método de acceso.
- [ ] `state`, callbacks, cookies, rate limit y secretos verificados.
- [ ] UI accesible, responsive y con estados completos.
- [ ] Pruebas backend/frontend y regresión 0–5 en verde.
- [ ] Documentación y CHANGELOG actualizados; sin secretos.

## 13. Riesgos

| Riesgo | Mitigación |
| --- | --- |
| Proveedor mal configurado o caído | Mantener login local; feature flag por proveedor |
| Duplicación de cuentas | ID externo único y vinculación verificada |
| Regresión de auth/tenant | Pruebas de regresión y revisión de seguridad |
| Dependencia Socialite incompatible | Verificar versión antes de instalar; aislar en servicio |
| Costos/tiempo de UI | Componentes incrementales, sin rediseño global |
| Falta de credenciales reales | Pruebas con mocks; configuración documentada |

## 14. Aprobación

Este plan no modifica código ni datos. La implementación comienza cuando el
Product Owner ratifique PD-01, PD-03 y PD-06, y confirme PD-02. Las decisiones
quedarán registradas en el ADR correspondiente antes de la primera migración.
