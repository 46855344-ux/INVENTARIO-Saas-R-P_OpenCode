# Despliegue

Esta carpeta contiene los scripts y el procedimiento de despliegue por ambiente.
El detalle operativo está en
[`DOCUMENTACION/DEVSECOPS/GUIA_PRODUCCION.md`](../../DOCUMENTACION/DEVSECOPS/GUIA_PRODUCCION.md).

## Ambientes

| Ambiente | Propósito | Estado |
| --- | --- | --- |
| `staging` | Validación previa a producción | Pendiente de ejecución real |
| `production` | Uso real de clientes | **No habilitado** |

## Reglas

1. Los secretos se inyectan desde el orquestador, nunca desde el repositorio.
2. Todo despliegue ejecuta migraciones controladas y smoke tests.
3. Debe existir un respaldo verificable antes de migrar.
4. Los despliegues se registran en `CHANGELOG.md` y en el historial de releases.
5. El rollback requiere aprobación y, si toca datos, respaldo previo.

## Referencias

- `DOCUMENTACION/DEVSECOPS/CI_CD.md`
- `DOCUMENTACION/DEVSECOPS/GUIA_PRODUCCION.md`
- `DOCUMENTACION/TESTING/ACEPTACION_MVP.md`
- `DOCUMENTACION/MANUALES/MANUAL_INSTALACION.md`
- `Reglas/ESTANDAR.md`
