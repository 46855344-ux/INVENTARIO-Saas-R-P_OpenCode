# SAAS Inversiones R&P

Sistema SaaS de **gestión de inventario y almacén**. Aplicación web con frontend
en React, API REST en Laravel y base de datos MySQL.

**Estado:** Sprint 0 — Fundación técnica completada.
Ver [`DOCUMENTACION/SDD/ACTA_CIERRE_SPRINT_0.md`](./DOCUMENTACION/SDD/ACTA_CIERRE_SPRINT_0.md).

---

## Stack

| Capa | Tecnología | Versión de referencia |
| --- | --- | --- |
| Frontend | React + TypeScript + Vite | React 19.3 · Vite 8.3 |
| Backend | Laravel (PHP) | Laravel 13.42 · PHP 8.3 |
| Autenticación API | Laravel Sanctum | 4.4 |
| Base de datos | MySQL | 8.0 |
| Pruebas frontend | Vitest + Testing Library | 5.1 |
| Pruebas backend | PHPUnit | 12.5 |
| Pruebas E2E | Cypress | 16.3 |
| Estilo | Pint (PHP) · Oxlint (TS) | — |
| Infraestructura | Docker Compose · Apache/XAMPP | — |
| CI | GitHub Actions | — |

---

## Estructura del proyecto

```text
SAAS-INVERSIONES-RP/
├── frontend/                  # Aplicación web React
│   ├── src/{app,components,features,hooks,layouts,services,styles,types,utils}
│   └── tests/                 # Vitest
├── backend/                   # API REST Laravel
│   ├── app/Modules/{Identity,Catalog,Inventory,Purchasing,Metrics,Audit}
│   └── database/migrations/   # Fuente oficial del esquema MySQL
├── database/                  # Diagramas, diccionario y scripts SQL
├── tests/                     # E2E Cypress (e2e, fixtures, support, reports)
├── infrastructure/            # Docker, Apache y XAMPP
├── DOCUMENTACION/             # SDD, TESTING, DEVSECOPS, SEGURIDAD, MANUALES
├── scripts/                   # setup, database, deployment
├── Reglas/ESTANDAR.md         # Estándar obligatorio
├── .github/workflows/ci.yml   # Integración continua
├── docker-compose.yml
└── .env.example
```

---

## Inicio rápido

### Opción A — Docker (recomendada)

Requiere Docker Desktop en ejecución.

```bash
cp .env.example .env
docker compose up --build
```

- Frontend: <http://localhost:5173>
- API: <http://localhost:8000/api/health>
- MySQL: `localhost:3306`

### Opción B — Local (PHP + Node + MySQL/XAMPP)

