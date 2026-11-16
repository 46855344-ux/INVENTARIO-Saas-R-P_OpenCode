# README — Incremento v1.1 «Autenticación social y experiencia»

**Proyecto:** SaaS Inversiones R&P
**Incremento:** v1.1, posterior al Sprint 5
**Fecha:** 2026-11-15
**Estado:** Implementado técnicamente — validación con Google/Meta reales pendiente
**Plan:** [`SDD/PLAN_INCREMENTO_V1_1_AUTH_CX.md`](./SDD/PLAN_INCREMENTO_V1_1_AUTH_CX.md)
**Diseño de seguridad:** [`SEGURIDAD/AUTH-01_AUTENTICACION_SOCIAL.md`](./SEGURIDAD/AUTH-01_AUTENTICACION_SOCIAL.md)

---

## 1. Resumen

Se incorporaron Google y Facebook como métodos de acceso para usuarios internos
existentes, manteniendo el login local y la sesión Sanctum. La identidad social
solo autentica: la empresa, el rol y los permisos siguen resolviéndose en
`company_user`, `roles` y `permissions`.

**Decisiones ratificadas:** solo invitación (sin autorregistro), una identidad
por proveedor y usuario, y ejecución antes del Sprint 6.

## 2. Historias cubiertas

| Historia | Resultado |
| --- | --- |
| US-0111 | Login con Google para identidad vinculada |
| US-0112 | Login con Facebook para identidad vinculada |
| US-0113 | Vincular/desvincular desde «Mi cuenta» |
| US-0114 | `state`, callbacks, rate limit, secretos y sesión Sanctum |
| US-0115 | Login accesible, estados y mensajes seguros |

## 3. Entregables

| Entregable | Ubicación |
| --- | --- |
| Dependencia Socialite 5.32 | `backend/composer.json` |
| Migración de identidades | `backend/database/migrations/2026_11_15_090000_create_social_identities_table.php` |
| Modelo, servicio, controladores y recursos | `backend/app/Modules/Identity/` |
| Rutas web OAuth y API de cuentas | `backend/routes/web.php`, `backend/routes/api.php` |
| Configuración por ambiente | `backend/config/services.php`, `backend/.env.example` |
| Login con botones sociales | `frontend/src/features/auth/LoginPage.tsx` |
| Página «Mi cuenta» | `frontend/src/features/account/AccountPage.tsx` |
| Servicio y tipos | `frontend/src/services/social.ts`, `frontend/src/types/index.ts` |
| Pruebas | `backend/tests/Feature/Auth/SocialAuthenticationTest.php`, `frontend/tests/` |

## 4. Contratos

| Método | Ruta | Uso |
| --- | --- | --- |
| `GET` | `/auth/social/{provider}/redirect` | Iniciar login OAuth |
| `GET` | `/auth/social/{provider}/callback` | Validar retorno y crear sesión |
| `GET` | `/auth/social/{provider}/link` | Iniciar vinculación autenticada |
| `GET` | `/api/v1/auth/social/providers` | Proveedores configurados |
| `GET` | `/api/v1/auth/social/accounts` | Identidades del usuario |
| `DELETE` | `/api/v1/auth/social/{provider}` | Desvincular |

Errores: `SOCIAL_AUTH_FAILED` (401), `SOCIAL_IDENTITY_NOT_LINKED` (403),
`SOCIAL_IDENTITY_CONFLICT` (409), `LAST_AUTH_METHOD` (409). Los mensajes no
revelan si un correo o una cuenta ajena existe.

## 5. Evidencia

| Verificación | Resultado |
| --- | --- |
| Pruebas backend | 140 pruebas, 523 aserciones — OK |
| Pint | Sin diferencias — OK |
| Pruebas frontend | 65 pruebas — OK |
| Lint frontend | Sin errores — OK |
| Build frontend | Compilado — OK |
| Migración demo | `social_identities` aplicada — OK |

Las pruebas de OAuth usan proveedores simulados; no se usaron credenciales
reales ni se guardaron secretos en el repositorio.

## 6. Configuración para probar con proveedores reales

1. Crear aplicaciones OAuth en Google Cloud y Meta for Developers.
2. Registrar el callback:
   - `http://localhost:8000/auth/social/google/callback`
   - `http://localhost:8000/auth/social/facebook/callback`
3. Completar en `backend/.env`:
   ```env
   GOOGLE_CLIENT_ID=...
   GOOGLE_CLIENT_SECRET=...
   FACEBOOK_CLIENT_ID=...
   FACEBOOK_CLIENT_SECRET=...
   ```
4. Ejecutar `php artisan config:clear` y reiniciar el backend.
5. El botón del proveedor aparece solo si sus credenciales están configuradas.

## 7. Guion de demostración

1. Ingresar con `admin@empresa-demo.test` / `password`.
2. Abrir **Mi cuenta** y verificar el método local.
3. Con proveedores configurados, pulsar **Vincular** en Google.
4. Autorizar, volver a **Mi cuenta** y ver la identidad vinculada.
5. Cerrar sesión e ingresar con **Continuar con Google**.
6. Verificar que la empresa, el rol y los permisos son los mismos.
7. Desvincular Google desde **Mi cuenta** y confirmar que el login local sigue disponible.

Sin credenciales configuradas, los botones sociales se ocultan y la aplicación
funciona normalmente con correo y contraseña.

## 8. Pendientes

| Pendiente | Motivo |
| --- | --- |
| Validar OAuth con Google/Meta reales | Faltan aplicaciones y credenciales de desarrollo |
| Sistema de diseño completo | Se implementaron login y cuenta; quedan componentes transversales |
| Recuperación de contraseña | Fuera del alcance de este incremento |
| Analítica CX | Pendiente de política de privacidad y metas |
| Pruebas E2E de OAuth | Requieren proveedor real o simulador de navegador |

## 9. Commits del incremento

```text
cc8efb2 docs(incremento): planificar autenticacion social y experiencia v1.1
7f446ce feat(auth): autenticacion social con Google y Facebook (v1.1)
88f73ce docs(incremento): cerrar autenticacion social y experiencia v1.1
cb0e4b5 chore(deps): actualizar lock de Composer
487fc2c chore(test): estabilizar workers de Vitest
```
