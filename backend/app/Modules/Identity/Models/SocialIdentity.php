<?php

namespace App\Modules\Identity\Models;

use Database\Factories\SocialIdentityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Identidad externa vinculada a un usuario interno. Es global, no pertenece a
 * una empresa: la autorización sigue en `company_user`.
 */
#[Fillable(['user_id', 'provider', 'provider_user_id', 'linked_at'])]
class SocialIdentity extends Model
{
    /** @use HasFactory<SocialIdentityFactory> */
    use HasFactory;

    public const PROVIDER_GOOGLE = 'google';

    public const PROVIDER_FACEBOOK = 'facebook';

    /** @var list<string> */
    public const PROVIDERS = [self::PROVIDER_GOOGLE, self::PROVIDER_FACEBOOK];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'linked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function providerLabel(string $provider): string
    {
        return match ($provider) {
            self::PROVIDER_GOOGLE => 'Google',
            self::PROVIDER_FACEBOOK => 'Facebook',
            default => $provider,
        };
    }

    protected static function newFactory(): SocialIdentityFactory
    {
        return SocialIdentityFactory::new();
    }
}
