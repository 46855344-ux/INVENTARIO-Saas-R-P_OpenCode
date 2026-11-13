# DOCUMENTACIÓN — SAAS-INVERSIONES-RP

Documentación oficial del proyecto. Cada carpeta agrupa los entregables según su área.

| Carpeta       | Contenido                                                        |
| ------------- | ---------------------------------------------------------------- |
| `SDD/`        | Diseño de software: arquitectura, casos de uso, modelo de datos   |
| `TESTING/`    | Plan de pruebas, casos, matriz de trazabilidad y evidencias       |
| `DEVSECOPS/`  | Integración continua, calidad de código y pipeline CI/CD          |
| `SEGURIDAD/`  | Políticas de seguridad, análisis de riesgos y controles           |
| `MANUALES/`   | Manuales de usuario, de instalación y documentación técnica       |

El estándar de programación obligatorio del proyecto está en [`Reglas/ESTANDAR.md`](../Reglas/ESTANDAR.md).

## Convenciones

- Documentos de diseño: prefijo `SDD-XX_` y título en mayúsculas (ej. `SDD-04_DISENO_BASE_DATOS.md`).
- Decisiones: `ADR-XXXX` o `REGISTRO_DECISIONES_*` dentro de `SDD/ADR/`.
- Diagramas en formato fuente (`.drawio`, `.puml`) y exportación en `.png`/`.svg`.
- Toda versión de documento debe incluir fecha y autor en el encabezado.
- Los documentos se versionan con Git junto con el código.

## Documentos del Sprint 0

| Documento | Contenido |
| --- | --- |
| [`SDD/SDD-01_ARQUITECTURA_GENERAL.md`](./SDD/SDD-01_ARQUITECTURA_GENERAL.md) | Arquitectura general y módulos |
| [`SDD/SDD-04_DISENO_BASE_DATOS.md`](./SDD/SDD-04_DISENO_BASE_DATOS.md) | Modelo de datos y estrategia multiempresa |
| [`SDD/SDD-09_ESTRUCTURA_REPOSITORIO.md`](./SDD/SDD-09_ESTRUCTURA_REPOSITORIO.md) | Estructura y convenciones |
| [`SDD/ADR/REGISTRO_DECISIONES_SPRINT_0.md`](./SDD/ADR/REGISTRO_DECISIONES_SPRINT_0.md) | Decisiones DP/DD y bloqueos |
| [`SDD/ACTA_CIERRE_SPRINT_0.md`](./SDD/ACTA_CIERRE_SPRINT_0.md) | Acta de cierre del Sprint 0 |
| [`MANUALES/MANUAL_INSTALACION.md`](./MANUALES/MANUAL_INSTALACION.md) | Instalación local y con Docker |
| [`TESTING/PLAN_PRUEBAS_SPRINT_0.md`](./TESTING/PLAN_PRUEBAS_SPRINT_0.md) | Plan y evidencias de prueba |
| [`DEVSECOPS/CI_CD.md`](./DEVSECOPS/CI_CD.md) | Pipeline de integración continua |
