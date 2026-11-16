# UI-01 — Sistema de diseño e interfaz

**Proyecto:** SaaS Inversiones R&P | **Versión:** 1.0 | **Estado:** especificación visual propuesta  
**Tecnología prevista:** React; comprobar librerías y estilos existentes antes de aplicar cambios.  
**Referencias:** SDD-01, UX-01, Documento Maestro (M01–M12), US-UX01 y US-0115.

## 1. Objetivos

Establecer una UI consistente, legible y accesible para usuarios de tienda/almacén, sin modificar reglas de negocio. **Los valores de diseño siguientes son sugeridos** y deben contrastarse con el branding y los componentes reales del repositorio.

## 2. Principios visuales

- Fondo claro y jerarquía tipográfica legible.
- Misma convención de botones, estados, formularios y tablas.
- Elementos táctiles suficientes para uso en celular, sin sacrificar densidad de información en desktop.
- Significado independiente del color (texto/icono/estado).
- Acción crítica siempre acompañada de revisión y confirmación.
- Temas estacionales permitidos solo si preservan contraste, consistencia y accesibilidad.

## 3. Tokens sugeridos (NO definitivos)

```css
:root {
  --color-bg: #f8fafc;
  --color-surface: #ffffff;
  --color-text: #172033;
  --color-text-muted: #475569;
  --color-primary: #225ac7;
  --color-primary-hover: #184cae;
  --color-success: #166534;
  --color-warning: #92400e;
  --color-error: #b42318;
  --color-border: #cbd5e1;
  --radius-sm: 6px;
  --radius-md: 10px;
  --radius-lg: 16px;
  --space-1: 4px;
  --space-2: 8px;
  --space-3: 12px;
  --space-4: 16px;
  --space-6: 24px;
  --space-8: 32px;
  --focus-ring: 0 0 0 3px rgba(34,90,199,.35);
}
```

**Antes de adoptar:** comprobar contraste WCAG según tamaño de texto y componente; revisar compatibilidad con Tailwind, CSS Modules u otra tecnología realmente presente. No reemplazar estilos globales sin evaluación de regresión.

## 4. Tipografía y composición

- Fuente sistema o tipografía aprobada; evitar depender de cargas externas para iniciar sesión.
- Tamaños de referencia: texto 16px; secundario 14px; títulos 20–28px; mantener zoom y escalado del navegador.
- Grilla adaptable: sidebar desktop, navegación compacta móvil; formularios verticales cuando falta espacio.
- Espaciados consistentes y máximo ancho adecuado en formularios de acceso/edición.

## 5. Componentes obligatorios

| Componente | Variantes/estados | Criterio UI |
|---|---|---|
| Button | primario, secundario, peligro, disabled, loading | etiqueta explícita, foco visible |
| TextField | normal, requerido, error, lectura | etiqueta persistente, ayuda/error asociado |
| Select/Autocomplete | carga, vacío, seleccionado, error | búsqueda de productos y variantes accesible |
| DataTable | orden, filtros, paginación, vacío | encabezados semánticos y lectura móvil |
| MetricCard | valor, unidad, período, estado | sin spinner permanente ni datos ambiguos |
| AlertBanner | información, advertencia, error, éxito | no usar color como única señal |
| Modal/Confirm | abierto, procesamiento, cancelado | manejo de foco y Escape donde proceda |
| Toast/Notification | éxito, fallo, información | no ocultar información crítica solo en toast |
| Breadcrumb | navegación | etiqueta actual y retorno coherente |
| AuthProviderButton | Google/Facebook | texto identificable y marca conforme a guías del proveedor |

## 6. Pantallas del alcance

| Pantalla | Contenido mínimo | Estado del trabajo |
|---|---|---|
| Login | correo, contraseña, Google, Facebook, recuperación y errores | ampliación propuesta |
| Dashboard | métricas de acceso permitido | Sprint 6 planificado |
| Productos/categorías | tabla, filtros, crear/editar | revisar Sprint 2 |
| Variantes/códigos | atributos, códigos y duplicidades | revisar Sprint 2 |
| Unidades/ubicaciones | equivalencias y jerarquía | revisar Sprint 3 |
| Entradas/salidas | producto, stock, unidad, confirmación | revisar Sprint 4 |
| Proveedores/compras | historial, costos según rol | revisar Sprint 5 |
| Traslados | origen, destino, estado, confirmación | revisar Sprint 5 |
| Alertas/conteos | prioridad y revisión autorizada | Sprint 6–7 planificado |

## 7. Especificación UI de login

1. Título «Iniciar sesión» y explicación breve.
2. Campos `Correo electrónico` y `Contraseña`, con mensajes junto a cada campo.
3. CTA principal `Iniciar sesión`.
4. Separador visual «O continúa con».
5. Botones diferenciados `Continuar con Google` y `Continuar con Facebook`.
6. Estado de procesamiento y retorno de OAuth; no abrir varias solicitudes con clic repetido.
7. Error neutro en caso de cancelación, proveedor no disponible o cuenta no vinculada.
8. Pantalla y enlaces ajustados a política de creación/vinculación de cuentas aprobada.

**Logos:** respetar guía de identidad vigente de Google y Meta; no generar marcas imprecisas a mano.

## 8. Formularios de inventario

- Mostrar nombre, variante y unidad antes del campo cantidad.
- Mensajes de error junto al campo y resumen de error si hay varios.
- Mostrar el stock por ubicación antes de salida.
- Botón `Confirmar salida` diferenciado de `Guardar borrador` (solo si el backend admite borradores).
- Tras guardar, informar operación creada y estado confirmado; no simular éxito antes de respuesta real.

## 9. Métricas — orden solicitado para el proyecto

**Propuesta de presentación:** Total de productos → Detalles → Productos agotados → Stock mínimo → Valor total → Más salidas → Sin movimiento → Resumen. Las métricas siguientes del backlog (rotación, entradas, salidas) se mantienen disponibles conforme al diseño aprobado. **No confundir preferencia de orden con una funcionalidad ya implementada.**

Establecer estados explícitos: cargando, sin datos, error y actualizado. No usar un spinner que oculta resultados ya disponibles indefinidamente.

## 10. Accesibilidad

Objetivo **WCAG 2.2 nivel AA**: navegación por teclado, orden de foco, nombres accesibles, contraste, mensajes asociados, dimensiones táctiles donde aplique, zoom sin pérdida de contenido y anuncios de estado no intrusivos. Verificar manualmente además de escáner automático.

## 11. Estructura sugerida de componentes

```text
src/
  components/
    ui/              # Button, Input, Alert, Dialog, Table
    auth/            # SocialLoginButtons, AuthFeedback
    inventory/       # StockSummary, MovementReview
    layout/          # Sidebar, Header, Breadcrumbs
  features/
    products/
    inventory/
    purchases/
    transfers/
```

**Orientativa:** respetar la organización real que OpenCode encuentre en el proyecto.

## 12. Gobernanza y Definition of Done UI

- Diseño/requisitos asociados a historia identificada.
- Estados `loading/error/empty/success` definidos.
- Validación de permisos frontend y backend (frontend no es barrera de seguridad).
- Pruebas responsive y teclado.
- Capturas o evidencia visual aprobadas.
- Sin regresión de inventario ni procesos anteriores.

## 13. Checklist de implementación

- [ ] Auditar estilos y librerías existentes.
- [ ] Aprobar tokens y contraste.
- [ ] Crear o adaptar componentes reutilizables.
- [ ] Probar en desktop y móvil.
- [ ] Documentar patrones de formularios críticos.
- [ ] Vincular hallazgos a historias US-UX01…07 y AUTH.
