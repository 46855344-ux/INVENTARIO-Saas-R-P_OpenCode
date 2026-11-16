import type {
  SocialIdentity,
  SocialProvider,
  SocialProviderOption,
} from '../types';
import { API_ORIGIN, api } from './api';

export function socialLoginUrl(provider: SocialProvider): string {
  return `${API_ORIGIN}/auth/social/${provider}/redirect`;
}

export function socialLinkUrl(provider: SocialProvider): string {
  return `${API_ORIGIN}/auth/social/${provider}/link`;
}

export async function listSocialProviders(): Promise<SocialProviderOption[]> {
  const { data } = await api.get<{ data: SocialProviderOption[] }>(
    '/v1/auth/social/providers',
  );

  return data.data;
}

export async function listSocialAccounts(): Promise<SocialIdentity[]> {
  const { data } = await api.get<{ data: SocialIdentity[] }>(
    '/v1/auth/social/accounts',
  );

  return data.data;
}

export async function unlinkSocialAccount(provider: SocialProvider): Promise<void> {
  await api.delete(`/v1/auth/social/${provider}`);
}
