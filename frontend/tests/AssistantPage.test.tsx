import { render, screen } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { AuthContext } from '../src/features/auth/AuthContext';
import type { AuthContextValue } from '../src/features/auth/AuthContext';
import { AssistantPage } from '../src/features/assistant/AssistantPage';

const mocks = vi.hoisted(() => ({
  askAssistant: vi.fn(),
  getAssistantSuggestions: vi.fn(),
}));

vi.mock('../src/services/assistant', () => ({
  askAssistant: mocks.askAssistant,
  getAssistantSuggestions: mocks.getAssistantSuggestions,
}));

function authValue(permissions: string[]): AuthContextValue {
  const company = { id: 1, name: 'Empresa Demo', slug: 'empresa-demo', role: null, permissions };
  return {
    status: 'authenticated',
    user: { id: 1, name: 'Administrador Demo', email: 'admin@empresa-demo.test' },
    companies: [company],
    activeCompany: company,
    permissions,
    hasPermission: (code: string) => permissions.includes(code),
    setActiveCompany: vi.fn(),
    login: vi.fn(),
    logout: vi.fn(),
  };
}

describe('AssistantPage', () => {
  beforeEach(() => {
    mocks.askAssistant.mockReset();
    mocks.getAssistantSuggestions.mockReset().mockResolvedValue([
      '¿Cuántos productos agotados hay?',
    ]);
  });

  it('muestra un mensaje sin permiso', () => {
    render(
      <AuthContext.Provider value={authValue([])}>
        <AssistantPage />
      </AuthContext.Provider>,
    );

    expect(screen.getByText(/no tiene permiso para usar el asistente/i)).toBeInTheDocument();
  });

  it('muestra sugerencias y el aviso de solo lectura', async () => {
    render(
      <AuthContext.Provider value={authValue(['assistant.use'])}>
        <AssistantPage />
      </AuthContext.Provider>,
    );

    expect(await screen.findByRole('button', { name: /productos agotados/i })).toBeInTheDocument();
    expect(screen.getByText(/solo lectura/i)).toBeInTheDocument();
  });
});
