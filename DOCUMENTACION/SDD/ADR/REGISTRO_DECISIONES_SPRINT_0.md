# Registro de decisiones — Sprint 0 (US-0004)

**Proyecto:** SaaS Inversiones R&P
**Documento:** Registro de decisiones de negocio (DP) y de datos (DD)
**Versión:** 1.2 — Sprint 0 / Sprint 1
**Fecha:** 2026-11-13
**Responsable del registro:** Product Owner
**Estado general:** DD-01 y DD-04 aprobadas; ADR-0001, ADR-0002 y ADR-0003 aprobadas; DP-01..DP-10 y DD-02/03/05 siguen como propuestas

---

## 1. Cómo leer este registro

El documento maestro (§13) exige, para cada decisión, la siguiente **plantilla de cierre**:

> ID · alternativa elegida · alternativas descartadas · fundamento · fecha · aprobador ·
> historias afectadas · migraciones/API afectadas · enlace ADR.

Cada decisión de este registro se presenta en dos partes:

1. **Tabla resumen** (§2 y §3) con el estado y las historias bloqueadas.
2. **Ficha detallada** (§4) que cumple la plantilla completa.

Campos utilizados:

- **Código:** identificador del backlog (`DP-xx` negocio, `DD-xx` datos).
- **Alternativa elegida:** regla recomendada por el equipo técnico.
- **Alternativas descartadas:** opciones evaluadas y no seleccionadas, con el motivo.
- **Fundamento:** razón técnica o de negocio que sostiene la elección.
- **Estado:** `Propuesta`, `Aprobada`, `Rechazada` o `Diferida`.
- **Aprobador:** quién debe aprobarla (Product Owner o Líder técnico).
- **Historias afectadas:** historias que quedan bloqueadas hasta la aprobación.
- **Migraciones/API afectadas:** artefactos técnicos que materializan la decisión.
- **Enlace ADR:** referencia interna al documento o sección que la respalda.

> **Importante:** las decisiones de negocio requieren aprobación explícita del
> Product Owner. Mientras el estado sea `Propuesta`, las historias afectadas no
> deben implementarse como definitivas. Las decisiones de datos (DD) son
> propuestas del Líder técnico y se validan con el Product Owner cuando impactan
> reglas de negocio.

## 2. Resumen de decisiones de negocio (DP)

| Código | Tema | Alternativa elegida (resumen) | Aprobador | Fecha | Estado | Historias afectadas |
| --- | --- | --- | --- | --- | --- | --- |
| DP-01 | Quién registra salidas por venta | El vendedor registra la salida; el cajero consulta | Product Owner | 2026-11-13 | Propuesta | US-0403, integración con ventas |
| DP-02 | Valorización del inventario | Costo promedio ponderado por variante | Product Owner | 2026-11-13 | Propuesta | US-0601, reportes de valor |
| DP-03 | Conversiones y redondeo | Factor decimal; redondeo 3 decimales `HALF_UP` | Product Owner | 2026-11-13 | Propuesta | US-0302, US-0402–US-0503 |
| DP-04 | Conteo concurrente con movimientos | Bloqueo lógico de la ubicación y conciliación al cerrar | Product Owner | 2026-11-13 | Propuesta | US-0701, US-0702 |
| DP-05 | Autorización sin administrador | Rol con permiso `inventory.approve`; escalado al owner | Product Owner | 2026-11-13 | Propuesta | US-0405, US-0702 |
| DP-06 | Retención y recuperación de datos | Respaldos diarios 30 días; movimientos/auditoría 5 años | Product Owner | 2026-11-13 | Propuesta | US-0903 |
| DP-07 | Disponibilidad y rendimiento | 99.5 % mensual; p95 < 500 ms; RTO 4 h; RPO 24 h | Product Owner | 2026-11-13 | Propuesta | US-0902, US-0903 |
| DP-08 | Límites y reglas de planes SaaS | Plan base con límites configurables | Product Owner | 2026-11-13 | Propuesta | Fase comercial posterior |
| DP-09 | Precios y márgenes por variante | Costo y precio por variante; margen `(price - cost) / price` | Product Owner | 2026-11-13 | Propuesta | US-0601, reportes financieros |
| DP-10 | Importación de inventario histórico | Importación CSV con simulación y confirmación | Product Owner | 2026-11-13 | Propuesta | Carga inicial |

