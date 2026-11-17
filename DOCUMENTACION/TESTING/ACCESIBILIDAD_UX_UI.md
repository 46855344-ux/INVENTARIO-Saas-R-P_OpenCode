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
| Contraste de tokens | Cumple: `--color-muted` ajustado; Lighthouse 100 |
| Lector de pantalla | **Pendiente** |
| Auditoría automatizada (Lighthouse/axe) | Cumple en `/login`, `/metrics` y `/counts` (100) |
| Zoom al 200 % y anchos móviles | Cumple en `/metrics`: sin desborde horizontal; tablas con scroll |
| Controles de línea (compras/conteos) | Migrados a `DataTable` con `aria-label` por fila |

## 1.1 Resultados Lighthouse

| Página | Accesibilidad | Best Practices | SEO |
| --- | --- | --- | --- |
| `/login` | 100 | 100 | 100 |
| `/metrics` | 100 | 100 | 100 |
| `/counts` | 100 | 100 | 100 |

Se corrigió el contraste de `--color-muted` (`#6b7280` → `#4b5563`) y se agregó
`robots.txt` para eliminar el único fallo de SEO.

## 2. Brechas

1. Los controles de línea ya están migrados a `DataTable` y etiquetados con `aria-label`.
2. Falta anunciar cambios de listados asíncronos con `aria-live`.
3. Falta prueba manual con lector de pantalla.
4. Validación en dispositivo móvil físico pendiente; zoom 200 % simulado sin desborde.
5. Falta prueba de usabilidad con usuarios.

> **Validación realizada:** Lighthouse 100 en `/login`, `/metrics` y `/counts`;
> inspección del árbol de accesibilidad (main, headings y tablas); zoom 200 %
> simulado sin desborde horizontal. **No** se dispone de lector de pantalla real
> ni dispositivo móvil físico en este entorno.

## 3. Próximos pasos

- Migrar los controles de línea restantes.
- Añadir `aria-live` a resultados de listados.
- Ejecutar axe/Lighthouse y registrar hallazgos.
- Prueba manual con teclado y lector de pantalla.
- Documentar excepciones y correcciones.

> No se declara conformidad WCAG 2.2 AA hasta completar las brechas.
