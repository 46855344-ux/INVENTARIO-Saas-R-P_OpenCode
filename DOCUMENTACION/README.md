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

> **Punto de entrada:** [`README_SPRINT_0.md`](./README_SPRINT_0.md) resume todo el
> Sprint 0 (entregables, evidencias, DoD, demo, pendientes) y responde si este
> proceso debe repetirse en cada iteración.

| Documento | Contenido |
| --- | --- |
| [`README_SPRINT_0.md`](./README_SPRINT_0.md) | README de cierre del Sprint 0 (resumen y evidencias) |
| [`SDD/SDD-01_ARQUITECTURA_GENERAL.md`](./SDD/SDD-01_ARQUITECTURA_GENERAL.md) | Arquitectura general y módulos |
| [`SDD/SDD-04_DISENO_BASE_DATOS.md`](./SDD/SDD-04_DISENO_BASE_DATOS.md) | Modelo de datos y estrategia multiempresa |
| [`SDD/SDD-05_DISENO_API.md`](./SDD/SDD-05_DISENO_API.md) | Contratos y convenciones de la API REST |
| [`SDD/SDD-09_ESTRUCTURA_REPOSITORIO.md`](./SDD/SDD-09_ESTRUCTURA_REPOSITORIO.md) | Estructura y convenciones |
| [`SDD/ADR/REGISTRO_DECISIONES_SPRINT_0.md`](./SDD/ADR/REGISTRO_DECISIONES_SPRINT_0.md) | Decisiones DP/DD, alternativas y bloqueos |
| [`SDD/ACTA_CIERRE_SPRINT_0.md`](./SDD/ACTA_CIERRE_SPRINT_0.md) | Acta de cierre del Sprint 0 |
| [`MANUALES/MANUAL_INSTALACION.md`](./MANUALES/MANUAL_INSTALACION.md) | Instalación local y con Docker |
| [`TESTING/PLAN_PRUEBAS_SPRINT_0.md`](./TESTING/PLAN_PRUEBAS_SPRINT_0.md) | Plan y evidencias de prueba |
| [`DEVSECOPS/CI_CD.md`](./DEVSECOPS/CI_CD.md) | Pipeline de integración continua |
| [`../database/diagrams/DER_LOGICO.md`](../database/diagrams/DER_LOGICO.md) | Diagrama entidad-relación lógico |
| [`../database/dictionaries/DICCIONARIO_DATOS.md`](../database/dictionaries/DICCIONARIO_DATOS.md) | Diccionario de datos del modelo lógico |

## Historial de sprints

Cada iteración produce su propio README/acta de cierre siguiendo la plantilla del
Sprint 0:

| Sprint | Documento de cierre | Estado |
| --- | --- | --- |
| 0 | [`README_SPRINT_0.md`](./README_SPRINT_0.md) | Cerrado técnicamente |
| 1 | [`README_SPRINT_1.md`](./README_SPRINT_1.md) | Cerrado técnicamente |
| 2 | [`README_SPRINT_2.md`](./README_SPRINT_2.md) | Cerrado técnicamente |
| 3 | [`README_SPRINT_3.md`](./README_SPRINT_3.md) | Cerrado técnicamente |
| 4 | [`README_SPRINT_4.md`](./README_SPRINT_4.md) | Cerrado técnicamente |

> Los READMEs de cierre resumen objetivo, entregables, historias, decisiones,
> evidencias de prueba, DoD, demo y pendientes de cada iteración.

## Planificación del Sprint 1

| Documento | Contenido |
| --- | --- |
| [`SDD/PLAN_SPRINT_1_IDENTIDAD_MULTIEMPRESA.md`](./SDD/PLAN_SPRINT_1_IDENTIDAD_MULTIEMPRESA.md) | Plan del Sprint 1: checklist de entrada, backlog refinado, diseño, contratos, permisos y pruebas |
| [`SDD/ADR/ADR-0001_ESTRATEGIA_AUTENTICACION_SPA.md`](./SDD/ADR/ADR-0001_ESTRATEGIA_AUTENTICACION_SPA.md) | Propuesta: cookie de SPA vs token Bearer |
| [`SDD/ADR/ADR-0002_ESTRATEGIA_RESOLUCION_TENANT.md`](./SDD/ADR/ADR-0002_ESTRATEGIA_RESOLUCION_TENANT.md) | Propuesta: resolución de la empresa activa |
| [`SDD/ADR/ADR-0003_MODELO_PERMISOS.md`](./SDD/ADR/ADR-0003_MODELO_PERMISOS.md) | Propuesta: roles, permisos y excepciones |

## Planificación del Sprint 2

| Documento | Contenido |
| --- | --- |
| [`SDD/PLAN_SPRINT_2_CATALOGO.md`](./SDD/PLAN_SPRINT_2_CATALOGO.md) | Catálogo: decisiones, historias US-0201 a US-0203, contratos, diseño y pruebas |

## Planificación del Sprint 3

| Documento | Contenido |
| --- | --- |
| [`SDD/PLAN_SPRINT_3_UNIDADES_UBICACIONES.md`](./SDD/PLAN_SPRINT_3_UNIDADES_UBICACIONES.md) | Unidades, conversiones y ubicaciones: decisiones, historias US-0301 a US-0303, contratos y pruebas |

## Planificación del Sprint 4

| Documento | Contenido |
| --- | --- |
| [`SDD/PLAN_SPRINT_4_INVENTARIO_CENTRAL.md`](./SDD/PLAN_SPRINT_4_INVENTARIO_CENTRAL.md) | Núcleo de inventario: alcance, decisiones, modelo, contratos, reglas transaccionales, pruebas y DoR/DoD |
