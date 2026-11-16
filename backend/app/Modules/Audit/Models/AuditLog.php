<?php

namespace App\Modules\Audit\Models;

use App\Modules\Identity\Models\User;
use App\Modules\Shared\Tenancy\BelongsToCompany;
use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['company_id', 'user_id', 'action', 'entity_type', 'entity_id', 'context', 'ip', 'created_at'])]
class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use BelongsToCompany, HasFactory;

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'context' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function newFactory(): AuditLogFactory
    {
        return AuditLogFactory::new();
    }
}
