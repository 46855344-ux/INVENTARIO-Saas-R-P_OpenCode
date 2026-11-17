import type { ReactNode } from 'react';

interface EmptyStateProps {
  children: ReactNode;
  action?: ReactNode;
}

export function EmptyState({ children, action }: EmptyStateProps) {
  return (
    <div className="empty-state" role="status">
      <p>{children}</p>
      {action}
    </div>
  );
}
