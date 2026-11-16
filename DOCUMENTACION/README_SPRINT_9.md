# README — Sprint 9 «Estabilización y producción»

**Proyecto:** SaaS Inversiones R&P
**Épica:** EP-09
**Fecha:** 2026-11-16
**Estado:** Release candidate — producción **no habilitada**
**Plan:** [`SDD/PLAN_SPRINT_9_ESTABILIZACION_PRODUCCION.md`](./SDD/PLAN_SPRINT_9_ESTABILIZACION_PRODUCCION.md)
**Runbook:** [`DEVSECOPS/GUIA_PRODUCCION.md`](./DEVSECOPS/GUIA_PRODUCCION.md)
**Aceptación:** [`TESTING/ACEPTACION_MVP.md`](./TESTING/ACEPTACION_MVP.md)

## 1. Resumen

El Sprint 9 consolidó los controles de calidad, la auditoría de dependencias y
la documentación operativa. No se agregaron funcionalidades ni se modificó el
esquema. El resultado es un **release candidate verificable en local**, no una
declaración de producción.

## 2. Entregables

| Entregable | Ubicación |
| --- | --- |
| Job de auditoría de dependencias | `.github/workflows/ci.yml` |
| Runbook de despliegue, rollback y backup | `DEVSECOPS/GUIA_PRODUCCION.md` |
| Matriz de aceptación del MVP | `TESTING/ACEPTACION_MVP.md` |
| Procedimiento de despliegue actualizado | `scripts/deployment/README.md` |

## 3. Controles de calidad

| Control | Resultado |
| --- | --- |
| Backend PHPUnit | 155 pruebas / 608 aserciones |
| Pint | Sin diferencias |
| Frontend Vitest | 78 pruebas |
| Oxlint | Sin errores |
| Build | Correcto |
| Composer audit | Sin vulnerabilidades |
| npm audit (producción) | 0 vulnerabilidades |

## 4. Estado de release

**Candidato local**, con las siguientes brechas bloqueantes:

- MySQL real y Docker no validados en este entorno.
- HTTPS, cookies seguras y CORS de staging pendientes.
- Restauración real y RPO/RTO pendientes (DP-06/DP-07).
- OAuth con Google/Meta reales pendiente.
- Aceptación formal del Product Owner pendiente.

## 5. Checklist de promoción

1. Etiquetar el commit aprobado.
2. Respaldar y verificar la copia.
3. Desplegar en staging con MySQL y HTTPS.
4. Ejecutar migraciones, smoke tests y suite completa.
5. Probar restauración en base aislada.
6. Revisar logs, errores y métricas.
7. Aprobar el cambio y promover a producción.
8. Documentar release y rollback.

## 6. Commits

```text
docs(sprint-9): planificar estabilizacion y produccion
ci(security): auditar dependencias en el pipeline
docs(sprint-9): agregar runbook y aceptacion del MVP
```

## 7. Conclusión

El proyecto queda en estado **release candidate**. No se declara listo para
producción hasta cerrar las brechas de la sección 4 y obtener aceptación formal.
