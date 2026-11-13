import { useState } from 'react';
import type { FormEvent } from 'react';
import type { ProductVariant } from '../../types';

export interface VariantFormValues {
  name: string;
  sku: string;
  barcode: string | null;
  cost: string;
  price: string;
}

interface ProductVariantFormProps {
  mode: 'create' | 'edit';
  variant: ProductVariant | null;
  submitting: boolean;
  error: string | null;
  onSubmit: (values: VariantFormValues) => void;
  onCancel: () => void;
}

export function ProductVariantForm({
  mode,
  variant,
  submitting,
  error,
  onSubmit,
  onCancel,
}: ProductVariantFormProps) {
  const [name, setName] = useState(variant?.name ?? '');
  const [sku, setSku] = useState(variant?.sku ?? '');
  const [barcode, setBarcode] = useState(variant?.barcode ?? '');
  const [cost, setCost] = useState(variant?.cost ?? '');
  const [price, setPrice] = useState(variant?.price ?? '');

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    const trimmedBarcode = barcode.trim();

    onSubmit({
      name: name.trim(),
      sku: sku.trim(),
      barcode: trimmedBarcode === '' ? null : trimmedBarcode,
      cost: cost.trim() === '' ? '0' : cost.trim(),
      price: price.trim() === '' ? '0' : price.trim(),
    });
  };

  return (
    <form className="user-form" onSubmit={handleSubmit}>
      <h2>{mode === 'create' ? 'Nueva variante' : `Editar ${variant?.name ?? 'variante'}`}</h2>

      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

      <div className="user-form-grid">
        <label className="field">
          <span>Nombre</span>
          <input
            name="name"
            value={name}
            onChange={(event) => setName(event.target.value)}
            maxLength={150}
            required
          />
        </label>

        <label className="field">
          <span>SKU</span>
          <input
            name="sku"
            value={sku}
            onChange={(event) => setSku(event.target.value)}
            maxLength={50}
            required
          />
        </label>

        <label className="field">
          <span>Código de barras</span>
          <input
            name="barcode"
            value={barcode}
            onChange={(event) => setBarcode(event.target.value)}
            maxLength={50}
          />
        </label>

        <label className="field">
          <span>Costo</span>
          <input
            name="cost"
            type="number"
            step="0.0001"
            min="0"
            value={cost}
            onChange={(event) => setCost(event.target.value)}
          />
        </label>

        <label className="field">
          <span>Precio</span>
          <input
            name="price"
            type="number"
            step="0.0001"
            min="0"
            value={price}
            onChange={(event) => setPrice(event.target.value)}
          />
        </label>
      </div>

      <div className="user-form-actions">
        <button type="submit" className="button-primary" disabled={submitting}>
          {submitting ? 'Guardando…' : 'Guardar'}
        </button>
        <button
          type="button"
          className="button-secondary"
          onClick={onCancel}
          disabled={submitting}
        >
          Cancelar
        </button>
      </div>
    </form>
  );
}
