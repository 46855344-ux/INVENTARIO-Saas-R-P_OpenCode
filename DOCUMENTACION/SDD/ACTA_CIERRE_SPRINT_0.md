# Acta de cierre — Sprint 0 «Fundación técnica»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-00 — Fundación técnica y decisiones
**Periodo:** Sprint 0
**Fecha de cierre técnico:** 2026-11-12
**Estado:** Cerrado técnicamente — US-0004 pendiente de aprobación del Product Owner

---

## 1. Objetivo del sprint

Dejar un esqueleto ejecutable, reproducible y documentado, con las decisiones
estructurales registradas, sin desarrollar prematuramente los módulos de negocio.

## 2. Entregables

| Entregable | Ubicación | Estado |
| --- | --- | --- |
| Estructura del repositorio | Raíz del proyecto | Completo |
| Backend Laravel 13 + Sanctum | `backend/` | Completo |
| Frontend React 19 + Vite + TypeScript | `frontend/` | Completo |
| Pruebas E2E Cypress | `tests/` | Configurado |
| Docker (backend, frontend, MySQL) | `infrastructure/docker/`, `docker-compose.yml` | Configurado, no validado sin Docker |
| Apache / XAMPP | `infrastructure/apache/`, `infrastructure/xampp/` | Documentado |
| Pipeline CI | `.github/workflows/ci.yml` | Configurado |
| Plantilla de entorno | `.env.example` | Completo |
| Manual de instalación | `DOCUMENTACION/MANUALES/MANUAL_INSTALACION.md` | Completo |
| Arquitectura general | `DOCUMENTACION/SDD/SDD-01_ARQUITECTURA_GENERAL.md` | Completo |
| Contratos de API | `DOCUMENTACION/SDD/SDD-05_DISENO_API.md` | Completo |
| Modelo de datos y multiempresa | `DOCUMENTACION/SDD/SDD-04_DISENO_BASE_DATOS.md` | Propuesta |
| DER lógico | `database/diagrams/DER_LOGICO.md` | Propuesta |
| Diccionario de datos | `database/dictionaries/DICCIONARIO_DATOS.md` | Propuesta |
| Estructura y convenciones | `DOCUMENTACION/SDD/SDD-09_ESTRUCTURA_REPOSITORIO.md` | Completo |
| Registro de decisiones | `DOCUMENTACION/SDD/ADR/REGISTRO_DECISIONES_SPRINT_0.md` | Pendiente de aprobación |
| Plan y evidencias de prueba | `DOCUMENTACION/TESTING/PLAN_PRUEBAS_SPRINT_0.md` | Completo |
| README de cierre del sprint | `DOCUMENTACION/README_SPRINT_0.md` | Completo |

## 3. Cumplimiento por historia

### US-0001 — Arquitectura y estructura del repositorio

| Criterio | Evidencia | Estado |
| --- | --- | --- |
| React separado de Laravel por API REST/JSON | `SDD-01`, `frontend/`, `backend/` | Cumplido |
| Backend modular monolítico y MySQL | `app/Modules/*`, `.env.example` | Cumplido |
| Convenciones de ramas, commits y versionado | `SDD-09` §3–4 | Cumplido |
| Proyecto base Laravel y React | `backend/composer.json`, `frontend/package.json` | Cumplido |
| Configuración por ambiente sin secretos | `.env.example`, `.gitignore` | Cumplido |

### US-0002 — Modelo de datos y estrategia multiempresa

| Criterio | Evidencia | Estado |
| --- | --- | --- |
| Entidades y relaciones revisadas | `SDD-04` §3; `database/diagrams/DER_LOGICO.md` | Cumplido (propuesta) |
| Representación de `company_id` | `SDD-04` §2; `DER_LOGICO.md` §1 | Cumplido (propuesta) |
| Restricciones, claves e índices | `SDD-04` §5; `database/dictionaries/DICCIONARIO_DATOS.md` | Cumplido |
| Estrategia de aislamiento y pruebas | `SDD-04` §2 y §7 | Cumplido |

### US-0003 — Entorno local y pipeline inicial

| Criterio | Evidencia | Estado |
| --- | --- | --- |
| Docker inicia los servicios locales | `docker-compose.yml`, `infrastructure/docker/*` | Configurado; requiere Docker |
| GitHub Actions ejecuta instalación y pruebas | `.github/workflows/ci.yml` | Configurado |
| Configuración local/test/producción separada | `.env.example`, `phpunit.xml` | Cumplido |
| Plantilla de variables y guía de instalación | `.env.example`, manual | Cumplido |

