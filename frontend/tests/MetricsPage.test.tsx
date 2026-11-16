import { render, screen } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { AuthContext } from '../src/features/auth/AuthContext';
import type { AuthContextValue } from '../src/features/auth/AuthContext';
import { MetricsPage } from '../src/features/metrics/MetricsPage';

const mocks = vi.hoisted(() => ({
  getMetricsOverview: vi.fn(),
  listStockDetail: vi.fn(),
  listAlerts: vi.fn(),
  listAlertRules: vi.fn(),
  listAllVariants: vi.fn(),
  listLocations: vi.fn(),
}));

vi.mock('../src/services/metrics', () => ({
  getMetricsOverview: mocks.getMetricsOverview,
  listStockDetail: mocks.listStockDetail,
  listAlerts: mocks.listAlerts,
  listAlertRules: mocks.listAlertRules,
  updateAlertStatus: vi.fn(),
  createAlertRule: vi.fn(),
  updateAlertRule: vi.fn(),
}));

vi.mock('../src/services/catalog', () => ({
  listAllVariants: mocks.listAllVariants,
}));

vi.mock('../src/services/settings', () => ({
  listLocations: mocks.listLocations,
}));

const EMPTY_META = { current_page: 1, last_page: 1, per_page: 10, total: 0 };

function authValue(permissions: string[]): AuthContextValue {
  const company = {
    id: 1,
    name: 'Empresa Demo',
    slug: 'empresa-demo',
    role: { id: 1, code: 'owner', name: 'Propietario' },
    permissions,
  };

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
      <MetricsPage />
    </AuthContext.Provider>,
  );
}

describe('MetricsPage', () => {
  beforeEach(() => {
    mocks.getMetricsOverview.mockReset().mockResolvedValue({
      total_products: 3,
      total_variants: 5,
      total_stock: '20.0000',
      out_of_stock: 1,
      low_stock: 2,
      total_value: null,
      valuation_status: 'pending',
      valuation_message: 'Pendiente de definir método de valorización (DP-02).',
      top_outflows: [
        { variant_id: 1, sku: 'LOW-1', name: 'Café', quantity: '4.0000' },
      ],
      inactive: [],
      period_days: 30,
      generated_at: '2026-11-16T00:00:00Z',
    });
    mocks.listStockDetail.mockReset().mockResolvedValue({ data: [], meta: EMPTY_META });
    mocks.listAlerts.mockReset().mockResolvedValue({ data: [], meta: EMPTY_META });
    mocks.listAlertRules.mockReset().mockResolvedValue({ data: [], meta: EMPTY_META });
    mocks.listAllVariants.mockReset().mockResolvedValue({ data: [], meta: EMPTY_META });
    mocks.listLocations.mockReset().mockResolvedValue({ data: [], meta: EMPTY_META });
  });

  it('muestra un mensaje sin permisos', () => {
    renderPage([]);

    expect(
      screen.getByText(/no tiene permiso para ver métricas ni alertas/i),
    ).toBeInTheDocument();
    expect(mocks.getMetricsOverview).not.toHaveBeenCalled();
  });

  it('muestra los indicadores y el estado pendiente de valorización', async () => {
    renderPage(['metrics.view']);

    expect(await screen.findByText('Total de productos')).toBeInTheDocument();
    expect(screen.getByText('3')).toBeInTheDocument();
    expect(screen.getByText('Pendiente')).toBeInTheDocument();
    expect(screen.getByText(/método de valorización/i)).toBeInTheDocument();
    expect(screen.getByText('LOW-1')).toBeInTheDocument();
  });

  it('abre alertas cuando solo existe alerts.view', async () => {
    renderPage(['alerts.view']);

    expect(await screen.findByText(/no hay alertas para los filtros/i)).toBeInTheDocument();
    expect(screen.queryByText('Total de productos')).not.toBeInTheDocument();
  });
});
