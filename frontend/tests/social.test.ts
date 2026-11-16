import { beforeEach, describe, expect, it, vi } from 'vitest';

const { get, del } = vi.hoisted(() => ({ get: vi.fn(), del: vi.fn() }));

vi.mock('../src/services/api', () => ({
  API_ORIGIN: 'http://localhost:8000',
  api: { get, delete: del },
}));

import {
  listSocialAccounts,
  listSocialProviders,
  socialLinkUrl,
  socialLoginUrl,
  unlinkSocialAccount,
} from '../src/services/social';

describe('social service', () => {
  beforeEach(() => {
    get.mockReset();
    del.mockReset().mockResolvedValue({});
  });

  it('construye las URLs de inicio y vinculación', () => {
    expect(socialLoginUrl('google')).toBe(
      'http://localhost:8000/auth/social/google/redirect',
    );
    expect(socialLinkUrl('facebook')).toBe(
      'http://localhost:8000/auth/social/facebook/link',
    );
  });

  it('lista proveedores configurados', async () => {
    get.mockResolvedValueOnce({
      data: { data: [{ provider: 'google', label: 'Google' }] },
    });

    await expect(listSocialProviders()).resolves.toEqual([
      { provider: 'google', label: 'Google' },
    ]);
    expect(get).toHaveBeenCalledWith('/v1/auth/social/providers');
  });

  it('lista cuentas vinculadas y desvincula por proveedor', async () => {
    get.mockResolvedValueOnce({
      data: {
        data: [{ provider: 'google', label: 'Google', linked_at: '2026-11-15T00:00:00Z' }],
      },
    });

    const accounts = await listSocialAccounts();
    expect(accounts).toHaveLength(1);

    await unlinkSocialAccount('google');
    expect(del).toHaveBeenCalledWith('/v1/auth/social/google');
  });
});
