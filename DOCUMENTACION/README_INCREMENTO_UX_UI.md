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
| Componentes | `Button`, `TextField`, `SelectField`, `Alert`, `EmptyState`, `PageHeader` |
| Accesibilidad | Skip link, `aria-invalid`, `aria-describedby`, `aria-busy`, roles de estado |
| Pantallas | Login migrado a componentes; cuenta y nuevas pantallas reutilizan patrones |
| Pruebas | Componentes, error accesible, carga y activación por teclado |

## 3. Evidencia

| Verificación | Resultado |
| --- | --- |
| Frontend | 82 pruebas — OK |
| Lint | Sin errores (advertencias de efectos existentes) |
| Build | Correcto |

## 4. Pendientes

- Migrar el resto de formularios y tablas a los componentes.
- Auditoría WCAG 2.2 AA completa con lector de pantalla.
- Pruebas responsive y de usabilidad con usuarios.
- Onboarding por rol y ayuda contextual.

## 5. Conclusión

La brecha principal de UI/UX queda reducida a una base reutilizable y accesible;
no se declara el sistema de diseño terminado.
