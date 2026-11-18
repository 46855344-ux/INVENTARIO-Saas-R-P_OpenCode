/**
 * Contenido de los QR de inventario.
 *
 * Formato principal: URL absoluta `<origen>/scan?variant=<id>`.
 * Así una cámara de teléfono también abre la aplicación.
 * Se acepta además `RYP:V:<id>` y texto plano (SKU o código de barras).
 */

export const VARIANT_QR_SCHEME = 'RYP:V';

export interface QrPayload {
  /** Id de variante cuando el QR apunta a una variante concreta. */
  variantId: number | null;
  /** Código a buscar por SKU o código de barras cuando no hay id. */
  code: string | null;
}

export function buildVariantQrPayload(variantId: number, origin: string): string {
  const base = origin.replace(/\/+$/, '');

  return `${base}/scan?variant=${variantId}`;
}

export function parseQrPayload(rawValue: string): QrPayload {
  const raw = rawValue.trim();

  if (raw === '') {
    return { variantId: null, code: null };
  }

  // URL de la app (o cualquier URL con el parámetro `variant`).
  const urlMatch = raw.match(/[?&]variant=(\d+)(?:&|$)/);

  if (urlMatch !== null) {
    return { variantId: Number(urlMatch[1]), code: null };
  }

  // Formato corto, útil para etiquetas generadas sin conocer el dominio.
  const schemeMatch = raw.match(/^RYP:V:(\d+)$/i);

  if (schemeMatch !== null) {
    return { variantId: Number(schemeMatch[1]), code: null };
  }

  return { variantId: null, code: raw };
}
