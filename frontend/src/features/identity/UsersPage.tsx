import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { Can } from '../auth/Can';
import { useAuth } from '../auth/AuthContext';
import { resolveApiError } from '../../services/errors';
import {
  assignUserRole,
  createUser,
  listRoles,
  listUsers,
  updateUser,
  updateUserStatus,
} from '../../services/identity';
import type { CreateMemberInput, UpdateMemberInput, UserFilters } from '../../services/identity';
import type { Member, PaginatedMeta, Role } from '../../types';
import { UserForm, type UserFormValues } from './UserForm';

const PER_PAGE = 10;

const EMPTY_META: PaginatedMeta = {
  current_page: 1,
  last_page: 1,
  per_page: PER_PAGE,
  total: 0,
};

export function UsersPage() {
  const { activeCompany, hasPermission } = useAuth();
  const canView = hasPermission('identity.users.view');
  const canManage = hasPermission('identity.users.manage');
  const activeCompanyId = activeCompany?.id ?? null;

  const [members, setMembers] = useState<Member[]>([]);
  const [meta, setMeta] = useState<PaginatedMeta>(EMPTY_META);
  const [roles, setRoles] = useState<Role[]>([]);
  const [query, setQuery] = useState<UserFilters>({ page: 1, per_page: PER_PAGE });
  const [reloadToken, setReloadToken] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [actionError, setActionError] = useState<string | null>(null);

  const [searchInput, setSearchInput] = useState('');
  const [statusInput, setStatusInput] = useState<'' | 'active' | 'inactive'>('');
  const [roleInput, setRoleInput] = useState<number | ''>('');

  const [formMode, setFormMode] = useState<'create' | 'edit' | null>(null);
  const [editing, setEditing] = useState<Member | null>(null);
  const [formError, setFormError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (!canView || activeCompanyId === null) {
      return;
    }

    let active = true;

    const load = async () => {
      setLoading(true);
      setError(null);

      try {
        const result = await listUsers(query);

        if (active) {
          setMembers(result.data);
          setMeta(result.meta);
        }
      } catch (requestError) {
        if (active) {
          setError(resolveApiError(requestError).message);
        }
      } finally {
        if (active) {
          setLoading(false);
        }
      }
    };

    void load();

    return () => {
      active = false;
    };
  }, [canView, activeCompanyId, query, reloadToken]);

  useEffect(() => {
    if (!canView || activeCompanyId === null) {
      return;
    }

    let active = true;

    listRoles()
      .then((result) => {
        if (active) {
          setRoles(result);
        }
      })
      .catch(() => {
        if (active) {
          setRoles([]);
        }
      });

    return () => {
      active = false;
    };
  }, [canView, activeCompanyId]);

  const handleFilter = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    setQuery({
      page: 1,
      per_page: PER_PAGE,
      search: searchInput.trim(),
      status: statusInput,
      role_id: roleInput,
    });
  };

  const openCreate = () => {
    setEditing(null);
    setFormError(null);
    setFormMode('create');
  };

  const openEdit = (member: Member) => {
    setEditing(member);
    setFormError(null);
    setFormMode('edit');
  };

  const closeForm = () => {
    setFormMode(null);
    setEditing(null);
    setFormError(null);
  };

  const handleSubmit = async (values: UserFormValues) => {
    setSubmitting(true);
    setFormError(null);

    try {
      if (formMode === 'create') {
        const input: CreateMemberInput = {
          name: values.name,
          email: values.email,
          password: values.password,
          password_confirmation: values.password_confirmation,
          role_id: values.role_id,
        };
        await createUser(input);
      } else if (editing !== null) {
        const input: UpdateMemberInput = {
          name: values.name,
          email: values.email,
        };

        if (values.password) {
          input.password = values.password;
          input.password_confirmation = values.password_confirmation;
        }

        await updateUser(editing.id, input);

        if (editing.role?.id !== values.role_id) {
          await assignUserRole(editing.id, values.role_id);
        }
      }

      closeForm();
      setReloadToken((token) => token + 1);
    } catch (submitError) {
      setFormError(resolveApiError(submitError).message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleToggleStatus = async (member: Member) => {
    setActionError(null);

    try {
      await updateUserStatus(member.id, member.status === 'active' ? 'inactive' : 'active');
      setReloadToken((token) => token + 1);
    } catch (statusError) {
      setActionError(resolveApiError(statusError).message);
    }
  };

  if (!canView) {
    return (
      <section className="home">
        <p className="lead">No tiene permiso para ver los usuarios de esta empresa.</p>
      </section>
    );
  }

  return (
    <section className="users-page">
      <header className="users-header">
        <div>
          <p className="eyebrow">Identidad</p>
          <h1>Usuarios</h1>
          <p className="lead">
            Usuarios de {activeCompany?.name ?? 'la empresa activa'}.
          </p>
        </div>
        <Can permission="identity.users.manage">
          <button
            type="button"
            className="button-primary"
            onClick={openCreate}
            disabled={roles.length === 0}
          >
            Nuevo usuario
          </button>
        </Can>
      </header>

      {actionError && (
        <p className="form-error" role="alert">
          {actionError}
        </p>
      )}

      <form className="users-filters" onSubmit={handleFilter}>
        <label className="field">
          <span>Buscar</span>
          <input
            name="search"
            value={searchInput}
            onChange={(event) => setSearchInput(event.target.value)}
            placeholder="Nombre o correo"
          />
        </label>

        <label className="field">
          <span>Estado</span>
          <select
            name="status"
            value={statusInput}
            onChange={(event) =>
              setStatusInput(event.target.value as '' | 'active' | 'inactive')
            }
          >
            <option value="">Todos</option>
            <option value="active">Activo</option>
            <option value="inactive">Inactivo</option>
          </select>
        </label>

        <label className="field">
          <span>Rol</span>
          <select
            name="role_id"
            value={roleInput}
            onChange={(event) =>
              setRoleInput(event.target.value === '' ? '' : Number(event.target.value))
            }
          >
            <option value="">Todos</option>
            {roles.map((role) => (
              <option key={role.id} value={role.id}>
                {role.name}
              </option>
            ))}
          </select>
        </label>

        <button type="submit" className="button-secondary">
          Filtrar
        </button>
      </form>

      {canManage && roles.length === 0 && (
        <p className="hint">No hay roles disponibles para esta empresa.</p>
      )}

      {formMode && (
        <UserForm
          key={`${formMode}-${editing?.id ?? 'new'}`}
          mode={formMode}
          member={editing}
          roles={roles}
          submitting={submitting}
          error={formError}
          onSubmit={handleSubmit}
          onCancel={closeForm}
        />
      )}

      {loading && <p className="loading">Cargando usuarios…</p>}

      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

      {!loading && !error && members.length === 0 && (
        <p className="empty-state">No hay usuarios para esta empresa.</p>
      )}

      {!loading && members.length > 0 && (
        <table className="users-table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Correo</th>
              <th>Rol</th>
              <th>Estado</th>
              <Can permission="identity.users.manage">
                <th>Acciones</th>
              </Can>
            </tr>
          </thead>
          <tbody>
            {members.map((member) => (
              <tr key={member.id}>
                <td>{member.name}</td>
                <td>{member.email}</td>
                <td>{member.role?.name ?? 'Sin rol'}</td>
                <td>
                  <span className={`status-badge status-${member.status}`}>
                    {member.status === 'active' ? 'Activo' : 'Inactivo'}
                  </span>
                </td>
                <Can permission="identity.users.manage">
                  <td className="users-actions">
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => openEdit(member)}
                    >
                      Editar
                    </button>
                    <button
                      type="button"
                      className="button-link"
                      onClick={() => {
                        void handleToggleStatus(member);
                      }}
                    >
                      {member.status === 'active' ? 'Desactivar' : 'Activar'}
                    </button>
                  </td>
                </Can>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      <nav className="pagination" aria-label="Paginación de usuarios">
        <button
          type="button"
          className="button-secondary"
          disabled={meta.current_page <= 1}
          onClick={() =>
            setQuery((current) => ({ ...current, page: Math.max(1, (current.page ?? 1) - 1) }))
          }
        >
          Anterior
        </button>
        <span>
          Página {meta.current_page} de {meta.last_page} · {meta.total} usuarios
        </span>
        <button
          type="button"
          className="button-secondary"
          disabled={meta.current_page >= meta.last_page}
          onClick={() =>
            setQuery((current) => ({ ...current, page: (current.page ?? 1) + 1 }))
          }
        >
          Siguiente
        </button>
      </nav>
    </section>
  );
}