```bash
cp .env.example .env
cp backend/.env.example backend/.env

cd backend
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

En otra terminal:

```bash
cd frontend
npm install
npm run dev
```

Instalar Cypress en la raíz:

```bash
npm install
npx cypress verify
```

> Guía detallada, solución de problemas y credenciales locales:
> [`DOCUMENTACION/MANUALES/MANUAL_INSTALACION.md`](./DOCUMENTACION/MANUALES/MANUAL_INSTALACION.md).

---

## Scripts disponibles

### Frontend (`frontend/`)

| Comando | Descripción |
| --- | --- |
| `npm run dev` | Servidor de desarrollo Vite |
| `npm run build` | Compilación de producción |
| `npm run lint` | Análisis estático con Oxlint |
| `npm run test` | Pruebas con Vitest |
| `npm run coverage` | Cobertura de pruebas |

### Backend (`backend/`)

| Comando | Descripción |
| --- | --- |
| `php artisan serve` | Servidor de desarrollo |
| `php artisan migrate` | Ejecutar migraciones |
| `php artisan test` | Pruebas PHPUnit |
| `vendor/bin/pint` | Corregir estilo |
| `vendor/bin/pint --test` | Verificar estilo |

### Raíz del repositorio

| Comando | Descripción |
| --- | --- |
| `npm run e2e` | Cypress en modo headless |
| `npm run cypress:open` | Cypress interactivo |
| `npm run e2e:headed` | Cypress con navegador visible |
| `docker compose up --build` | Levantar el entorno completo |

### Automatización (`scripts/`)

| Script | Descripción |
| --- | --- |
| `scripts/setup/setup.ps1` | Preparación completa en Windows |
| `scripts/setup/setup.sh` | Preparación completa en Linux/macOS |
| `scripts/database/backup.ps1` | Respaldo de MySQL |
| `scripts/database/restore.ps1` | Restauración de MySQL |

---

## Documentación

| Documento | Contenido |
| --- | --- |
| [`Reglas/ESTANDAR.md`](./Reglas/ESTANDAR.md) | Estándar obligatorio: ISO, nombres, SOLID, multiempresa, pruebas |
| [`DOCUMENTACION/README_SPRINT_0.md`](./DOCUMENTACION/README_SPRINT_0.md) | README de cierre del Sprint 0 y guía de iteraciones |
| [`DOCUMENTACION/SDD/SDD-01_ARQUITECTURA_GENERAL.md`](./DOCUMENTACION/SDD/SDD-01_ARQUITECTURA_GENERAL.md) | Arquitectura general |
| [`DOCUMENTACION/SDD/SDD-04_DISENO_BASE_DATOS.md`](./DOCUMENTACION/SDD/SDD-04_DISENO_BASE_DATOS.md) | Modelo de datos y multiempresa |
| [`DOCUMENTACION/SDD/SDD-05_DISENO_API.md`](./DOCUMENTACION/SDD/SDD-05_DISENO_API.md) | Contratos y convenciones de la API |
| [`DOCUMENTACION/SDD/SDD-09_ESTRUCTURA_REPOSITORIO.md`](./DOCUMENTACION/SDD/SDD-09_ESTRUCTURA_REPOSITORIO.md) | Estructura y convenciones |
| [`DOCUMENTACION/SDD/ADR/REGISTRO_DECISIONES_SPRINT_0.md`](./DOCUMENTACION/SDD/ADR/REGISTRO_DECISIONES_SPRINT_0.md) | Decisiones DP/DD y bloqueos |
| [`DOCUMENTACION/SDD/ACTA_CIERRE_SPRINT_0.md`](./DOCUMENTACION/SDD/ACTA_CIERRE_SPRINT_0.md) | Acta de cierre del Sprint 0 |
| [`database/diagrams/DER_LOGICO.md`](./database/diagrams/DER_LOGICO.md) | Diagrama entidad-relación lógico |
| [`database/dictionaries/DICCIONARIO_DATOS.md`](./database/dictionaries/DICCIONARIO_DATOS.md) | Diccionario de datos |
| [`DOCUMENTACION/MANUALES/MANUAL_INSTALACION.md`](./DOCUMENTACION/MANUALES/MANUAL_INSTALACION.md) | Instalación paso a paso |
| [`DOCUMENTACION/DEVSECOPS/CI_CD.md`](./DOCUMENTACION/DEVSECOPS/CI_CD.md) | Pipeline de integración continua |
| [`DOCUMENTACION/TESTING/PLAN_PRUEBAS_SPRINT_0.md`](./DOCUMENTACION/TESTING/PLAN_PRUEBAS_SPRINT_0.md) | Plan y evidencias de prueba |

---

## Convenciones clave

- **Interfaz y textos visibles:** español.
- **Código, tablas y columnas:** inglés (`company_id`, no `empresa_id`).
- **Commits:** Conventional Commits (`feat(inventory): registrar entrada`).
- **Ramas:** `main`, `develop`, `feature/*`, `fix/*`, `docs/*`.
- **API:** REST/JSON versionada en `/api/v1` a partir del Sprint 1.
- **Seguridad:** secretos fuera del repositorio; permisos verificados en backend.

El glosario bilingüe completo está en [`Reglas/ESTANDAR.md`](./Reglas/ESTANDAR.md), Anexo A.

---

## Estado del Sprint 0

| Historia | Resultado |
| --- | --- |
| US-0001 — Arquitectura y estructura | Completado |
| US-0002 — Modelo de datos y multiempresa | Propuesta documentada |
| US-0003 — Entorno local y CI | Configurado; Docker pendiente de validar en equipo |
| US-0004 — Decisiones de negocio | Documentado; pendiente de aprobación del Product Owner |

---

## Licencia

Ver [LICENSE](./LICENSE).
