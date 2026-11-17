# README — Incremento transversal UX/UI

**Proyecto:** SaaS Inversiones R&P
**Fecha:** 2026-11-16
**Estado:** Implementación inicial completada — auditoría WCAG formal pendiente
**Plan:** [`SDD/PLAN_INCREMENTO_UX_UI.md`](./SDD/PLAN_INCREMENTO_UX_UI.md)

## 1. Resumen

Se creó la base del sistema de diseño y se aplicaron mejoras de accesibilidad
sin rediseñar las reglas de negocio ni todas las pantallas.

## 2. Entregables

| Área | Entregable |
| --- | --- |
| Tokens | Colores semánticos, radios, foco visible |
| Componentes | `Button`, `TextField`, `SelectField`, `Alert`, `EmptyState`, `PageHeader`, `Dialog`, `ReasonDialog`, `Tabs`, `DataTable` |
| Accesibilidad | Skip link, `aria-invalid`, `aria-describedby`, `aria-busy`, `role="dialog"`, Escape y foco |
| Pantallas | Login, cabecera de Compras, formulario de Conteos y acciones de Inventario migrados a componentes; diálogos accesibles en flujos críticos |
| Pruebas | Componentes, error accesible, carga y activación por teclado |

## 3. Evidencia

| Verificación | Resultado |
| --- | --- |
| Frontend | 86 pruebas — OK |
| Lint | Sin errores (advertencias de efectos existentes) |
| Build | Correcto |

## 4. Pendientes

- Migrar los campos restantes de Inventario y las tablas de líneas de Compras/Conteos.
- Auditoría WCAG 2.2 AA completa con lector de pantalla.
- Pruebas de usabilidad y onboarding por rol.

## 5. Conclusión

La brecha principal de UI/UX queda reducida a una base reutilizable y accesible;
no se declara el sistema de diseño terminado.
