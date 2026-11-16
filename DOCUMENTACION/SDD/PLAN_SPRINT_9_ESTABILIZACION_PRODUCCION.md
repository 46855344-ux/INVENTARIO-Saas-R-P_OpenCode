# Plan — Sprint 9 «Estabilización y producción»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-09
**Fecha:** 2026-11-16
**Estado:** En implementación

## 1. Objetivo

Consolidar un release candidato verificable: calidad automatizada, seguridad de
dependencias, despliegue reproducible, respaldo/restauración documentados y
aceptación funcional. **No se declara producción sin evidencias de MySQL,
HTTPS y aceptación del Product Owner.**

## 2. Alcance

| Historia | Resultado |
| --- | --- |
| US-0901 | CI con pruebas, Pint, lint, build y auditoría de dependencias |
| US-0902 | Runbook de despliegue, variables, TLS, migraciones, smoke y rollback |
| US-0903 | Política y procedimiento de backup/restore; RPO/RTO pendientes DP-06/07 |
| US-0904 | Matriz de aceptación del MVP y evidencia de la versión candidata |

Fuera de alcance: nuevas funcionalidades, cambios de esquema, contratación de
cloud, metas numéricas de disponibilidad no aprobadas y certificaciones ISO.

## 3. Decisiones

| Tema | Decisión |
| --- | --- |
| Calidad | Los controles actuales (Pint, PHPUnit, Oxlint, Vitest, build) son bloqueantes |
| Dependencias | `composer audit` y `npm audit --omit=dev --audit-level=high` en CI |
| SonarQube/OWASP | Recomendados, no obligatorios sin aprobación y credenciales |
| Secretos | Solo variables de entorno/gestor; `.env` fuera de Git |
| Backup | Scripts existentes; copia fuera del servidor y restore probado antes de producción |
| RPO/RTO | Pendientes DP-06/DP-07; no inventar cifras |
| Release | Candidato, no producción, hasta validar MySQL/Docker/HTTPS y aceptación |

## 4. Entregables

- Job `security` en `.github/workflows/ci.yml`.
- `DOCUMENTACION/DEVSECOPS/GUIA_PRODUCCION.md` con despliegue, rollback y backup.
- `DOCUMENTACION/TESTING/ACEPTACION_MVP.md` con matriz y evidencias.
- Actualización de `scripts/deployment/README.md`.
- README de cierre y CHANGELOG.

## 5. Evidencia base

| Verificación | Resultado |
| --- | --- |
| Backend | 155 pruebas / 608 aserciones |
| Frontend | 78 pruebas |
| Pint / lint / build | OK |
| Composer audit | Sin vulnerabilidades |
| npm audit (prod) | 0 vulnerabilidades |
| MySQL/Docker/HTTPS | **No validado en este entorno** |

## 6. Criterios de salida

- [x] CI con controles bloqueantes y auditoría de dependencias.
- [x] Runbook de despliegue y rollback documentado.
- [x] Backup/restore documentado y scripts referenciados.
- [x] Matriz de aceptación con evidencia y pendientes explícitos.
- [ ] Validación en staging con MySQL real y HTTPS.
- [ ] Prueba de restauración real y aprobación del Product Owner.

## 7. Riesgos

| Riesgo | Mitigación |
| --- | --- |
| Declarar producción sin staging | Estado «release candidate» explícito |
| Pérdida de datos | Backup previo, restore probado y ventana de cambio |
| Dependencias vulnerables | Auditoría en CI y revisión de reportes |
| Secretos filtrados | Variables de entorno, escaneo y `.gitignore` |
| Rollback incompleto | Plan de reversión y migraciones compatibles |
