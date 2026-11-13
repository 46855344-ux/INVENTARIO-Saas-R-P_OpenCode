import { useState } from 'react';
import type { FormEvent } from 'react';
import type { Location, LocationType } from '../../types';
import { LOCATION_TYPES } from './constants';

export interface LocationFormValues {
  code: string;
  name: string;
  type: LocationType;
  parent_id: number | null;
}

interface LocationFormProps {
  mode: 'create' | 'edit';
  location: Location | null;
  options: Location[];
  submitting: boolean;
  error: string | null;
  onSubmit: (values: LocationFormValues) => void;
  onCancel: () => void;
}

export function LocationForm({
  mode,
  location,
  options,
  submitting,
  error,
  onSubmit,
  onCancel,
}: LocationFormProps) {
  const [code, setCode] = useState(location?.code ?? '');
  const [name, setName] = useState(location?.name ?? '');
  const [type, setType] = useState<LocationType>(location?.type ?? 'warehouse');
  const [parentId, setParentId] = useState<number | ''>(location?.parent_id ?? '');

  const availableParents = options.filter((option) => option.id !== location?.id);

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    onSubmit({
      code: code.trim().toUpperCase(),
      name: name.trim(),
      type,
      parent_id: parentId === '' ? null : parentId,
    });
  };

  return (
    <form className="user-form" onSubmit={handleSubmit}>
      <h2>{mode === 'create' ? 'Nueva ubicación' : `Editar ${location?.name ?? 'ubicación'}`}</h2>

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
            maxLength={30}
            required
          />
        </label>

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
          <span>Tipo</span>
          <select
            name="type"
            value={type}
            onChange={(event) => setType(event.target.value as LocationType)}
            required
          >
            {LOCATION_TYPES.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
        </label>

        <label className="field">
          <span>Ubicación padre</span>
          <select
            name="parent_id"
            value={parentId}
            onChange={(event) =>
              setParentId(event.target.value === '' ? '' : Number(event.target.value))
            }
          >
            <option value="">Sin padre</option>
            {availableParents.map((option) => (
              <option key={option.id} value={option.id}>
                {option.code} — {option.name}
              </option>
            ))}
          </select>
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
