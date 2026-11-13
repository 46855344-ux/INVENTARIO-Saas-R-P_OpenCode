# Changelog

Todos los cambios relevantes de este proyecto se documentan en este archivo.

El formato sigue las convenciones de [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/)
y el versionado semántico de [SemVer](https://semver.org/lang/es/).

## [No publicado]

### Añadido

- **Sprint 0 — Fundación técnica**
  - Backend Laravel 13.42 con PHP 8.3, Laravel Sanctum 4.4 y PHPUnit 12.5.
  - Frontend React 19.3 + TypeScript 7 + Vite 8.3, con Vitest 5.1 y Testing Library.
  - Cypress 16.3 configurado en la raíz (`tests/e2e`, `tests/fixtures`, `tests/support`).
  - Endpoint `GET /api/health` y prueba de integración asociada.
  - Estructura modular del backend: `Identity`, `Catalog`, `Inventory`, `Purchasing`, `Metrics`, `Audit`.
  - Estructura por dominio del frontend: `auth`, `products`, `inventory`, `movements`, `suppliers`, `metrics`, `alerts`.
  - Docker: `Dockerfile.backend`, `Dockerfile.frontend`, `php.ini`, `entrypoint.backend.sh`.
  - `docker-compose.yml` con backend, frontend y MySQL 8.0 (`utf8mb4`).
  - Virtual host de referencia para Apache y guía de entorno XAMPP.
  - Pipeline CI con trabajos de backend (PHP 8.3, Pint, PHPUnit) y frontend (Node 22, lint, test, build).
  - Plantillas `.env.example` (raíz y backend) y `.dockerignore`.
  - `tsconfig.json` raíz y `@types/node` para compilar las pruebas Cypress.
  - Scripts de preparación (`setup.ps1`, `setup.sh`), respaldo y restauración de MySQL.
  - Documentación: `SDD-01`, `SDD-04`, `SDD-09`, registro de decisiones DP/DD, acta de cierre del Sprint 0, manual de instalación, plan de pruebas y guía de CI/CD.
  - Script SQL de inicialización `database/scripts/01-init.sql`.
  - **Cierre documental del Sprint 0:**
    - `DOCUMENTACION/README_SPRINT_0.md`: README de cierre con entregables, evidencias, DoD, demo, pendientes y guía de iteraciones.
    - `DOCUMENTACION/SDD/SDD-05_DISENO_API.md`: contratos, versionado, autenticación, tenant y error de la API.
    - `database/diagrams/DER_LOGICO.md`: diagrama entidad-relación lógico y clasificación de tablas.
    - `database/dictionaries/DICCIONARIO_DATOS.md`: diccionario de datos con tipos, claves, índices y matriz de ámbito.
    - `REGISTRO_DECISIONES_SPRINT_0.md` ampliado con la plantilla de cierre (alternativas, fundamento, aprobador, migraciones/API afectadas y enlace ADR).

### Cambiado

- `README.md` actualizado con stack, estructura, inicio rápido, scripts y estado del Sprint 0.
- `.env.example` alineado con MySQL 8, zona horaria UTC y variables de Docker.
- `.gitignore` ampliado para entorno, dependencias, reportes y respaldos.
- `composer.json` del backend con Sanctum y Laravel Boost como dependencia de desarrollo.
- `phpunit.xml` configurado con SQLite en memoria para pruebas sin MySQL.

### Corregido

- Codificación UTF-8 y acentos en la documentación y archivos de configuración.
- Prueba de componentes del frontend ajustada para evitar coincidencias múltiples de texto.
- Estilo de `bootstrap/app.php` normalizado con Pint.
- Configuración de TypeScript para que Cypress compile los specs (`TsConfigNotFoundError` resuelto).

### Eliminado

- Archivos de ejemplo del template de Vite (`App.css`, `index.css`, logos e íconos sin uso).
- `AGENTS.md` permanece como guía de Laravel Boost; no se reinstala.

---

## [0.1.0] - 2026-11-12

### Añadido

- Versión inicial del proyecto.
- `Reglas/ESTANDAR.md`: reglas obligatorias de programación (ISO 25001/25010/27001/19119/29119, convenciones de nombres, SOLID, BDD, multiempresa, integridad de inventario, seguridad, pruebas y checklist de cierre), con glosario bilingüe español → inglés.
- Estructura inicial del repositorio (`frontend`, `backend`, `database`, `tests`, `infrastructure`).
- Carpetas de documentación oficial (`DOCUMENTACION/`).
- Scripts de automatización (`scripts/`).
- Pipeline CI/CD base en `.github/workflows/`.
- Plantilla de variables de entorno `.env.example`.
- Configuración de `.gitignore`.
- `docker-compose.yml` base para el entorno de desarrollo.