## 3. Resumen de decisiones de datos (DD)

| Código | Tema | Alternativa elegida (resumen) | Aprobador | Fecha | Estado | Historias afectadas |
| --- | --- | --- | --- | --- | --- | --- |
| DD-01 | Unicidad del correo electrónico | `users.email` único global + `company_user` | Líder técnico | 2026-11-13 | Aprobada | US-0101, US-0102 |
| DD-02 | Nivel del stock mínimo | Por variante (por defecto) y por ubicación | Líder técnico | 2026-11-13 | Propuesta | US-0602 |
| DD-03 | Ubicación en el movimiento | Obligatoria en todo movimiento que afecte stock | Líder técnico | 2026-11-13 | Propuesta | US-0402–US-0503 |
| DD-04 | Roles globales o por empresa | Roles base globales + roles personalizados por empresa | Líder técnico | 2026-11-13 | Aprobada | US-0102, US-0103 |
| DD-05 | Borrado lógico y auditoría | Baja lógica en maestros; movimientos inmutables; `audit_logs` | Líder técnico | 2026-11-13 | Propuesta | Catálogo y trazabilidad |

## 4. Fichas detalladas

### DP-01 — Quién registra las salidas por venta

| Campo | Contenido |
| --- | --- |
| Alternativa elegida | El vendedor registra la salida de inventario; el cajero puede consultarla. El sistema guarda autor, fecha y referencia del documento. |
| Alternativas descartadas | (a) Registro automático desde un módulo de ventas inexistente en el MVP; (b) registro exclusivo por el administrador, que genera cuello de botella operativo. |
| Fundamento | El MVP no incluye facturación; separar «quién vende» de «quién administra» mantiene trazabilidad sin bloquear la operación diaria. |
| Fecha | 2026-11-13 |
| Aprobador | Product Owner |
| Historias afectadas | US-0403; integración futura con ventas |
| Migraciones/API afectadas | `stock_movements.created_by`, `stock_movements.reference_type`/`reference_id`; `POST /api/v1/stock-movements` |
| Enlace ADR | `SDD-04` §3.4; `DER_LOGICO.md` §5 |

### DP-02 — Valorización del inventario

| Campo | Contenido |
| --- | --- |
| Alternativa elegida | Costo promedio ponderado por variante, recalculado en cada entrada. |
| Alternativas descartadas | (a) PEPS/FIFO por capas, que exige tablas de capas y mayor complejidad; (b) costo estándar fijo, que no refleja compras reales. |
| Fundamento | Es el método más simple y suficiente para el MVP, con trazabilidad de costo por movimiento y sin tablas adicionales. |
| Fecha | 2026-11-13 |
| Aprobador | Product Owner |
| Historias afectadas | US-0601; reportes de valorización |
| Migraciones/API afectadas | `product_variants.cost`; `stock_movements.unit_cost`; `GET /api/v1/metrics/*` |
| Enlace ADR | `SDD-04` §3.4; `DICCIONARIO_DATOS.md` §10 |

### DP-03 — Conversiones y redondeo de unidades

| Campo | Contenido |
| --- | --- |
| Alternativa elegida | Conversión por factor decimal; redondeo a 3 decimales con `HALF_UP`; validar factor > 0. |
| Alternativas descartadas | (a) Solo unidades enteras, insuficiente para peso/volumen; (b) conversiones anidadas en cascada, difíciles de auditar. |
| Fundamento | `DECIMAL(14,3)` cubre fracciones habituales y `HALF_UP` es el redondeo esperado en operación comercial. |
| Fecha | 2026-11-13 |
| Aprobador | Product Owner |
| Historias afectadas | US-0302; US-0402–US-0503 |
| Migraciones/API afectadas | `units`, `unit_conversions.factor DECIMAL(18,6)`; validación en API de movimientos |
| Enlace ADR | `SDD-04` §3.3; `DICCIONARIO_DATOS.md` §5.2 |

