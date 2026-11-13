import { useState } from 'react';
import type { FormEvent } from 'react';
import type { Member, Role } from '../../types';

export interface UserFormValues {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  role_id: number;
}

interface UserFormProps {
  mode: 'create' | 'edit';
  member: Member | null;
  roles: Role[];
  submitting: boolean;
  error: string | null;
  onSubmit: (values: UserFormValues) => void;
  onCancel: () => void;
}

export function UserForm({
  mode,
  member,
  roles,
  submitting,
  error,
  onSubmit,
  onCancel,
}: UserFormProps) {
  const [name, setName] = useState(member?.name ?? '');
  const [email, setEmail] = useState(member?.email ?? '');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [roleId, setRoleId] = useState(member?.role?.id ?? roles[0]?.id ?? 0);

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    onSubmit({
      name: name.trim(),
      email: email.trim(),
      password,
      password_confirmation: passwordConfirmation,
      role_id: roleId,
    });
  };

  return (
    <form className="user-form" onSubmit={handleSubmit}>
      <h2>{mode === 'create' ? 'Nuevo usuario' : `Editar ${member?.name ?? 'usuario'}`}</h2>

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
          <span>Correo electrónico</span>
          <input
            name="email"
            type="email"
            value={email}
            onChange={(event) => setEmail(event.target.value)}
            maxLength={255}
            required
          />
        </label>

        <label className="field">
          <span>{mode === 'create' ? 'Contraseña' : 'Nueva contraseña (opcional)'}</span>
          <input
            name="password"
            type="password"
            value={password}
            onChange={(event) => setPassword(event.target.value)}
            minLength={8}
            autoComplete="new-password"
            required={mode === 'create'}
          />
        </label>

        <label className="field">
          <span>Confirmar contraseña</span>
          <input
            name="password_confirmation"
            type="password"
            value={passwordConfirmation}
            onChange={(event) => setPasswordConfirmation(event.target.value)}
            minLength={8}
            autoComplete="new-password"
            required={mode === 'create' || password.length > 0}
          />
        </label>

        <label className="field">
          <span>Rol</span>
          <select
            name="role_id"
            value={roleId}
            onChange={(event) => setRoleId(Number(event.target.value))}
            required
          >
            {roles.map((role) => (
              <option key={role.id} value={role.id}>
                {role.name}
                {role.is_global ? '' : ' (empresa)'}
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
