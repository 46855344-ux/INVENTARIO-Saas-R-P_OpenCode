import { describe, expect, it } from 'vitest';
import {
  buildVariantQrPayload,
  parseQrPayload,
  VARIANT_QR_SCHEME,
} from '../src/features/scanner/qrPayload';

describe('payload de QR de variantes', () => {
  it('construye una URL absoluta al escáner', () => {
    expect(buildVariantQrPayload(42, 'https://inventario.ejemplo.com')).toBe(
      'https://inventario.ejemplo.com/scan?variant=42',
    );
  });

  it('no duplica la barra final del origen', () => {
    expect(buildVariantQrPayload(7, 'http://localhost:5173/')).toBe(
      'http://localhost:5173/scan?variant=7',
    );
  });

  it('reconoce la URL generada', () => {
    expect(parseQrPayload('https://inventario.ejemplo.com/scan?variant=42')).toEqual({
      variantId: 42,
      code: null,
    });
  });

  it('reconoce el esquema corto', () => {
    expect(parseQrPayload(`${VARIANT_QR_SCHEME}:15`)).toEqual({
      variantId: 15,
      code: null,
    });
    expect(parseQrPayload('ryp:v:15')).toEqual({ variantId: 15, code: null });
  });

  it('trata el texto plano como SKU o código de barras', () => {
    expect(parseQrPayload('  E2E-1 ')).toEqual({ variantId: null, code: 'E2E-1' });
    expect(parseQrPayload('7791234567890')).toEqual({
      variantId: null,
      code: '7791234567890',
    });
  });

  it('devuelve vacío para un valor en blanco', () => {
    expect(parseQrPayload('   ')).toEqual({ variantId: null, code: null });
  });
});
