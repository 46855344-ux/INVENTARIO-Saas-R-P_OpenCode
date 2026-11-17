import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { Alert } from '../src/components/ui/Alert';
import { Button } from '../src/components/ui/Button';
import { Dialog, ReasonDialog } from '../src/components/ui/Dialog';
import { TextField } from '../src/components/ui/TextField';

describe('UI components', () => {
  it('asocia el error del campo y lo anuncia', () => {
    render(
      <TextField
        label="Correo"
        name="email"
        value=""
        onChange={vi.fn()}
        error="El correo es obligatorio."
      />,
    );

    const input = screen.getByLabelText('Correo');
    expect(input).toHaveAttribute('aria-invalid', 'true');
    expect(input).toHaveAttribute('aria-describedby');
    expect(screen.getByRole('alert')).toHaveTextContent('El correo es obligatorio.');
  });

  it('bloquea el botón y anuncia la carga', () => {
    render(
      <Button loading onClick={vi.fn()}>
        Guardando
      </Button>,
    );

    const button = screen.getByRole('button', { name: 'Guardando' });
    expect(button).toBeDisabled();
    expect(button).toHaveAttribute('aria-busy', 'true');
  });

  it('usa el rol alert para errores y status para éxito', () => {
    const { rerender } = render(<Alert variant="error">Falló</Alert>);
    expect(screen.getByRole('alert')).toHaveTextContent('Falló');

    rerender(<Alert variant="success">Listo</Alert>);
    expect(screen.getByRole('status')).toHaveTextContent('Listo');
  });

  it('permite activar el botón por teclado', async () => {
    const onClick = vi.fn();
    render(<Button onClick={onClick}>Confirmar</Button>);

    await userEvent.tab();
    await userEvent.keyboard('{Enter}');

    expect(onClick).toHaveBeenCalled();
  });

  it('expone el diálogo como modal y cierra con Escape', async () => {
    const onClose = vi.fn();
    render(
      <Dialog open title="Confirmar" onConfirm={vi.fn()} onClose={onClose}>
        Contenido
      </Dialog>,
    );

    expect(screen.getByRole('dialog', { name: 'Confirmar' })).toHaveAttribute(
      'aria-modal',
      'true',
    );

    await userEvent.keyboard('{Escape}');
    expect(onClose).toHaveBeenCalled();
  });

  it('exige motivo en el diálogo de reversión', async () => {
    const onConfirm = vi.fn();
    render(
      <ReasonDialog
        open
        title="Revertir"
        label="Motivo"
        onConfirm={onConfirm}
        onClose={vi.fn()}
      />,
    );

    await userEvent.click(screen.getByRole('button', { name: 'Confirmar' }));
    expect(screen.getByText(/el motivo es obligatorio/i)).toBeInTheDocument();
    expect(onConfirm).not.toHaveBeenCalled();
  });
});
