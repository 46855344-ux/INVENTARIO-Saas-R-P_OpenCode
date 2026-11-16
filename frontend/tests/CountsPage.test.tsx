import { render, screen } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { AuthContext } from '../src/features/auth/AuthContext';
import type { AuthContextValue } from '../src/features/auth/AuthContext';
import { CountsPage } from '../src/features/audit/CountsPage';

const mocks = vi.hoisted(() => ({
  listStockCounts: vi.fn(),
  listAuditEvents: vi.fn(),
  listAllVariants: vi.fn(),
  listLocations: vi.fn(),
}));

vi.mock('../src/services/audit', () => ({
  listStockCounts: mocks.listStockCounts,
  listAuditEvents: mocks.listAuditEvents,
  createStockCount: vi.fn(),
  updateStockCountStatus: vi.fn(),
}));
vi.mock('../src/services/catalog', () => ({ listAllVariants: mocks.listAllVariants }));
vi.mock('../src/services/settings', () => ({ listLocations: mocks.listLocations }));

const EMPTY_META = { current_page: 1, last_page: 1, per_page: 10, total: 0 };

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

function renderPage(permissions: string[]) {
  render(
    <AuthContext.Provider value={authValue(permissions)}>
      <CountsPage />
    </AuthContext.Provider>,
  );
}

describe('CountsPage', () => {
  beforeEach(() => {
    mocks.listStockCounts.mockReset().mockResolvedValue({ data: [], meta: EMPTY_META });
    mocks.listAuditEvents.mockReset().mockResolvedValue({ data: [], meta: EMPTY_META });
    mocks.listAllVariants.mockReset().mockResolvedValue({ data: [], meta: EMPTY_META });
    mocks.listLocations.mockReset().mockResolvedValue({ data: [], meta: EMPTY_META });
  });

  it('muestra un mensaje sin permisos', () => {
    renderPage([]);
    expect(screen.getByText(/no tiene permiso para ver conteos ni auditoría/i)).toBeInTheDocument();
  });

  it('muestra el estado vacío de conteos', async () => {
    renderPage(['counts.view']);
    expect(await screen.findByText(/no hay conteos registrados/i)).toBeInTheDocument();
  });

  it('muestra auditoría cuando solo hay audit.view', async () => {
    renderPage(['audit.view']);
    expect(await screen.findByText(/no hay eventos para el filtro/i)).toBeInTheDocument();
    expect(screen.queryByRole('button', { name: 'Conteos' })).not.toBeInTheDocument();
  });
});
