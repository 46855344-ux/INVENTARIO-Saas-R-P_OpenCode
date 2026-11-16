import type {
  AlertRule,
  AlertStatus,
  AlertType,
  MetricsOverview,
  Paginated,
  StockAlert,
  StockBalance,
} from '../types';
import { api } from './api';

export interface MetricsFilters {
  days?: number;
  location_id?: number | '';
}

export interface StockDetailFilters {
  page?: number;
  per_page?: number;
  search?: string;
  location_id?: number | '';
}

export interface AlertFilters {
  page?: number;
  per_page?: number;
  status?: AlertStatus | '';
  type?: AlertType | '';
}

export interface AlertRuleFilters {
  page?: number;
  per_page?: number;
  status?: 'active' | 'inactive' | '';
  type?: AlertType | '';
}

export interface AlertRuleInput {
  type: AlertType;
  variant_id: number;
  location_id: number;
  threshold: string | null;
  inactivity_days: number | null;
}

function toParams(filters: object): Record<string, string | number> {
  const params: Record<string, string | number> = {};

  Object.entries(filters).forEach(([key, value]) => {
    if (typeof value === 'string' || typeof value === 'number') {
      if (value !== '') {
        params[key] = value;
      }
    }
  });

  return params;
}

export async function getMetricsOverview(
  filters: MetricsFilters = {},
): Promise<MetricsOverview> {
  const { data } = await api.get<{ data: MetricsOverview }>(
    '/v1/metrics/overview',
    { params: toParams(filters) },
  );

  return data.data;
}

export async function listStockDetail(
  filters: StockDetailFilters = {},
): Promise<Paginated<StockBalance>> {
  const { data } = await api.get<Paginated<StockBalance>>(
    '/v1/metrics/stock-detail',
    { params: toParams(filters) },
  );

  return data;
}

export async function listAlerts(
  filters: AlertFilters = {},
): Promise<Paginated<StockAlert>> {
  const { data } = await api.get<Paginated<StockAlert>>('/v1/alerts', {
    params: toParams(filters),
  });

  return data;
}

export async function updateAlertStatus(
  id: number,
  status: AlertStatus,
): Promise<StockAlert> {
  const { data } = await api.patch<{ data: StockAlert }>(
    `/v1/alerts/${id}/status`,
    { status },
  );

  return data.data;
}

export async function listAlertRules(
  filters: AlertRuleFilters = {},
): Promise<Paginated<AlertRule>> {
  const { data } = await api.get<Paginated<AlertRule>>('/v1/alert-rules', {
    params: toParams(filters),
  });

  return data;
}

export async function createAlertRule(input: AlertRuleInput): Promise<AlertRule> {
  const { data } = await api.post<{ data: AlertRule }>('/v1/alert-rules', input);

  return data.data;
}

export async function updateAlertRule(
  id: number,
  input: AlertRuleInput,
): Promise<AlertRule> {
  const { data } = await api.patch<{ data: AlertRule }>(
    `/v1/alert-rules/${id}`,
    input,
  );

  return data.data;
}
