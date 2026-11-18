# Plan — Incremento QR y escáner (Sprint 10)

**Proyecto:** SaaS Inversiones R&P
**Fecha:** 2026-11-18
**Estado:** En ejecución
**Alcance:** lectura de QR con cámara y generación de QR por variante de producto

---

## 1. Objetivo

Permitir identificar un producto del inventario escaneando un QR desde el
navegador y entregar un QR único por cada variante creada, sin depender de
hardware dedicado.

## 2. Decisiones de diseño

| Tema | Decisión |
| --- | --- |
| Unidad escaneable | La **variante** (`product_variants`), porque el stock y los movimientos se registran por variante. Un producto con una sola variante equivale a un QR por producto. |
| Contenido del QR | URL `${origin}/scan?variant=<id>`. También se acepta el formato `RYP:V:<id>` y texto plano (SKU o código de barras). |
| Generación | En el cliente con `qrcode`. No se persiste el QR: se deriva del `id` de la variante, por lo que no puede desincronizarse. |
| Lectura | `jsqr` sobre frames de `getUserMedia` (sin subir imágenes al servidor). |
| Permisos | Escanear requiere `catalog.view`; el stock se muestra solo con `inventory.view`. |
| Backend | Sin cambios de esquema ni endpoints nuevos: se reutilizan `/v1/variants`, `/v1/products/{id}` y `/v1/inventory/stocks`. |

## 3. Experiencia de usuario

1. **Catálogo → Productos → Variantes:** cada fila tiene la acción `QR`.
2. Al crear una variante, el diálogo de QR se abre automáticamente.
3. El diálogo muestra el QR, producto, variante y SKU, con acciones
   **Descargar PNG** e **Imprimir etiqueta**.
4. Nueva página **Escáner** (`/scan`):
   - Botón para activar la cámara con vista previa y encuadre.
   - Código detectado → ficha con producto, variante, SKU, precio y stock por
     ubicación.
   - Entrada manual como alternativa si no hay cámara o el permiso se deniega.
   - Mensajes claros para permiso denegado, cámara ausente o código desconocido.

## 4. Entregables técnicos

- `frontend/src/features/scanner/qrPayload.ts`: construir/parsear el contenido.
- `frontend/src/features/scanner/ScannerPage.tsx`: cámara, decodificación y ficha.
- `frontend/src/features/products/VariantQrDialog.tsx`: QR, descarga e impresión.
- `frontend/src/services/catalog.ts`: `getVariant` y `getProduct`.
- Ruta `/scan` y enlace **Escáner** en la navegación.
- Estilos en `styles/index.css`.
- Pruebas Vitest del payload y Cypress del flujo QR + búsqueda manual.

## 5. Pruebas

| Nivel | Caso |
| --- | --- |
| Unitaria | `buildVariantQrPayload` usa el origen y el id. |
| Unitaria | `parseQrPayload` reconoce URL, esquema y texto plano. |
| E2E | Abrir QR de una variante y ver el diálogo con el SKU. |
| E2E | Buscar `E2E-1` en el escáner y ver producto y stock. |
| Manual | Permiso de cámara concedido/denegado y lectura de un QR impreso. |

## 6. Riesgos y límites

- `getUserMedia` exige contexto seguro: funciona en `localhost` y HTTPS; en
  HTTP por IP de red local el navegador bloquea la cámara.
- La calidad de lectura depende de la iluminación y del enfoque de la cámara.
- El QR contiene el origen de la instancia; si cambia el dominio, las etiquetas
  impresas dejan de abrir la app (el escáner interno las resuelve igual por id).
- No se almacenan imágenes ni se envían frames al backend.

## 7. Criterio de cierre

- `npm run build`, `npm run lint` y `npm test` sin errores.
- Cypress 3/3 más el nuevo spec de escáner.
- Documentación de cierre en `DOCUMENTACION/README_SPRINT_10.md`.
