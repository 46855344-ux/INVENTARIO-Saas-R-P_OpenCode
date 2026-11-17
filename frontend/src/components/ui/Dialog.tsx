import { useEffect, useId, useRef, useState } from 'react';
import type { ReactNode } from 'react';
import { Button } from './Button';
import { TextField } from './TextField';

interface DialogProps {
  open: boolean;
  title: string;
  description?: ReactNode;
  children?: ReactNode;
  confirmLabel?: string;
  cancelLabel?: string;
  loading?: boolean;
  danger?: boolean;
  onConfirm: () => void;
  onClose: () => void;
}

export function Dialog({
  open,
  title,
  description,
  children,
  confirmLabel = 'Confirmar',
  cancelLabel = 'Cancelar',
  loading = false,
  danger = false,
  onConfirm,
  onClose,
}: DialogProps) {
  const titleId = useId();
  const dialogRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!open) {
      return;
    }

    const previous = document.activeElement as HTMLElement | null;
    const first = dialogRef.current?.querySelector<HTMLElement>(
      'input, select, textarea, button',
    );
    first?.focus();

    const handleKey = (event: KeyboardEvent) => {
      if (event.key === 'Escape' && !loading) {
        onClose();
      }
    };

    document.addEventListener('keydown', handleKey);

    return () => {
      document.removeEventListener('keydown', handleKey);
      previous?.focus();
    };
  }, [open, loading, onClose]);

  if (!open) {
    return null;
  }

  return (
    <div className="dialog-backdrop" role="presentation">
      <div
        className="dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby={titleId}
        ref={dialogRef}
      >
        <h2 id={titleId}>{title}</h2>
        {description && <div className="dialog-description">{description}</div>}
        {children}
        <div className="dialog-actions">
          <Button variant="secondary" onClick={onClose} disabled={loading}>
            {cancelLabel}
          </Button>
          <Button variant={danger ? 'danger' : 'primary'} onClick={onConfirm} loading={loading}>
            {confirmLabel}
          </Button>
        </div>
      </div>
    </div>
  );
}

interface ReasonDialogProps {
  open: boolean;
  title: string;
  label: string;
  confirmLabel?: string;
  loading?: boolean;
  danger?: boolean;
  onConfirm: (reason: string) => void;
  onClose: () => void;
}

export function ReasonDialog({
  open,
  title,
  label,
  confirmLabel = 'Confirmar',
  loading = false,
  danger = false,
  onConfirm,
  onClose,
}: ReasonDialogProps) {
  const [reason, setReason] = useState('');
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (open) {
      setReason('');
      setError(null);
    }
  }, [open]);

  const handleConfirm = () => {
    if (reason.trim() === '') {
      setError('El motivo es obligatorio.');
      return;
    }

    onConfirm(reason.trim());
  };

  return (
    <Dialog
      open={open}
      title={title}
      confirmLabel={confirmLabel}
      loading={loading}
      danger={danger}
      onConfirm={handleConfirm}
      onClose={onClose}
    >
      <TextField
        label={label}
        value={reason}
        onChange={(event) => setReason(event.target.value)}
        error={error}
        maxLength={255}
        required
      />
    </Dialog>
  );
}
