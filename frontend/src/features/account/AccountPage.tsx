import { useEffect, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';
import { Dialog } from '../../components/ui/Dialog';
import { resolveApiError } from '../../services/errors';
import {
  listSocialAccounts,
  listSocialProviders,
  socialLinkUrl,
  unlinkSocialAccount,
} from '../../services/social';
import type { SocialIdentity, SocialProvider, SocialProviderOption } from '../../types';

const SOCIAL_ERRORS: Record<string, string> = {
  conflict: 'Esa identidad ya está vinculada a otra cuenta o ya tiene una cuenta del mismo proveedor.',
  provider_unavailable: 'El proveedor no está configurado o no está disponible.',
  session_expired: 'La sesión expiró. Inicie sesión nuevamente.',
  failed: 'No se pudo completar la operación con el proveedor.',
};

export function AccountPage() {
  const { user } = useAuth();
  const [searchParams] = useSearchParams();
  const [accounts, setAccounts] = useState<SocialIdentity[]>([]);
  const [providers, setProviders] = useState<SocialProviderOption[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [actionError, setActionError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [reloadToken, setReloadToken] = useState(0);
  const [unlinkTarget, setUnlinkTarget] = useState<SocialProvider | null>(null);
  const [unlinking, setUnlinking] = useState(false);

  const linkedQuery = searchParams.get('social');
  const errorQuery = searchParams.get('social_error');

  useEffect(() => {
    let active = true;

    Promise.all([listSocialAccounts(), listSocialProviders()])
      .then(([accountList, providerList]) => {
        if (active) {
          setAccounts(accountList);
          setProviders(providerList);
        }
      })
      .catch((requestError) => {
        if (active) {
          setError(resolveApiError(requestError).message);
        }
      })
      .finally(() => {
        if (active) {
          setLoading(false);
        }
      });

    return () => {
      active = false;
    };
  }, [reloadToken]);

  const handleUnlink = (provider: SocialProvider) => {
    setActionError(null);
    setSuccess(null);
    setUnlinkTarget(provider);
  };

  const confirmUnlink = async () => {
    if (unlinkTarget === null) {
      return;
    }

    setUnlinking(true);

    try {
      await unlinkSocialAccount(unlinkTarget);
      setSuccess('Cuenta social desvinculada.');
      setUnlinkTarget(null);
      setReloadToken((token) => token + 1);
    } catch (unlinkError) {
      setActionError(resolveApiError(unlinkError).message);
      setUnlinkTarget(null);
    } finally {
      setUnlinking(false);
    }
  };

  return (
    <section className="users-page">
      <header className="users-header">
        <div>
          <p className="eyebrow">Cuenta</p>
          <h1>Mi cuenta</h1>
          <p className="lead">
            Métodos de acceso de {user?.name ?? 'su usuario'} ({user?.email ?? '—'}).
          </p>
        </div>
      </header>

      {errorQuery && (
        <p className="form-error" role="alert">
          {SOCIAL_ERRORS[errorQuery] ?? 'No se pudo completar la operación social.'}
        </p>
      )}

      {linkedQuery === 'linked' && (
        <p className="simulator-result" role="status">
          Cuenta social vinculada correctamente.
        </p>
      )}

      {actionError && (
        <p className="form-error" role="alert">
          {actionError}
        </p>
      )}

      {success && (
        <p className="simulator-result" role="status">
          {success}
        </p>
      )}

      <div className="catalog-section">
        <div className="catalog-section-header">
          <div>
            <h2>Métodos de acceso</h2>
            <p className="hint">
              La cuenta local siempre permanece disponible. El método social no cambia
              su empresa, rol ni permisos.
            </p>
          </div>
        </div>

        {loading && <p className="loading">Cargando métodos de acceso…</p>}

        {error && (
          <p className="form-error" role="alert">
            {error}
          </p>
        )}

        {!loading && !error && (
          <table className="users-table">
            <thead>
              <tr>
                <th>Método</th>
                <th>Estado</th>
                <th>Vinculado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Correo y contraseña</td>
                <td>Activo</td>
                <td>{user?.email ?? '—'}</td>
                <td>
                  <span className="hint">Método principal</span>
                </td>
              </tr>

              {providers.map((option) => {
                const linked = accounts.find((item) => item.provider === option.provider);

                return (
                  <tr key={option.provider}>
                    <td>{option.label}</td>
                    <td>{linked ? 'Vinculado' : 'No vinculado'}</td>
                    <td>
                      {linked?.linked_at
                        ? new Date(linked.linked_at).toLocaleString('es-AR')
                        : '—'}
                    </td>
                    <td className="users-actions">
                      {linked ? (
                        <button
                          type="button"
                          className="button-link"
                          onClick={() => {
                            void handleUnlink(option.provider);
                          }}
                          disabled={unlinking}
                        >
                          {unlinking ? 'Desvinculando…' : 'Desvincular'}
                        </button>
                      ) : (
                        <a
                          className="button-link"
                          href={socialLinkUrl(option.provider)}
                        >
                          Vincular
                        </a>
                      )}
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        )}

        {!loading && !error && providers.length === 0 && (
          <p className="empty-state">
            No hay proveedores sociales configurados en este entorno.
          </p>
        )}
      </div>

      <Dialog
        open={unlinkTarget !== null}
        title={`Desvincular ${unlinkTarget === 'google' ? 'Google' : 'Facebook'}`}
        description="Podrá seguir ingresando con su correo y contraseña."
        confirmLabel="Desvincular"
        loading={unlinking}
        danger
        onConfirm={() => {
          void confirmUnlink();
        }}
        onClose={() => setUnlinkTarget(null)}
      />
    </section>
  );
}