### DP-04 — Conteo concurrente con ventas y movimientos

| Campo | Contenido |
| --- | --- |
| Alternativa elegida | Bloqueo lógico de la ubicación durante el conteo; los movimientos entrantes se registran y se concilian al cerrar. |
| Alternativas descartadas | (a) Bloqueo total del inventario, que detiene la operación; (b) conteo sin control, que produce diferencias no explicables. |
| Fundamento | Permite contar sin detener ventas, manteniendo un registro auditable de lo ocurrido durante el conteo. |
| Fecha | 2026-11-13 |
| Aprobador | Product Owner |
| Historias afectadas | US-0701, US-0702 |
| Migraciones/API afectadas | `physical_counts.status`, `physical_count_items.difference`; `POST /api/v1/physical-counts/{id}/close` |
| Enlace ADR | `SDD-04` §7; `DICCIONARIO_DATOS.md` §8.1–8.2 |

### DP-05 — Autorización cuando falta el administrador

| Campo | Contenido |
| --- | --- |
| Alternativa elegida | El rol con permiso `inventory.approve` autoriza; si nadie está disponible, se escala al owner de la empresa. |
| Alternativas descartadas | (a) Autorización solo del administrador, que bloquea ajustes; (b) autoaprobación del operador, que rompe la segregación de funciones. |
| Fundamento | Mantiene el control interno (quien ejecuta no aprueba) sin detener la operación por ausencia del administrador. |
| Fecha | 2026-11-13 |
| Aprobador | Product Owner |
| Historias afectadas | US-0405, US-0702 |
| Migraciones/API afectadas | `permissions.code = 'inventory.approve'`; `physical_count_items.approved_by`; endpoints de aprobación |
| Enlace ADR | `SDD-04` §3.1; `DICCIONARIO_DATOS.md` §3.5 |

### DP-06 — Retención y recuperación de datos

| Campo | Contenido |
| --- | --- |
| Alternativa elegida | Respaldos diarios con retención de 30 días; movimientos y auditoría se conservan 5 años. |
| Alternativas descartadas | (a) Retención indefinida de todo, con costo creciente; (b) retención corta uniforme, que incumple requisitos de trazabilidad. |
| Fundamento | Separa la copia operativa (recuperación) de la evidencia contable/auditable (conservación prolongada). |
| Fecha | 2026-11-13 |
| Aprobador | Product Owner |
| Historias afectadas | US-0903 |
| Migraciones/API afectadas | Política de respaldos en `scripts/database/`; sin cambio de esquema |
| Enlace ADR | `SDD-01` §7; pendiente política de respaldos |

### DP-07 — Disponibilidad y rendimiento

| Campo | Contenido |
| --- | --- |
| Alternativa elegida | Disponibilidad 99.5 % mensual; p95 < 500 ms en lecturas; RTO 4 h y RPO 24 h. |
| Alternativas descartadas | (a) 99.9 % con arquitectura redundante, sobredimensionada para el MVP; (b) sin objetivos definidos, imposible de verificar. |
| Fundamento | Objetivos alcanzables con un monolito modular y verificables mediante métricas y monitoreo básico. |
| Fecha | 2026-11-13 |
| Aprobador | Product Owner |
| Historias afectadas | US-0902, US-0903 |
| Migraciones/API afectadas | Monitoreo y logs; sin cambio de esquema |
| Enlace ADR | `SDD-01` §7 |

### DP-08 — Límites y reglas de los planes SaaS

| Campo | Contenido |
| --- | --- |
| Alternativa elegida | Un plan base con límites de usuarios, empresas y ubicaciones configurables por plan. |
| Alternativas descartadas | (a) Límites fijos en código, difíciles de cambiar comercialmente; (b) sin límites, que permite abuso de recursos. |
| Fundamento | Permite evolucionar la oferta comercial sin tocar el núcleo del inventario. |
| Fecha | 2026-11-13 |
| Aprobador | Product Owner |
| Historias afectadas | Fase comercial posterior |
| Migraciones/API afectadas | Futuras tablas `plans`/`subscriptions` (fuera del MVP); middleware de límites |
| Enlace ADR | `SDD-04` §3.1; `DER_LOGICO.md` §3 |

