import { useEffect, useId, useRef, useState } from 'react';
import { toDataURL } from 'qrcode';
import type { ProductVariant } from '../../types';
import { buildVariantQrPayload } from '../scanner/qrPayload';

interface VariantQrDialogProps {
  variant: ProductVariant | null;
  productName: string;
  onClose: () => void;
}

function escapeHtml(value: string): string {
  return value
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');
}

export function VariantQrDialog({ variant, productName, onClose }: VariantQrDialogProps) {
  const titleId = useId();
  const closeRef = useRef<HTMLButtonElement>(null);
  const [dataUrl, setDataUrl] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const payload = variant
    ? buildVariantQrPayload(variant.id, window.location.origin)
    : '';

  useEffect(() => {
    if (!variant) {
      return;
    }

    let active = true;
    setDataUrl(null);
    setError(null);

    toDataURL(payload, {
      width: 320,
      margin: 1,
      errorCorrectionLevel: 'M',
      color: { dark: '#111827', light: '#ffffff' },
    })
      .then((url) => {
        if (active) {
          setDataUrl(url);
        }
      })
      .catch(() => {
        if (active) {
          setError('No se pudo generar el QR. Intente nuevamente.');
        }
      });

    return () => {
      active = false;
    };
  }, [variant, payload]);

  useEffect(() => {
    if (!variant) {
      return;
    }

    const previous = document.activeElement as HTMLElement | null;
    closeRef.current?.focus();

    const handleKey = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        onClose();
      }
    };

    document.addEventListener('keydown', handleKey);

    return () => {
      document.removeEventListener('keydown', handleKey);
      previous?.focus();
    };
  }, [variant, onClose]);

  if (!variant) {
    return null;
  }

  const downloadName = `qr-${variant.sku.replace(/[^a-z0-9_-]+/gi, '-')}.png`;

  const handlePrint = () => {
    if (!dataUrl) {
      return;
    }

    const win = window.open('', '_blank', 'width=440,height=620');

    if (!win) {
      setError('El navegador bloqueó la ventana de impresión.');
      return;
    }

    const safeProduct = escapeHtml(productName);
    const safeVariant = escapeHtml(variant.name);
    const safeSku = escapeHtml(variant.sku);

    win.document.write(`<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<title>QR ${safeSku}</title>
<style>
  * { box-sizing: border-box; }
  body { margin: 0; padding: 24px; font-family: system-ui, sans-serif; color: #111827; }
  .label { width: 280px; margin: 0 auto; padding: 16px; border: 1px solid #d1d5db; border-radius: 12px; text-align: center; }
  .label img { width: 240px; height: 240px; display: block; margin: 0 auto 8px; }
  .product { margin: 0; font-size: 15px; font-weight: 700; }
  .variant { margin: 2px 0 0; font-size: 13px; color: #4b5563; }
  .sku { margin: 8px 0 0; font-family: ui-monospace, monospace; font-size: 12px; letter-spacing: 0.04em; }
</style>
</head>
<body>
  <div class="label">
    <img src="${dataUrl}" alt="Código QR" />
    <p class="product">${safeProduct}</p>
    <p class="variant">${safeVariant}</p>
    <p class="sku">${safeSku}</p>
  </div>
</body>
</html>`);
    win.document.close();
    win.focus();
    win.print();
  };

  return (
    <div
      className="dialog-backdrop"
      role="presentation"
      onClick={(event) => {
        if (event.target === event.currentTarget) {
          onClose();
        }
      }}
    >
      <div
        className="dialog qr-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby={titleId}
      >
        <h2 id={titleId}>QR de la variante</h2>

        {error !== null && (
          <p className="form-error" role="alert">
            {error}
          </p>
        )}

        {error === null && dataUrl === null && (
          <p className="loading">Generando QR…</p>
        )}

        {dataUrl !== null && (
          <img
            className="qr-image"
            src={dataUrl}
            alt={`Código QR de ${productName}, ${variant.name}`}
            width={320}
            height={320}
          />
        )}

        <dl className="qr-meta">
          <div>
            <dt>Producto</dt>
            <dd>{productName}</dd>
          </div>
          <div>
            <dt>Variante</dt>
            <dd>{variant.name}</dd>
          </div>
          <div>
            <dt>SKU</dt>
            <dd>{variant.sku}</dd>
          </div>
        </dl>

        <p className="hint qr-payload">Contenido: {payload}</p>

        <div className="dialog-actions">
          <button
            type="button"
            className="button-secondary"
            onClick={onClose}
            ref={closeRef}
          >
            Cerrar
          </button>
          <button
            type="button"
            className="button-secondary"
            onClick={handlePrint}
            disabled={dataUrl === null}
          >
            Imprimir etiqueta
          </button>
          <a
            className="button-primary qr-download"
            href={dataUrl ?? '#'}
            download={downloadName}
            aria-disabled={dataUrl === null}
            onClick={(event) => {
              if (dataUrl === null) {
                event.preventDefault();
              }
            }}
          >
            Descargar PNG
          </a>
        </div>
      </div>
    </div>
  );
}
