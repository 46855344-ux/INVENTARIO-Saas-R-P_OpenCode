import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import App from '../src/app/App';

describe('App', () => {
  it('muestra el nombre del proyecto y el módulo de inventario', () => {
    render(<App />);

    expect(
      screen.getByRole('heading', { level: 1, name: /SAAS Inversiones R&P/i }),
    ).toBeInTheDocument();

    expect(
      screen.getByText(/Sistema de gestión de inventario y almacén/i),
    ).toBeInTheDocument();
  });
});
