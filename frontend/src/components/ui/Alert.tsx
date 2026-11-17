import type { ReactNode } from 'react';

type AlertVariant = 'info' | 'success' | 'warning' | 'error';

interface AlertProps {
  variant?: AlertVariant;
  children: ReactNode;
  className?: string;
}

export function Alert({ variant = 'info', children, className = '' }: AlertProps) {
  const role = variant === 'error' ? 'alert' : 'status';

  return (
    <p className={`alert alert-${variant} ${className}`.trim()} role={role}>
      {children}
    </p>
  );
}