### US-0004 — Decisiones de negocio

| Criterio | Evidencia | Estado |
| --- | --- | --- |
| Cada decisión con responsable, decisión, fecha y evidencia | Registro DP-01..DP-10 y DD-01..DD-05 | Documentado |
| Decisiones no resueltas marcadas como bloqueantes | Registro §4 | Cumplido |
| Modelo, reglas y criterios actualizados | `SDD-04` | Propuesta |

## 4. Verificación técnica ejecutada

| Verificación | Comando | Resultado |
| --- | --- | --- |
| Pruebas backend | `php artisan test` | 3 pruebas, 5 aserciones — OK |
| Estilo backend | `vendor/bin/pint --test` | Sin diferencias — OK |
| Pruebas frontend | `npm run test` | 1 prueba — OK |
| Lint frontend | `npm run lint` | Sin errores — OK |
| Build frontend | `npm run build` | Compilado — OK |
| Binario Cypress | `npx cypress verify` | Verificado — OK |
| Smoke E2E | `npm run e2e` | 1 prueba — OK |

## 5. Pendientes y bloqueos

| Pendiente | Motivo | Responsable | Próximo paso |
| --- | --- | --- | --- |
| Validar `docker compose up --build` | Docker no instalado en el equipo | Desarrollo | Instalar Docker Desktop |
| Ejecutar migraciones contra MySQL real | MySQL no instalado localmente | Desarrollo | Instalar MySQL o usar Docker |
| Ejecutar E2E completo | Requiere frontend levantado | Desarrollo | Validado localmente con `npm run e2e` (1 prueba) |
| Aprobar DP-01..DP-10 y DD-01..DD-05 | Decisiones de negocio | Product Owner | DD-01 y DD-04 aprobadas (2026-11-13); el resto sigue pendiente |
| SonarQube y OWASP Dependency-Check | Fuera del alcance del Sprint 0 | DevSecOps | Incorporar en Sprint 1–2 |

## 6. Riesgos abiertos

1. **Aislamiento multiempresa:** el diseño está documentado, pero aún no hay
   pruebas reales de separación entre empresas. Se implementarán en el Sprint 1.
2. **Concurrencia de inventario:** requiere transacciones y bloqueos; se
   implementará en el Sprint 4.
3. **Decisiones de negocio pendientes:** bloquean historias de los sprints 1, 4,
   6 y 7.

## 7. Próximo sprint (Sprint 1 — EP-01)

Historias previstas:

- US-0101 — Iniciar y cerrar sesión.
- US-0102 — Gestionar usuarios y roles por empresa.
- US-0103 — Controlar permisos por rol.

Condiciones de entrada:

1. Aprobar DD-01 y DD-04. _(Cumplido el 2026-11-13.)_
2. Contar con MySQL disponible (Docker o local).
3. Ejecutar las migraciones base de `companies`, `users` y `company_user`.

## 8. Conclusión

El Sprint 0 queda **cerrado técnicamente**: el repositorio, los proyectos base,
la infraestructura configurada, el pipeline y la documentación están disponibles.
La aprobación de las decisiones de negocio (US-0004) queda en manos del Product
Owner y es requisito para iniciar el Sprint 1 sin retrabajo.

## 9. Anexo — avance del Sprint 1 (2026-11-13)

Con las aprobaciones de DD-01, DD-04 y los ADR-0001/0002/0003, se implementaron
**US-0101 — Iniciar y cerrar sesión** y **US-0102 — Administrar usuarios y asignar roles**.

| Verificación | Comando | Resultado |
| --- | --- | --- |
| Pruebas backend | `php artisan test` | 27 pruebas, 96 aserciones — OK |
| Estilo backend | `vendor/bin/pint --test` | Sin diferencias — OK |
| Pruebas frontend | `npm run test` | 15 pruebas — OK |
| Lint frontend | `npm run lint` | Sin errores — OK |
| Build frontend | `npm run build` | Compilado — OK |
| Smoke E2E | `npx cypress run` | 1 prueba — OK |

Entregables: autenticación SPA con Sanctum; esquema y API de empresas, usuarios,
roles y permisos; `TenantContext` con `X-Company-Id`; pantallas React de acceso y
gestión de usuarios. La validación con Docker y MySQL real sigue pendiente por no
estar instalados en el equipo.
