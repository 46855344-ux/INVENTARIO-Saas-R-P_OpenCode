# Auditoría de accesibilidad UX/UI

**Proyecto:** SaaS Inversiones R&P
**Fecha:** 2026-11-16
**Alcance:** componentes y flujos migrados
**Estado:** Revisión manual inicial — **no certifica WCAG 2.2 AA**

## 1. Checklist aplicado

| Criterio | Resultado |
| --- | --- |
| Idioma del documento (`lang="es"`) | Cumple |
| Landmark principal y skip link | Cumple |
| Foco visible global | Cumple |
| Navegación por teclado en botones/pestañas | Cumple en componentes |
| Diálogos con `role="dialog"`, `aria-modal`, Escape y foco inicial | Cumple |
| Etiquetas de formulario asociadas | Cumple en componentes migrados |
| Errores con `aria-invalid` y `aria-describedby` | Cumple en componentes migrados |
| Estados de carga con `aria-busy` | Cumple en botones |
| Mensajes de éxito/error con `role` | Cumple |
| Tablas con encabezados `th scope="col"` | Cumple en `DataTable` |
| Tablas responsive con desplazamiento | Cumple |
| Contraste de tokens | Revisión visual; falta medición formal |
| Lector de pantalla | **Pendiente** |
| Auditoría automatizada (axe/Lighthouse) | **Pendiente** |
| Controles de línea (compras/conteos) | `aria-label` por fila; migración visual a componentes pendiente |

## 2. Brechas

1. Los controles de línea de Compras/Conteos ya tienen `aria-label`; falta migrarlos visualmente a componentes.
2. Falta anunciar cambios de listados asíncronos con `aria-live`.
3. No se ejecutó axe ni una prueba con lector de pantalla.
4. No se midió contraste con herramienta dedicada.
5. No se validó zoom al 200 % ni anchos móviles reales.

## 3. Próximos pasos

- Migrar los controles de línea restantes.
- Añadir `aria-live` a resultados de listados.
- Ejecutar axe/Lighthouse y registrar hallazgos.
- Prueba manual con teclado y lector de pantalla.
- Documentar excepciones y correcciones.

> No se declara conformidad WCAG 2.2 AA hasta completar las brechas.