### DP-09 — Precios y márgenes por variante

| Campo | Contenido |
| --- | --- |
| Alternativa elegida | Costo y precio por variante; margen calculado como `(price - cost) / price`. |
| Alternativas descartadas | (a) Precio a nivel de producto, que impide variantes con distinto valor; (b) margen calculado en el frontend, no auditable. |
| Fundamento | El costo/valor real vive en la variante; calcular el margen en el backend garantiza consistencia en reportes. |
| Fecha | 2026-11-13 |
| Aprobador | Product Owner |
| Historias afectadas | US-0601; reportes financieros |
| Migraciones/API afectadas | `product_variants.cost` y `.price`; `GET /api/v1/metrics/*` |
| Enlace ADR | `SDD-04` §3.2; `DICCIONARIO_DATOS.md` §4.4 |

### DP-10 — Importación de inventario histórico

| Campo | Contenido |
| --- | --- |
| Alternativa elegida | Importación CSV con validación previa, modo simulación y confirmación; una sola carga inicial por empresa. |
| Alternativas descartadas | (a) Carga directa sin validación, con riesgo de datos corruptos; (b) carga manual ítem por ítem, inviable a escala. |
| Fundamento | La simulación permite detectar errores antes de afectar saldos; la carga única evita duplicados. |
| Fecha | 2026-11-13 |
| Aprobador | Product Owner |
| Historias afectadas | Carga inicial |
| Migraciones/API afectadas | `POST /api/v1/stock-movements` con `type = 'adjustment'`; plantilla CSV pendiente |
| Enlace ADR | `SDD-04` §3.4; pendiente plantilla CSV |

### DD-01 — Unicidad del correo electrónico

| Campo | Contenido |
| --- | --- |
| Alternativa elegida | `users.email` único a nivel global; un usuario puede pertenecer a varias empresas mediante `company_user`. |
| Alternativas descartadas | (a) Correo único por empresa, que obliga a múltiples cuentas por persona; (b) sin unicidad, que impide identificar al usuario. |
| Fundamento | Facilita la identidad única de la persona y el cambio de empresa sin duplicar credenciales. |
| Fecha | 2026-11-13 |
| Aprobador | Líder técnico |
| Historias afectadas | US-0101, US-0102 |
| Migraciones/API afectadas | `users.email UNIQUE`; `company_user`; `POST /api/v1/auth/login` |
| Enlace ADR | `SDD-04` §5; `DICCIONARIO_DATOS.md` §3.2 |
| Estado | Aprobada (2026-11-13) |

### DD-02 — Nivel del stock mínimo

| Campo | Contenido |
| --- | --- |
| Alternativa elegida | Stock mínimo por variante (valor por defecto) y por ubicación (override). |
| Alternativas descartadas | (a) Solo por producto, que ignora diferencias entre variantes; (b) solo por ubicación, que multiplica la configuración. |
| Fundamento | El valor por variante da una base razonable y la ubicación permite afinar donde el almacén lo requiere. |
| Fecha | 2026-11-13 |
| Aprobador | Líder técnico |
| Historias afectadas | US-0602 |
| Migraciones/API afectadas | `product_variants.min_stock`; umbral por ubicación en `stock_alerts.threshold` |
| Enlace ADR | `SDD-04` §3.6; `DICCIONARIO_DATOS.md` §4.4, §8.3 |

### DD-03 — Ubicación en el detalle de movimiento

| Campo | Contenido |
| --- | --- |
| Alternativa elegida | `location_id` obligatorio en todo movimiento que afecte existencias. |
| Alternativas descartadas | (a) Ubicación opcional, que impide calcular saldos por ubicación; (b) ubicación a nivel de cabecera únicamente, insuficiente para movimientos multilínea. |
| Fundamento | El saldo se lleva por variante y ubicación; sin ubicación no es posible garantizar consistencia ni trazabilidad. |
| Fecha | 2026-11-13 |
| Aprobador | Líder técnico |
| Historias afectadas | US-0402–US-0503 |
| Migraciones/API afectadas | `stock_movements.location_id NOT NULL`; `stock_movement_lines.location_id`; API de movimientos |
| Enlace ADR | `SDD-04` §3.4; `DICCIONARIO_DATOS.md` §6.2 |

