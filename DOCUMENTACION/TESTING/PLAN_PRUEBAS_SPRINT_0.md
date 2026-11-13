# Plan y evidencias de pruebas — Sprint 0

**Proyecto:** SaaS Inversiones R&P
**Versión:** 1.0 — Sprint 0
**Fecha:** 2026-11-12
**Responsable:** Equipo de desarrollo

---

## 1. Alcance

Validar la fundación técnica: estructura del repositorio, backend Laravel,
frontend React, conexión configurada a MySQL, entorno Docker y pipeline de CI.
El Sprint 0 no incluye pruebas funcionales de módulos de negocio.

## 2. Estrategia por nivel

| Nivel | Herramienta | Alcance | Ubicación |
| --- | --- | --- | --- |
| Unitarias backend | PHPUnit | Componentes aislados | `backend/tests/Unit/` |
| Integración backend | PHPUnit | Rutas y contratos HTTP | `backend/tests/Feature/` |
| Componentes frontend | Vitest + Testing Library | Render y comportamiento | `frontend/tests/` |
| End-to-end | Cypress | Flujo smoke sobre la SPA | `tests/e2e/` |
| Estilo | Pint / Oxlint | Convenciones de código | Backend / frontend |
| Compilación | TypeScript + Vite | Build de producción | `frontend/` |

## 3. Casos de prueba del Sprint 0

| ID | Caso | Tipo | Resultado esperado |
| --- | --- | --- | --- |
| TC-S0-01 | `GET /api/health` responde | Integración | HTTP 200, `status=ok`, nombre del servicio |
| TC-S0-02 | La aplicación Laravel responde | Integración | HTTP 200 |
| TC-S0-03 | Verificación base de PHPUnit | Unitaria | `true` es verdadero |
| TC-S0-04 | La SPA muestra el nombre del proyecto | Componente | Se renderiza «SAAS Inversiones R&P» |
| TC-S0-05 | La SPA muestra el módulo de inventario | Componente | Se renderiza el texto de inventario y almacén |
| TC-S0-06 | Smoke E2E de la página de inicio | E2E | La página contiene el nombre del proyecto |
| TC-S0-07 | Estilo del backend | Estático | Pint sin diferencias |
| TC-S0-08 | Estilo del frontend | Estático | Oxlint sin errores |
| TC-S0-09 | Compilación del frontend | Build | `dist/` generado sin errores |

## 4. Evidencias de ejecución

### 4.1 Backend — PHPUnit

```text
$ php artisan test

PASS  Tests\Unit\ExampleTest
PASS  Tests\Feature\ExampleTest
PASS  Tests\Feature\HealthTest

Tests:    3 passed (5 assertions)
Duration: ~2.4 s
```

### 4.2 Backend — Pint

```text
$ vendor/bin/pint --test
{"tool":"pint","result":"pass"}
```

### 4.3 Frontend — Vitest

```text
$ npm run test

Test Files  1 passed (1)
     Tests  1 passed (1)
```

### 4.4 Frontend — Oxlint

```text
$ npm run lint
(no output — sin errores)
```

### 4.5 Frontend — Build

```text
$ npm run build

✓ 25 modules transformed.
dist/index.html                   0.59 kB
dist/assets/index-*.css           1.51 kB
dist/assets/index-*.js          259.71 kB
✓ built in ~4.4 s
```

### 4.6 Cypress — verificación del binario y smoke E2E

```text
$ npx cypress verify
✔ Verified Cypress! C:\Users\...\Cypress\Cache\16.3.0\Cypress

$ npm run e2e
  Aplicación
    √ muestra la página de inicio (6375ms)

  1 passing (7s)
  ✔ All specs passed!
```

La ejecución se realizó con Vite levantado en `http://127.0.0.1:5173`.

## 5. Criterios de aceptación del Sprint 0

| Historia | Criterio | Evidencia | Estado |
| --- | --- | --- | --- |
| US-0001 | React separado de Laravel y API REST | `SDD-01`, estructura de carpetas | Cumplido |
| US-0001 | Backend modular y MySQL como persistencia | `backend/app/Modules`, `.env.example` | Cumplido |
| US-0001 | Convenciones de ramas, commits y versionado | `SDD-09` | Cumplido |
| US-0002 | Entidades y relaciones revisadas | `SDD-04` | Cumplido (propuesta) |
| US-0002 | Estrategia de `company_id` definida | `SDD-04` §2 | Cumplido (propuesta) |
| US-0002 | Restricciones, claves e índices documentados | `SDD-04` §5 | Cumplido |
| US-0003 | Docker inicia los servicios | Archivos Docker creados | Configurado, no validado en equipo sin Docker |
| US-0003 | GitHub Actions ejecuta instalación y pruebas | `.github/workflows/ci.yml` | Configurado |
| US-0003 | Configuración local/test/producción separada | `.env.example`, `phpunit.xml` | Cumplido |
| US-0003 | Plantilla de entorno y guía de instalación | `.env.example`, `MANUAL_INSTALACION.md` | Cumplido |
| US-0004 | Cada decisión con responsable, fecha y evidencia | `ADR/REGISTRO_DECISIONES_SPRINT_0.md` | Documentado, pendiente de aprobación |
| US-0004 | Decisiones no resueltas marcadas como bloqueantes | `ADR/REGISTRO_DECISIONES_SPRINT_0.md` §4 | Cumplido |

## 6. Riesgos y limitaciones

| Riesgo | Impacto | Acción |
| --- | --- | --- |
| Docker no instalado en el equipo de desarrollo | No se validó el stack completo | Instalar Docker Desktop o usar XAMPP (ver manual) |
| MySQL no disponible localmente | No se ejecutaron migraciones reales | Configurar MySQL y ejecutar `php artisan migrate` |
| E2E no ejecutado en CI | Cobertura E2E limitada | Smoke validado localmente; incorporar Cypress al pipeline en Sprint 2–3 |
| Decisiones DP/DD pendientes | Bloquean historias futuras | Aprobación del Product Owner |

## 7. Próximos pasos

1. Ejecutar `docker compose up --build` en un equipo con Docker y registrar evidencia.
2. Ejecutar `php artisan migrate` contra MySQL real.
3. Levantar frontend y backend, y ejecutar `npm run e2e` (smoke validado: 1 prueba).
4. Incorporar el umbral de cobertura definido en `Reglas/ESTANDAR.md` §8.3.
