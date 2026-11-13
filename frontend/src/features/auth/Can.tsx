import type { ReactNode } from 'react';
import { useAuth } from './AuthContext';

interface CanProps {
  permission: string;
  children: ReactNode;
  fallback?: ReactNode;
}

/**
 * Muestra u oculta una acción según los permisos de la empresa activa.
 * La interfaz no sustituye la autorización del backend.
 */
export function Can({ permission, children, fallback = null }: CanProps) {
  const { hasPermission } = useAuth();

  return <>{hasPermission(permission) ? children : fallback}</>;
}