### DD-04 — Roles globales o por empresa

| Campo | Contenido |
| --- | --- |
| Alternativa elegida | Roles base globales (`owner`, `admin`, `operator`, `viewer`) y roles personalizados por empresa. |
| Alternativas descartadas | (a) Solo roles por empresa, que obliga a recrearlos en cada alta; (b) solo roles globales, que impide personalización. |
| Fundamento | Los roles base cubren el 90 % de los casos y la personalización se reserva a necesidades específicas. |
| Fecha | 2026-11-13 |
| Aprobador | Líder técnico |
| Historias afectadas | US-0102, US-0103 |
| Migraciones/API afectadas | `roles.company_id` nullable; `permission_role`; endpoints de roles/permisos |
| Enlace ADR | `SDD-04` §3.1; `DICCIONARIO_DATOS.md` §3.4 |
| Estado | Aprobada (2026-11-13) |

### DD-05 — Borrado lógico y relaciones de auditoría

| Campo | Contenido |
| --- | --- |
| Alternativa elegida | Baja lógica (`deleted_at`) en maestros y catálogo; movimientos confirmados inmutables; auditoría en `audit_logs`. |
| Alternativas descartadas | (a) Borrado físico, que rompe la trazabilidad histórica; (b) borrado lógico en todas las tablas, que complica reportes de inventario. |
| Fundamento | Conserva la historia sin bloquear el rendimiento de las tablas transaccionales de inventario. |
| Fecha | 2026-11-13 |
| Aprobador | Líder técnico |
| Historias afectadas | Catálogo y trazabilidad |
| Migraciones/API afectadas | `deleted_at` en maestros; `audit_logs`; filtros por defecto en API |
| Enlace ADR | `SDD-04` §3.6; `DICCIONARIO_DATOS.md` §8.4 |

## 5. Bloqueantes por historia

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

## 6. Registro de aprobación

| Fecha | Decisión | Aprobado por | Observaciones |
| --- | --- | --- | --- |
| 2026-11-13 | DD-01 — Unicidad del correo electrónico | Product Owner | Aprobada; habilita US-0101 y US-0102. |
| 2026-11-13 | DD-04 — Roles globales o por empresa | Product Owner | Aprobada; habilita US-0102 y US-0103. |
| 2026-11-13 | ADR-0001 — Autenticación SPA con Sanctum | Product Owner / Líder técnico | Aprobada e implementada en US-0101 (cookie de SPA + CSRF). |
| 2026-11-13 | ADR-0002 — Empresa activa por `X-Company-Id` | Product Owner / Líder técnico | Aprobada; se implementa en US-0104. |
| 2026-11-13 | ADR-0003 — Roles y permisos sin `user_permission` | Product Owner / Líder técnico | Aprobada; se implementa en US-0102 y US-0103. |

> Con estas aprobaciones, US-0101 queda desbloqueada y se implementó el
> 2026-11-13. Los bloqueos de US-0102 y US-0103 quedan resueltos; sus historias
> se implementarán en las siguientes iteraciones del Sprint 1.

> Al aprobar cada decisión, actualizar la columna **Estado** de las tablas resumen,
> completar este registro y reflejar los cambios en `SDD-04`, en el
> `DICCIONARIO_DATOS.md` y en los criterios de aceptación de las historias afectadas.

## 7. Documentos relacionados

- `DOCUMENTACION/SDD/SDD-04_DISENO_BASE_DATOS.md`
- `DOCUMENTACION/SDD/SDD-05_DISENO_API.md`
- `database/diagrams/DER_LOGICO.md`
- `database/dictionaries/DICCIONARIO_DATOS.md`
- `DOCUMENTACION/SDD/ACTA_CIERRE_SPRINT_0.md`
- `DOCUMENTACION/DOCS_origen/DOCUMENTO_MAESTRO_SAAS_INVERSIONES_RP.md` §13
