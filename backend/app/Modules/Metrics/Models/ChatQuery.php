<?php

namespace App\Modules\Metrics\Models;

use App\Modules\Shared\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['company_id', 'user_id', 'intent', 'status', 'duration_ms', 'created_at'])]
class ChatQuery extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'duration_ms' => 'integer'];
    }
}
