# Plan — Incremento transversal UX/UI

**Proyecto:** SaaS Inversiones R&P
**Fecha:** 2026-11-16
**Estado:** Implementado — auditoría WCAG completa pendiente

## 1. Objetivo

Cerrar la brecha de UI/UX pendiente: componentes reutilizables, estados
accesibles y navegación por teclado, sin rediseñar reglas de negocio.

## 2. Alcance

| Área | Entregable |
| --- | --- |
| Tokens | Colores semánticos, foco visible, espaciado y radios |
| Componentes | `Button`, `TextField`, `SelectField`, `Alert`, `EmptyState`, `PageHeader` |
| Accesibilidad | Skip link, `aria-invalid`, `aria-describedby`, estados `aria-live`, foco visible |
| Pantallas | Login y cuenta migradas a componentes; nuevas pantallas los reutilizan |
| Pruebas | Componentes, errores accesibles y skip link |
| Documentación | README del incremento, ADR-0006 actualizado |

## 3. Fuera de alcance

- Rediseño visual completo de catálogo, inventario, compras y conteos.
- Certificación WCAG formal; se aplican criterios AA y queda auditoría manual.
- Librería UI externa o cambio de stack.

## 4. Criterios de aceptación

- Componentes reutilizables con estados normal/error/disabled/loading.
- Login y cuenta usan los componentes y conservan pruebas.
- Skip link funcional y foco visible en controles.
- Tests de frontend, lint y build en verde.
