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
| Pantallas | Login, Inventario, Compras y Conteos migrados; líneas editables con `DataTable`; asistente flotante disponible en todas las pantallas |
| Pruebas | Componentes, error accesible, carga y activación por teclado |

## 3. Evidencia

| Verificación | Resultado |
| --- | --- |
| Frontend | 88 pruebas — OK |
| Lighthouse | 100 accesibilidad, 100 best practices y 100 SEO en `/login`, `/metrics` y `/counts` |
| Lint | Sin errores (advertencias de efectos existentes) |
| Build | Correcto |

### 3.1 Visual refresh

- Cabecera con gradiente, navegación tipo pill y estado activo.
- Tarjetas de métricas con acento de color, elevación y hover.
- Botones, pestañas, tablas y diálogos con transiciones y profundidad.
- Fondo del login con gradiente y tarjeta destacada.
- Animaciones respetando `prefers-reduced-motion`.
- **Asistente flotante** con botón/robot, panel lateral, sugerencias, Escape y foco; oculto en la página `/assistant`.
- Contraste revalidado con Lighthouse 100.

## 4. Pendientes

- Prueba manual con lector de pantalla (NVDA/VoiceOver).
- Validación en dispositivo móvil físico.
- Pruebas de usabilidad y onboarding por rol.

## 5. Conclusión

La brecha principal de UI/UX queda reducida a una base reutilizable y accesible;
no se declara el sistema de diseño terminado.
