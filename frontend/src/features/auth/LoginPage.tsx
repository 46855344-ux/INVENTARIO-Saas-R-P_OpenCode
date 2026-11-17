import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { Navigate, useLocation, useSearchParams } from 'react-router-dom';
import { resolveApiError } from '../../services/errors';
import { listSocialProviders, socialLoginUrl } from '../../services/social';
import type { SocialProviderOption } from '../../types';
import { Alert } from '../../components/ui/Alert';
import { Button } from '../../components/ui/Button';
import { TextField } from '../../components/ui/TextField';
import { useAuth } from './AuthContext';

interface RedirectState {
  from?: {
    pathname?: string;
  };
}

const SOCIAL_ERRORS: Record<string, string> = {
  provider_unavailable: 'El proveedor no está disponible. Ingrese con su correo y contraseña.',
  not_linked: 'La cuenta social no está vinculada. Ingrese con su contraseña y vincúlela desde Mi cuenta.',
  account_inactive: 'La cuenta está inhabilitada. Comuníquese con el administrador.',
  session_expired: 'La sesión expiró. Intente nuevamente.',
  failed: 'No se pudo completar el acceso con el proveedor. Intente nuevamente.',
};

export function LoginPage() {
  const { status, login } = useAuth();
  const location = useLocation();
  const [searchParams] = useSearchParams();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [providers, setProviders] = useState<SocialProviderOption[]>([]);

  const state = location.state as RedirectState | null;
  const redirectTo = state?.from?.pathname ?? '/';
  const socialError = searchParams.get('social_error');
  const socialMessage = socialError
    ? SOCIAL_ERRORS[socialError] ?? 'No se pudo completar el acceso social.'
    : null;

  useEffect(() => {
    let active = true;

    listSocialProviders()
      .then((options) => {
        if (active) {
          setProviders(options);
        }
      })
      .catch(() => {
        if (active) {
          setProviders([]);
        }
      });

    return () => {
      active = false;
    };
  }, []);

  if (status === 'authenticated') {
    return <Navigate to={redirectTo} replace />;
  }

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setError(null);
    setSubmitting(true);

    try {
      await login(email.trim(), password);
    } catch (loginError) {
      setError(resolveApiError(loginError).message);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <main className="auth-page">
      <section className="auth-card">
        <p className="eyebrow">Acceso</p>
        <h1>SAAS Inversiones R&amp;P</h1>
        <p className="lead">
          Sistema de gestión de inventario y almacén. Inicie sesión para continuar.
        </p>

        {error && <Alert variant="error">{error}</Alert>}

        {socialMessage && <Alert variant="error">{socialMessage}</Alert>}

        <form className="auth-form" onSubmit={handleSubmit} noValidate>
          <TextField
            label="Correo electrónico"
            type="email"
            name="email"
            autoComplete="username"
            value={email}
            onChange={(event) => setEmail(event.target.value)}
            required
          />

          <TextField
            label="Contraseña"
            type="password"
            name="password"
            autoComplete="current-password"
            value={password}
            onChange={(event) => setPassword(event.target.value)}
            required
          />

          <Button type="submit" loading={submitting}>
            {submitting ? 'Ingresando…' : 'Ingresar'}
          </Button>
        </form>

        {providers.length > 0 && (
          <div className="auth-social">
            <p className="auth-divider">
              <span>O continúa con</span>
            </p>
            <div className="social-buttons">
              {providers.map((option) => (
                <a
                  key={option.provider}
                  className="button-secondary social-button"
                  href={socialLoginUrl(option.provider)}
                >
                  Continuar con {option.label}
                </a>
              ))}
            </div>
          </div>
        )}

        <p className="auth-hint">Demo: admin@empresa-demo.test · contraseña: password</p>
      </section>
    </main>
  );
}
