# Cierre — Sprint 10: QR y escáner

**Proyecto:** SaaS Inversiones R&P
**Fecha:** 2026-11-18
**Alcance:** lectura de QR con cámara y QR por variante de producto
**Estado:** Implementado y validado en local (frontend + Docker + Cypress)

---

## 1. Qué se entregó

| Elemento | Ubicación |
| --- | --- |
| Payload del QR (construir/parsear) | `frontend/src/features/scanner/qrPayload.ts` |
| Página del escáner | `frontend/src/features/scanner/ScannerPage.tsx` |
| Diálogo de QR, descarga e impresión | `frontend/src/features/products/VariantQrDialog.tsx` |
| Consultas de catálogo | `frontend/src/services/catalog.ts` (`getVariant`, `getProduct`) |
| Ruta `/scan` | `frontend/src/app/App.tsx` |
| Enlace **Escáner** | `frontend/src/layouts/AppLayout.tsx` |
| Acción **QR** por variante y apertura automática | `frontend/src/features/products/VariantsSection.tsx` |
| Alta de producto → detalle de variantes | `frontend/src/features/products/ProductsSection.tsx` |
| Estilos | `frontend/src/styles/index.css` |
| Pruebas unitarias | `frontend/tests/qrPayload.test.ts` |
| Prueba E2E | `tests/e2e/scanner.cy.ts` |
| Plan | `DOCUMENTACION/SDD/PLAN_SPRINT_10_QR_ESCANER.md` |

## 2. Cómo funciona

### QR por variante

- Cada variante tiene un QR con la URL `${origin}/scan?variant=<id>`.
- En **Catálogo → Productos → Variantes** cada fila tiene la acción **QR**.
- Al crear una variante, el QR se abre automáticamente.
- El diálogo permite **Descargar PNG** e **Imprimir etiqueta** (QR + producto + variante + SKU).
- No se persiste el QR: se deriva del id de la variante, así que nunca se desincroniza.

### Escáner

- **Escáner** en la navegación (permiso `catalog.view`).
- **Activar cámara** pide permiso al navegador y decodifica QR con `jsqr`.
- Al leer un código:
  - Si es una URL con `variant` o `RYP:V:<id>`, busca por id.
  - Si es texto, busca por SKU o código de barras.
- Muestra producto, variante, SKU, código de barras, precio y stock por ubicación
  (el stock requiere `inventory.view`).
- La entrada manual permite buscar sin cámara.
- El enlace `/scan?variant=<id>` resuelve el código automáticamente al abrirse.

## 3. Evidencia

| Verificación | Resultado |
| --- | --- |
| `npm run lint` | Sin errores (solo advertencias `set-state-in-effect` ya presentes) |
| `npm test` | 24 archivos / 94 pruebas |
| `npm run build` | Correcto; escáner en chunk separado (138 kB) |
| `npm audit --omit=dev` | 0 vulnerabilidades |
| Cypress contra Docker | 4/4 specs (`scanner.cy.ts` incluido) |
| Docker frontend | Imagen reconstruida con las dependencias nuevas |

## 4. Limitaciones

- `getUserMedia` exige contexto seguro: funciona en `localhost` y HTTPS; en HTTP
  por IP de red local el navegador bloquea la cámara.
- La lectura depende de la iluminación y del enfoque de la cámara del dispositivo.
- El QR incluye el origen de la instancia; si cambia el dominio, las etiquetas
  impresas ya no abren la app (el escáner interno las resuelve igual por id).
- No hay autenticación por QR ni etiquetas preimpresas en lote.
- Electron de Cypress cerró una pestaña en esta máquina; la suite se ejecutó con
  Chrome (`--browser chrome`).

## 5. Próximos pasos sugeridos

- Impresión masiva de etiquetas por producto o ubicación.
- Escaneo continuo para recepciones y conteos (cantidad + variante en una pasada).
- Lectura de códigos de barras 1D además de QR.
- Integración del nuevo spec en GitHub Actions.

## 6. Cómo probarlo

1. Ingresar como `admin@empresa-demo.test` / `password`.
2. Ir a **Catálogo → Productos → Variantes** del producto deseado.
3. Crear una variante: el QR aparece automáticamente.
4. Descargar el PNG o imprimir la etiqueta.
5. Ir a **Escáner**, activar la cámara y apuntar al QR.
6. Alternativa sin cámara: escribir `E2E-1` y pulsar **Buscar**.
