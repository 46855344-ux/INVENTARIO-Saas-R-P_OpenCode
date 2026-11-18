import { useCallback, useEffect, useRef, useState } from 'react';
import type { FormEvent } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import jsQR from 'jsqr';
import { useAuth } from '../auth/AuthContext';
import { getProduct, getVariant, listAllVariants } from '../../services/catalog';
import { resolveApiError } from '../../services/errors';
import { listStock } from '../../services/inventory';
import type { ProductVariant, StockBalance } from '../../types';
import { parseQrPayload } from './qrPayload';

type CameraState = 'idle' | 'starting' | 'active' | 'error';

interface ScanResult {
  variant: ProductVariant;
  productName: string;
  stocks: StockBalance[];
}

const DECODE_INTERVAL_MS = 120;
const MAX_DECODE_WIDTH = 480;

function describeCameraError(error: unknown): string {
  if (error instanceof DOMException) {
    switch (error.name) {
      case 'NotAllowedError':
      case 'SecurityError':
        return 'Permiso de cámara denegado. Habilitalo desde el candado de la barra de direcciones y volvé a intentar.';
      case 'NotFoundError':
      case 'OverconstrainedError':
        return 'No se encontró ninguna cámara disponible en este dispositivo.';
      case 'NotReadableError':
        return 'La cámara está siendo usada por otra aplicación.';
      default:
        return 'No se pudo iniciar la cámara.';
    }
  }

  return 'No se pudo iniciar la cámara.';
}

function formatQuantity(value: string): string {
  const number = Number(value);

  return Number.isFinite(number)
    ? number.toLocaleString('es-AR', { maximumFractionDigits: 4 })
    : value;
}

function formatPrice(value: string): string {
  const number = Number(value);

  return Number.isFinite(number)
    ? number.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    : value;
}

