# CI/CD — Pipeline de integración continua

**Proyecto:** SaaS Inversiones R&P
**Versión:** 1.0 — Sprint 0
**Archivo:** `.github/workflows/ci.yml`

---

## 1. Objetivo

Validar automáticamente cada cambio en `main` y `develop`, y en cada *pull
request*, mediante instalación de dependencias, análisis de estilo, pruebas y
compilación.

## 2. Disparadores

| Evento | Ramas |
| --- | --- |
| `push` | `main`, `develop` |
| `pull_request` | `main`, `develop` |

Se usa `concurrency` para cancelar ejecuciones anteriores de la misma rama.

## 3. Trabajos

### 3.1 Backend (Laravel)

| Paso | Acción |
| --- | --- |
| PHP | `shivammathur/setup-php@v2` con PHP 8.3 y extensiones del proyecto |
| Caché | Caché de Composer por `backend/composer.lock` |
| Dependencias | `composer install --no-interaction --prefer-dist --optimize-autoloader` |
| Entorno | `cp .env.example .env` + `php artisan key:generate` |
| Estilo | `vendor/bin/pint --test` |
| Pruebas | `php artisan test` (SQLite en memoria) |

## 3.2 Frontend (React)

| Paso | Acción |
| --- | --- |
| Node.js | `actions/setup-node@v4` con Node 22 y caché npm |
| Dependencias | `npm ci` |
| Lint | `npm run lint` |
| Pruebas | `npm run test` |
| Compilación | `npm run build` |

### 3.3 Seguridad de dependencias

| Paso | Acción |
| --- | --- |
| Backend | `composer install` y `composer audit --format=summary` |
| Frontend | `npm ci` y `npm audit --omit=dev --audit-level=high` |
| Resultado | Falla el job ante vulnerabilidades altas/críticas |

Ver [`GUIA_PRODUCCION.md`](./GUIA_PRODUCCION.md) y
[`../TESTING/ACEPTACION_MVP.md`](../TESTING/ACEPTACION_MVP.md).

## 4. Criterios de éxito

Un cambio se considera válido cuando:

1. El backend instala dependencias sin errores.
2. Pint no detecta problemas de estilo.
3. Todas las pruebas PHPUnit pasan.
4. El frontend compila con TypeScript sin errores.
5. Oxlint no reporta problemas.
6. Todas las pruebas Vitest pasan.
7. `composer audit` y `npm audit --omit=dev --audit-level=high` no reportan vulnerabilidades altas o críticas.

## 5. Ejecución local equivalente

```bash
# Backend
cd backend
composer install
vendor/bin/pint --test
php artisan test

# Frontend
cd frontend
npm ci
npm run lint
npm run test
npm run build
```

## 6. Próximas incorporaciones

| Mejora | Sprint | Estado |
| --- | --- | --- |
| SonarQube / SonarCloud | 1–2 | Pendiente |
| OWASP Dependency-Check | 1–2 | Pendiente |
| Pruebas Cypress en CI con servicios levantados | 2–3 | Pendiente |
| Análisis de cobertura con umbral mínimo | 2 | Pendiente |
| Despliegue automatizado a staging | 5–9 | Pendiente |

## 7. Notas

- Las pruebas de backend usan SQLite en memoria, por lo que no requieren un
  servicio MySQL en CI.
- Las credenciales de CI se gestionan con *GitHub Secrets*; no se escriben en el
  workflow.
- El pipeline no despliega en producción en el Sprint 0.

## 8. Actualización v1.1 — autenticación social (propuesta)

> **Estado:** recomendaciones documentales. No se modificó el pipeline ni se
> agregaron dependencias al 2026-11-15.

Si se aprueba el incremento de autenticación con Google/Facebook:

| Control | Implementación propuesta |
| --- | --- |
| Secretos OAuth | Solo en GitHub Secrets/variables del entorno; nunca en el repositorio ni en el bundle React |
| Pruebas automatizadas | Mocks/fixtures de proveedores; sin credenciales reales ni llamadas externas en CI |
| Callbacks | URLs de prueba registradas por ambiente; prohibido usar producción en CI |
| Análisis de dependencias | Incorporar OWASP Dependency-Check o equivalente antes de agregar Socialite |
| Escaneo de secretos | Verificación previa al merge y en el pipeline |
| Regresión | Mantener backend, frontend, Pint, Oxlint, Vitest y build como gates obligatorios |

Ver `../SEGURIDAD/AUTH-01_AUTENTICACION_SOCIAL.md`,
`../TESTING/QA-01_PLAN_DE_PRUEBAS_CX_UX_UI_AUTH.md` y
`../SDD/ADR/REGISTRO_DECISIONES_V1_1_CX_UX_AUTH.md`.
