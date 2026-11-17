import { useId } from 'react';
import type { ReactNode, SelectHTMLAttributes } from 'react';

interface SelectFieldProps extends SelectHTMLAttributes<HTMLSelectElement> {
  label: string;
  error?: string | null;
  children: ReactNode;
}

export function SelectField({
  label,
  error,
  id,
  children,
  className = '',
  ...rest
}: SelectFieldProps) {
  const generatedId = useId();
  const selectId = id ?? `select-${generatedId}`;
  const errorId = `${selectId}-error`;

  return (
    <div className={`field ${className}`.trim()}>
      <label htmlFor={selectId}>{label}</label>
      <select
        id={selectId}
        aria-invalid={error ? true : undefined}
        aria-describedby={error ? errorId : undefined}
        {...rest}
      >
        {children}
      </select>
      {error && (
        <small id={errorId} className="field-error" role="alert">
          {error}
        </small>
      )}
    </div>
  );
}
