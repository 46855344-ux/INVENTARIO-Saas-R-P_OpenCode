import { useCallback, useEffect, useMemo, useState } from 'react';
import type { ReactNode } from 'react';
import * as authService from '../../services/auth';
import { setActiveCompanyId } from '../../services/api';
import type { AuthSession, Company } from '../../types';
import { AuthContext, type AuthContextValue, type AuthStatus } from './AuthContext';

const ACTIVE_COMPANY_KEY = 'saas:active-company';

function storedCompanyId(): number | null {
  const value = Number(window.localStorage.getItem(ACTIVE_COMPANY_KEY));

  return Number.isInteger(value) && value > 0 ? value : null;
}

interface AuthProviderProps {
  children: ReactNode;
}

export function AuthProvider({ children }: AuthProviderProps) {
  const [status, setStatus] = useState<AuthStatus>('loading');
  const [session, setSession] = useState<AuthSession | null>(null);
  const [selectedCompanyId, setSelectedCompanyId] = useState<number | null>(storedCompanyId);

  useEffect(() => {
    let active = true;

    authService
      .me()
      .then((data) => {
        if (active) {
          setSession(data);
          setStatus('authenticated');
        }
      })
      .catch(() => {
        if (active) {
          setSession(null);
          setStatus('guest');
        }
      });

    return () => {
      active = false;
    };
  }, []);

  /**
   * La empresa activa se deriva de la sesión y queda disponible para el
   * interceptor de axios antes de que los hijos disparen sus peticiones.
   */
  const activeCompany = useMemo<Company | null>(() => {
    if (session === null || session.companies.length === 0) {
      setActiveCompanyId(null);

      return null;
    }

    const company =
      session.companies.find((item) => item.id === selectedCompanyId) ?? session.companies[0];

    setActiveCompanyId(company.id);

    return company;
  }, [session, selectedCompanyId]);

  useEffect(() => {
    if (activeCompany !== null) {
      window.localStorage.setItem(ACTIVE_COMPANY_KEY, String(activeCompany.id));
    }
  }, [activeCompany]);

  const permissions = useMemo(() => activeCompany?.permissions ?? [], [activeCompany]);

  const hasPermission = useCallback(
    (code: string) => permissions.includes(code),
    [permissions],
  );

  const setActiveCompany = useCallback((companyId: number) => {
    setSelectedCompanyId(companyId);
  }, []);

  const login = useCallback(async (email: string, password: string) => {
    const data = await authService.login(email, password);

    setSession(data);
    setStatus('authenticated');
  }, []);

  const logout = useCallback(async () => {
    try {
      await authService.logout();
    } finally {
      setSession(null);
      setStatus('guest');
      setActiveCompanyId(null);
      window.localStorage.removeItem(ACTIVE_COMPANY_KEY);
    }
  }, []);

  const value = useMemo<AuthContextValue>(
    () => ({
      status,
      user: session?.user ?? null,
      companies: session?.companies ?? [],
      activeCompany,
      permissions,
      hasPermission,
      setActiveCompany,
      login,
      logout,
    }),
    [
      status,
      session,
      activeCompany,
      permissions,
      hasPermission,
      setActiveCompany,
      login,
      logout,
    ],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