export function ScannerPage() {
  const { hasPermission } = useAuth();
  const canViewCatalog = hasPermission('catalog.view');
  const canViewStock = hasPermission('inventory.view');

  const videoRef = useRef<HTMLVideoElement>(null);
  const canvasRef = useRef<HTMLCanvasElement | null>(null);
  const streamRef = useRef<MediaStream | null>(null);
  const frameRef = useRef<number | null>(null);
  const busyRef = useRef(false);
  const handledParamRef = useRef<string | null>(null);

  const [cameraState, setCameraState] = useState<CameraState>('idle');
  const [cameraError, setCameraError] = useState<string | null>(null);
  const [lookupError, setLookupError] = useState<string | null>(null);
  const [lastCode, setLastCode] = useState<string | null>(null);
  const [result, setResult] = useState<ScanResult | null>(null);
  const [busy, setBusy] = useState(false);
  const [manualCode, setManualCode] = useState('');

  const [searchParams] = useSearchParams();
  const variantParam = searchParams.get('variant');

  const stopCamera = useCallback(() => {
    if (frameRef.current !== null) {
      cancelAnimationFrame(frameRef.current);
      frameRef.current = null;
    }

    const stream = streamRef.current;
    streamRef.current = null;
    stream?.getTracks().forEach((track) => track.stop());

    if (videoRef.current !== null) {
      videoRef.current.srcObject = null;
    }

    setCameraState((current) => (current === 'error' ? current : 'idle'));
  }, []);

  useEffect(() => stopCamera, [stopCamera]);

  const handleCode = useCallback(
    async (rawValue: string) => {
      const raw = rawValue.trim();

      if (raw === '' || busyRef.current) {
        return;
      }

      busyRef.current = true;
      stopCamera();
      setBusy(true);
      setLookupError(null);
      setLastCode(raw);
      setResult(null);

      const payload = parseQrPayload(raw);

      try {
        let variant: ProductVariant | null = null;

        if (payload.variantId !== null) {
          variant = await getVariant(payload.variantId);
        } else if (payload.code !== null) {
          const page = await listAllVariants({ search: payload.code, per_page: 20 });
          const code = payload.code.toLowerCase();
          variant =
            page.data.find((item) => item.sku.toLowerCase() === code) ??
            page.data.find((item) => (item.barcode ?? '').toLowerCase() === code) ??
            page.data[0] ??
            null;
        }

        if (variant === null) {
          setLookupError('No se encontró ningún producto con ese código.');
          return;
        }

        let productName = '';
        try {
          productName = (await getProduct(variant.product_id)).name;
        } catch {
          productName = '';
        }

        let stocks: StockBalance[] = [];
        if (canViewStock) {
          try {
            const stockPage = await listStock({ variant_id: variant.id, per_page: 100 });
            stocks = stockPage.data;
          } catch {
            stocks = [];
          }
        }

        setResult({ variant, productName, stocks });
      } catch (error) {
        setLookupError(resolveApiError(error).message);
      } finally {
        busyRef.current = false;
        setBusy(false);
      }
    },
    [canViewStock, stopCamera],
  );

  useEffect(() => {
    if (variantParam === null || !/^\d+$/.test(variantParam)) {
      return;
    }

    if (handledParamRef.current === variantParam) {
      return;
    }

    handledParamRef.current = variantParam;
    void handleCode(`RYP:V:${variantParam}`);
  }, [variantParam, handleCode]);

  const startCamera = async () => {
    setCameraError(null);
    setLookupError(null);

    if (navigator.mediaDevices?.getUserMedia === undefined) {
      setCameraState('error');
      setCameraError('Este navegador no permite acceder a la cámara.');
      return;
    }

    setCameraState('starting');

    try {
      const stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: { ideal: 'environment' } },
        audio: false,
      });

      streamRef.current = stream;
      const video = videoRef.current;

      if (video === null) {
        stream.getTracks().forEach((track) => track.stop());
        setCameraState('error');
        setCameraError('No se pudo preparar la vista previa de la cámara.');
        return;
      }

      video.srcObject = stream;
      await video.play();
      setCameraState('active');

      let lastDecode = 0;

      const loop = (time: number) => {
        if (streamRef.current === null) {
          return;
        }

        const currentVideo = videoRef.current;
        if (currentVideo !== null && time - lastDecode > DECODE_INTERVAL_MS) {
          lastDecode = time;

          if (currentVideo.readyState >= 2 && currentVideo.videoWidth > 0) {
            const scale = Math.min(1, MAX_DECODE_WIDTH / currentVideo.videoWidth);
            const width = Math.round(currentVideo.videoWidth * scale);
            const height = Math.round(currentVideo.videoHeight * scale);
            const canvas = canvasRef.current ?? document.createElement('canvas');
            canvasRef.current = canvas;
            canvas.width = width;
            canvas.height = height;

            const context = canvas.getContext('2d', { willReadFrequently: true });

            if (context !== null) {
              context.drawImage(currentVideo, 0, 0, width, height);
              const image = context.getImageData(0, 0, width, height);
              const code = jsQR(image.data, image.width, image.height, {
                inversionAttempts: 'dontInvert',
              });

              if (code !== null && code.data !== '') {
                void handleCode(code.data);
                return;
              }
            }
          }
        }

        frameRef.current = requestAnimationFrame(loop);
      };

      frameRef.current = requestAnimationFrame(loop);
    } catch (error) {
      streamRef.current?.getTracks().forEach((track) => track.stop());
      streamRef.current = null;
      setCameraState('error');
      setCameraError(describeCameraError(error));
    }
  };

  const handleManualSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    void handleCode(manualCode);
  };

  const resetScan = () => {
    setResult(null);
    setLookupError(null);
    setLastCode(null);
    setManualCode('');
  };

  const stockTotal = result?.stocks.reduce((sum, item) => sum + Number(item.quantity), 0) ?? 0;

  if (!canViewCatalog) {
    return (
      <section className="users-page">
        <div className="alert alert-warning">
          No tiene permiso para consultar el catálogo de esta empresa.
        </div>
      </section>
    );
  }

  return (
    <section className="users-page scanner-page">
      <header className="users-header">
        <div>
          <p className="eyebrow">Escáner</p>
          <h1>Escanear código QR</h1>
          <p className="lead">
            Apuntá la cámara al QR de una variante para ver el producto y su stock.
          </p>
        </div>
      </header>

      <div className="scanner-grid">
        <div className="scanner-panel">
          <div className={`scanner-camera scanner-camera-${cameraState}`}>
            <video
              ref={videoRef}
              className="scanner-video"
              muted
              playsInline
              aria-label="Vista previa de la cámara"
            />
            {cameraState === 'active' && <span className="scanner-frame" aria-hidden="true" />}
            {cameraState !== 'active' && (
              <div className="scanner-placeholder">
                <p>
                  {cameraState === 'starting'
                    ? 'Iniciando cámara…'
                    : 'La cámara está apagada.'}
                </p>
                {cameraState === 'idle' && (
                  <p className="hint">
                    Necesitás dar permiso de cámara al navegador. Funciona en localhost y
                    sitios HTTPS.
                  </p>
                )}
              </div>
            )}
          </div>

          {cameraError !== null && (
            <p className="form-error" role="alert">
              {cameraError}
            </p>
          )}

          <p className="sr-status" aria-live="polite">
            {cameraState === 'active'
              ? 'Cámara activa, buscando un código QR.'
              : cameraState === 'starting'
                ? 'Iniciando cámara.'
                : 'Cámara inactiva.'}
          </p>

          <div className="scanner-actions">
            {cameraState === 'active' ? (
              <button type="button" className="button-secondary" onClick={stopCamera}>
                Detener cámara
              </button>
            ) : (
              <button
                type="button"
                className="button-primary"
                onClick={() => {
                  void startCamera();
                }}
                disabled={cameraState === 'starting'}
              >
                {cameraState === 'starting' ? 'Iniciando…' : 'Activar cámara'}
              </button>
            )}
          </div>

          <form className="scanner-manual" onSubmit={handleManualSubmit}>
            <label className="field">
              <span>Código manual</span>
              <input
                name="code"
                value={manualCode}
                onChange={(event) => setManualCode(event.target.value)}
                placeholder="SKU o código de barras"
                autoComplete="off"
              />
              <span className="field-hint">
                Alternativa si no tenés cámara o el permiso está bloqueado.
              </span>
            </label>
            <button
              type="submit"
              className="button-secondary"
              disabled={manualCode.trim() === '' || busy}
            >
              Buscar
            </button>
          </form>
        </div>

        <div className="scanner-panel scanner-result" aria-live="polite">
          {busy && <p className="loading">Buscando el código…</p>}

          {!busy && lookupError !== null && (
            <div>
              <p className="form-error" role="alert">
                {lookupError}
              </p>
              {lastCode !== null && (
                <p className="hint">Código leído: {lastCode}</p>
              )}
              <button type="button" className="button-secondary" onClick={resetScan}>
                Intentar de nuevo
              </button>
            </div>
          )}

          {!busy && lookupError === null && result === null && (
            <div>
              <h2>Sin lecturas todavía</h2>
              <p className="hint">
                Activá la cámara y apuntá al QR de una etiqueta, o ingresá el código a mano.
              </p>
            </div>
          )}

          {!busy && result !== null && (
            <div className="scanner-card">
              <p className="eyebrow">Producto encontrado</p>
              <h2>{result.productName !== '' ? result.productName : result.variant.name}</h2>
              <p className="scanner-variant">{result.variant.name}</p>

              <dl className="scanner-meta">
                <div>
                  <dt>SKU</dt>
                  <dd>{result.variant.sku}</dd>
                </div>
                <div>
                  <dt>Código de barras</dt>
                  <dd>{result.variant.barcode ?? '—'}</dd>
                </div>
                <div>
                  <dt>Precio</dt>
                  <dd>${formatPrice(result.variant.price)}</dd>
                </div>
                <div>
                  <dt>Estado</dt>
                  <dd>{result.variant.status === 'active' ? 'Activo' : 'Inactivo'}</dd>
                </div>
              </dl>

              {canViewStock && (
                <div className="scanner-stock">
                  <h3>
                    Stock total: {formatQuantity(String(stockTotal))}
                  </h3>
                  {result.stocks.length === 0 ? (
                    <p className="hint">La variante no tiene stock registrado.</p>
                  ) : (
                    <ul>
                      {result.stocks.map((item) => (
                        <li key={item.id}>
                          <span>
                            {item.location?.code ?? 'Sin ubicación'} ·{' '}
                            {item.location?.name ?? '—'}
                          </span>
                          <strong>{formatQuantity(item.quantity)}</strong>
                        </li>
                      ))}
                    </ul>
                  )}
                </div>
              )}

              <div className="scanner-card-actions">
                <button type="button" className="button-secondary" onClick={resetScan}>
                  Escanear otro
                </button>
                <Link className="button-secondary" to="/catalog">
                  Ver catálogo
                </Link>
              </div>
            </div>
          )}
        </div>
      </div>
    </section>
  );
}
