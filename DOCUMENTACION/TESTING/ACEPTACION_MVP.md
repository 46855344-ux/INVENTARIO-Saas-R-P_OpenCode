# Aceptación del MVP — Sprint 9

**Proyecto:** SaaS Inversiones R&P
**Fecha:** 2026-11-16
**Estado:** Evidencia local; **no sustituye** aceptación formal ni staging

## 1. Matriz de aceptación

| Área | Verificación | Evidencia | Estado |
| --- | --- | --- | --- |
| Identidad | Login local, sesión, logout, permisos | Pruebas Auth + Sprint 1 | OK local |
| Auth social | Google/Facebook simulados, vinculación y errores | Pruebas incremento v1.1 | OK con mocks |
| Catálogo | Productos, variantes, categorías y marcas | Pruebas Sprint 2 | OK local |
| Unidades/ubicaciones | Conversiones, precisión y jerarquía | Pruebas Sprint 3 | OK local |
| Inventario | Entradas, salidas, reversión, idempotencia | Pruebas Sprint 4 | OK local |
| Compras/traslados | Costos históricos y atomicidad | Pruebas Sprint 5 | OK local |
| Métricas/alertas | Indicadores, reglas y deduplicación | Pruebas Sprint 6 | OK local |
| Conteos/auditoría | Foto teórica, aprobación y bitácora | Pruebas Sprint 7 | OK local |
| Asistente | Solo lectura, permisos y prompt injection | Pruebas Sprint 8 | OK local |
| Multiempresa | Aislamiento A/B | Pruebas de todos los módulos | OK local |
| Seguridad de dependencias | `composer audit`, `npm audit` | Ejecución local 2026-11-16 | OK |
| CI | Pint, PHPUnit, Oxlint, Vitest, build y auditoría | `.github/workflows/ci.yml` | Configurado |

## 2. Evidencia cuantitativa

| Suite | Resultado |
| --- | --- |
| Backend | 155 pruebas / 608 aserciones |
| Frontend | 78 pruebas |
| Pint | Sin diferencias |
| Oxlint | Sin errores |
| Build | Correcto |
| Composer audit | Sin vulnerabilidades |
| npm audit (producción) | 0 vulnerabilidades |

## 3. Pendientes para aceptación

- Ejecutar la suite contra MySQL real y Docker.
- Validar HTTPS, cookies seguras y CORS en staging.
- Ejecutar backup/restore real y registrar RPO/RTO.
- Validar OAuth con Google/Meta reales.
- Revisión de accesibilidad y usabilidad con usuarios.
- Aprobación formal del Product Owner.

## 4. Conclusión

El repositorio está en estado **release candidate** con evidencia local. No se
declara «listo para producción» hasta completar los pendientes anteriores.
