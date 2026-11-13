# Registro de decisiones — Sprint 0 (US-0004)

**Proyecto:** SaaS Inversiones R&P
**Documento:** Registro de decisiones de negocio y de datos
**Versión:** 1.0 — Sprint 0
**Fecha:** 2026-11-12
**Responsable del registro:** Product Owner
**Estado general:** Propuestas documentadas — pendientes de aprobación formal

---

## 1. Cómo leer este registro

Cada decisión incluye:

- **Código:** identificador del backlog (`DP-xx` negocio, `DD-xx` datos).
- **Decisión propuesta:** regla recomendada por el equipo técnico.
- **Responsable:** quién debe aprobarla.
- **Fecha:** fecha de registro de la propuesta.
- **Estado:** `Propuesta`, `Aprobada`, `Rechazada` o `Diferida`.
- **Evidencia:** documento, migración o prueba que respalda la decisión.
- **Historias afectadas:** historias que quedan bloqueadas hasta la aprobación.

> **Importante:** las decisiones de negocio requieren aprobación explícita del
> Product Owner. Mientras el estado sea `Propuesta`, las historias afectadas no
> deben implementarse como definitivas.

## 2. Decisiones de negocio (DP)

| Código | Tema | Decisión propuesta | Responsable | Fecha | Estado | Evidencia | Historias afectadas |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DP-01 | Quién registra salidas por venta | El vendedor registra la salida; el cajero puede consultarla. El sistema registra autor, fecha y referencia. | Product Owner | 2026-11-12 | Propuesta | SDD-04 §3.4 | US-0403, integración futura con ventas |
| DP-02 | Valorización del inventario | Costo promedio ponderado por variante, recalculado en cada entrada. | Product Owner | 2026-11-12 | Propuesta | SDD-04 §3.4 | US-0601, reportes de valor |
| DP-03 | Conversiones y redondeo de unidades | Conversión por factor decimal; redondeo a 3 decimales con `HALF_UP`; validar factor > 0. | Product Owner | 2026-11-12 | Propuesta | SDD-04 §3.3 | US-0302, US-0402–US-0503 |
| DP-04 | Conteo concurrente con ventas/movimientos | Bloqueo lógico de la ubicación durante el conteo; movimientos entrantes se registran y se concilian al cerrar. | Product Owner | 2026-11-12 | Propuesta | SDD-04 §7 | US-0701, US-0702 |
| DP-05 | Autorización si falta el administrador | El rol con permiso `inventory.approve` autoriza; si no hay nadie disponible, se escala al owner de la empresa. | Product Owner | 2026-11-12 | Propuesta | SDD-04 §3.1 | US-0405, US-0702 |
| DP-06 | Retención y recuperación de datos | Respaldos diarios con retención de 30 días; movimientos y auditoría se conservan 5 años. | Product Owner | 2026-11-12 | Propuesta | Pendiente: política de respaldos | US-0903 |
| DP-07 | Disponibilidad y rendimiento | 99.5 % mensual; p95 < 500 ms en lecturas; RTO 4 h y RPO 24 h. | Product Owner | 2026-11-12 | Propuesta | SDD-01 §7 | US-0902, US-0903 |
| DP-08 | Límites y reglas de planes SaaS | Un plan base con usuarios, empresas y ubicaciones limitadas; límites configurables por plan. | Product Owner | 2026-11-12 | Propuesta | SDD-04 §3.1 | Fase comercial posterior |
| DP-09 | Precios y márgenes por variante | Precio y costo por variante; margen calculado como `(price - cost) / price`. | Product Owner | 2026-11-12 | Propuesta | SDD-04 §3.2 | US-0601, reportes financieros |
| DP-10 | Importación de inventario histórico | Importación CSV con validación previa, modo simulación y confirmación; una sola carga inicial por empresa. | Product Owner | 2026-11-12 | Propuesta | Pendiente: plantilla CSV | Sprint 0 / carga inicial |

## 3. Decisiones de datos (DD)

| Código | Tema | Decisión propuesta | Responsable | Fecha | Estado | Evidencia | Historias afectadas |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DD-01 | Unicidad del correo electrónico | `users.email` único a nivel global; un usuario puede pertenecer a varias empresas mediante `company_user`. | Líder técnico | 2026-11-12 | Propuesta | SDD-04 §5 | US-0101, US-0102 |
| DD-02 | Nivel del stock mínimo | Stock mínimo por variante y ubicación, con valor por defecto a nivel de variante. | Líder técnico | 2026-11-12 | Propuesta | SDD-04 §3.6 | US-0602 |
| DD-03 | Ubicación en el detalle de movimiento | Obligatoria en todo movimiento que afecte existencias. | Líder técnico | 2026-11-12 | Propuesta | SDD-04 §3.4 | US-0402–US-0503 |
| DD-04 | Roles globales o por empresa | Roles base globales (`owner`, `admin`, `operator`, `viewer`) y roles personalizados por empresa. | Líder técnico | 2026-11-12 | Propuesta | SDD-04 §3.1 | US-0102, US-0103 |
| DD-05 | Borrado lógico y auditoría | Borrado lógico en maestros y catálogo; movimientos confirmados inmutables; auditoría en `audit_logs`. | Líder técnico | 2026-11-12 | Propuesta | SDD-04 §3.6 | Catálogo y trazabilidad |

## 4. Bloqueantes por historia

| Historia | Decisiones que deben aprobarse antes de implementar |
| --- | --- |
| US-0101 | DD-01 |
| US-0102 | DD-01, DD-04 |
| US-0103 | DD-04 |
| US-0302 | DP-03 |
| US-0402–US-0503 | DP-03, DD-03 |
| US-0403 | DP-01 |
| US-0405 | DP-05 |
| US-0601 | DP-02, DP-09 |
| US-0602 | DD-02 |
| US-0701–US-0702 | DP-04, DP-05 |
| US-0902–US-0903 | DP-06, DP-07 |

## 5. Registro de aprobación

| Fecha | Decisión | Aprobado por | Observaciones |
| --- | --- | --- | --- |
| — | — | — | Pendiente de revisión del Product Owner |

> Al aprobar cada decisión, actualizar la columna **Estado**, completar este
> registro y reflejar los cambios en `SDD-04` y en los criterios de aceptación
> de las historias afectadas.
