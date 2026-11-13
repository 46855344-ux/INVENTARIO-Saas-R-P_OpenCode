import { useState } from 'react';
import type { FormEvent } from 'react';
import type { CatalogItem } from '../../types';

interface CatalogFormProps {
  mode: 'create' | 'edit';
  item: CatalogItem | null;
  singular: string;
  submitting: boolean;
  error: string | null;
  onSubmit: (name: string) => void;
  onCancel: () => void;
}

export function CatalogForm({
  mode,
  item,
  singular,
  submitting,
  error,
  onSubmit,
  onCancel,
}: CatalogFormProps) {
  const [name, setName] = useState(item?.name ?? '');

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    onSubmit(name.trim());
  };

  return (
    <form className="user-form" onSubmit={handleSubmit}>
      <h2>{mode === 'create' ? `Nueva ${singular}` : `Editar ${item?.name ?? singular}`}</h2>

      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

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
