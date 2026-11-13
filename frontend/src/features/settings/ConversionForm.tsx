import { useState } from 'react';
import type { FormEvent } from 'react';
import type { Unit, UnitConversion } from '../../types';

export interface ConversionFormValues {
  from_unit_id: number;
  to_unit_id: number;
  factor: string;
}

interface ConversionFormProps {
  mode: 'create' | 'edit';
  conversion: UnitConversion | null;
  units: Unit[];
  submitting: boolean;
  error: string | null;
  onSubmit: (values: ConversionFormValues) => void;
  onCancel: () => void;
}

export function ConversionForm({
  mode,
  conversion,
  units,
  submitting,
  error,
  onSubmit,
  onCancel,
}: ConversionFormProps) {
  const [fromId, setFromId] = useState(conversion?.from_unit_id ?? units[0]?.id ?? 0);
  const [toId, setToId] = useState(
    conversion?.to_unit_id ?? units[1]?.id ?? units[0]?.id ?? 0,
  );
  const [factor, setFactor] = useState(conversion?.factor ?? '');

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    onSubmit({
      from_unit_id: fromId,
      to_unit_id: toId,
      factor: factor.trim(),
    });
  };

  return (
    <form className="user-form" onSubmit={handleSubmit}>
      <h2>{mode === 'create' ? 'Nueva conversión' : 'Editar factor de conversión'}</h2>

      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

      <div className="user-form-grid">
        <label className="field">
          <span>Unidad de origen</span>
          <select
            name="from_unit_id"
            value={fromId}
            onChange={(event) => setFromId(Number(event.target.value))}
            disabled={mode === 'edit'}
            required
          >
            {units.map((unit) => (
              <option key={unit.id} value={unit.id}>
                {unit.code} — {unit.name}
              </option>
            ))}
          </select>
        </label>

        <label className="field">
          <span>Unidad de destino</span>
          <select
            name="to_unit_id"
            value={toId}
            onChange={(event) => setToId(Number(event.target.value))}
            disabled={mode === 'edit'}
            required
          >
            {units.map((unit) => (
              <option key={unit.id} value={unit.id}>
                {unit.code} — {unit.name}
              </option>
            ))}
          </select>
        </label>

        <label className="field">
          <span>Factor</span>
          <input
            name="factor"
            type="number"
            step="0.000001"
            min="0.000001"
            value={factor}
            onChange={(event) => setFactor(event.target.value)}
            required
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
