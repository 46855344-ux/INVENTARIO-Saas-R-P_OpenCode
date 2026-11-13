import { useCallback, useEffect, useMemo, useState } from 'react';
import type { ReactNode } from 'react';
import * as authService from '../../services/auth';
import type { AuthSession } from '../../types';
import { AuthContext, type AuthContextValue, type AuthStatus } from './AuthContext';

interface AuthProviderProps {
  children: ReactNode;
}

export function AuthProvider({ children }: AuthProviderProps) {
  const [status, setStatus] = useState<AuthStatus>('loading');
  const [session, setSession] = useState<AuthSession | null>(null);

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
    }
  }, []);

  const value = useMemo<AuthContextValue>(
    () => ({
      status,
      user: session?.user ?? null,
      companies: session?.companies ?? [],
      login,
      logout,
    }),
    [status, session, login, logout],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
