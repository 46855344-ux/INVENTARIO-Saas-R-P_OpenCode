# Despliegue

Esta carpeta contendrá los scripts de despliegue por ambiente. En el Sprint 0
solo se documenta el procedimiento; no hay destinos de producción configurados.

## Ambientes previstos

| Ambiente | Propósito | Estado |
| --- | --- | --- |
| `staging` | Validación previa a producción | Pendiente (Sprint 5–9) |
| `production` | Uso real de clientes | Pendiente (Sprint 9) |

## Reglas

1. Los secretos se inyectan desde el orquestador, nunca desde el repositorio.
2. Todo despliegue ejecuta migraciones y pruebas de humo.
3. Debe existir un respaldo verificable antes de migrar.
4. Los despliegues se registran en `CHANGELOG.md` y en el historial de releases.

## Referencias

- `DOCUMENTACION/DEVSECOPS/CI_CD.md`
- `DOCUMENTACION/MANUALES/MANUAL_INSTALACION.md`
- `Reglas/ESTANDAR.md`
