# CX / UX / UI — requisitos propuestos v1.1

**Estado:** documentación de referencia. Ningún requisito de esta carpeta está
implementado ni validado con usuarios al 2026-11-15.

| Documento | Contenido | Uso |
| --- | --- | --- |
| [`CX-01_EXPERIENCIA_CLIENTE.md`](./CX-01_EXPERIENCIA_CLIENTE.md) | Recorrido del cliente, onboarding, soporte e indicadores | Referencia para el incremento v1.1 |
| [`UX-01_FLUJOS_DE_USUARIO.md`](./UX-01_FLUJOS_DE_USUARIO.md) | Flujos de login, catálogo, inventario, compras y traslados | Referencia de interacción |
| [`UI-01_SISTEMA_DE_DISENO.md`](./UI-01_SISTEMA_DE_DISENO.md) | Tokens, componentes, accesibilidad y login social | Referencia visual |

## Criterios adoptados en la documentación canónica

- Objetivo **WCAG 2.2 AA**: teclado, foco visible, contraste, etiquetas y zoom.
- Estados explícitos de carga, vacío, error, éxito y sin permisos.
- Confirmación y resumen antes de operaciones de stock.
- No mostrar costos/márgenes sin permiso.
- Componentes reutilizables y diseño responsive.
- Login con correo, Google y Facebook con estados de error comprensibles.

La integración documental está en
[`../SDD/INTEGRACION_V1_1_CX_UX_AUTH.md`](../SDD/INTEGRACION_V1_1_CX_UX_AUTH.md),
el ADR-0006 y `../SDD/SDD-01_ARQUITECTURA_GENERAL.md` §10.2.

> Los valores de tokens, tipografía y componentes son **sugerencias** sujetas a
> validación contra los estilos reales y a pruebas de regresión. No reemplazan
> el sistema visual existente sin aprobación.
