<?php

namespace App\Modules\Metrics\Models;

use App\Modules\Shared\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['company_id', 'started_at', 'finished_at', 'status', 'evaluated', 'generated', 'resolved', 'error_summary'])]
class AlertEvaluationRun extends Model
{
    use BelongsToCompany;

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'evaluated' => 'integer',
            'generated' => 'integer',
            'resolved' => 'integer',
        ];
    }
}
