import { useState } from 'react';
import type { FormEvent } from 'react';
import type { Unit, UnitDimension } from '../../types';
import { UNIT_DIMENSIONS } from './constants';

export interface UnitFormValues {
  code: string;
  name: string;
  dimension: UnitDimension;
  precision: number;
}

interface UnitFormProps {
  mode: 'create' | 'edit';
  unit: Unit | null;
  submitting: boolean;
  error: string | null;
  onSubmit: (values: UnitFormValues) => void;
  onCancel: () => void;
}

export function UnitForm({
  mode,
  unit,
  submitting,
  error,
  onSubmit,
  onCancel,
}: UnitFormProps) {
  const [code, setCode] = useState(unit?.code ?? '');
  const [name, setName] = useState(unit?.name ?? '');
  const [dimension, setDimension] = useState<UnitDimension>(unit?.dimension ?? 'count');
  const [precision, setPrecision] = useState(unit?.precision ?? 4);

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    onSubmit({
      code: code.trim().toUpperCase(),
      name: name.trim(),
      dimension,
      precision,
    });
  };

  return (
    <form className="user-form" onSubmit={handleSubmit}>
      <h2>{mode === 'create' ? 'Nueva unidad' : `Editar ${unit?.name ?? 'unidad'}`}</h2>

      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

      <div className="user-form-grid">
        <label className="field">
          <span>Código</span>
          <input
            name="code"
            value={code}
            onChange={(event) => setCode(event.target.value)}
            maxLength={20}
            required
          />
        </label>

        <label className="field">
          <span>Nombre</span>
          <input
            name="name"
            value={name}
            onChange={(event) => setName(event.target.value)}
            maxLength={100}
            required
          />
        </label>

        <label className="field">
          <span>Dimensión</span>
          <select
            name="dimension"
            value={dimension}
            onChange={(event) => setDimension(event.target.value as UnitDimension)}
            required
          >
            {UNIT_DIMENSIONS.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
        </label>

        <label className="field">
          <span>Precisión (decimales)</span>
          <input
            name="precision"
            type="number"
            min={0}
            max={4}
            value={precision}
            onChange={(event) => setPrecision(Number(event.target.value))}
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
