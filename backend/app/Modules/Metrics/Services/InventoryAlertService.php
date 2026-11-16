<?php

namespace App\Modules\Metrics\Services;

use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Metrics\Models\AlertEvaluationRun;
use App\Modules\Metrics\Models\AlertRule;
use App\Modules\Metrics\Models\StockAlert;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Throwable;

class InventoryAlertService
{
    /**
     * @param  array{status?: string|null, type?: string|null, per_page?: int|null}  $filters
     */
    public function paginateAlerts(TenantContext $context, array $filters): LengthAwarePaginator
    {
        return StockAlert::query()
            ->with(['variant', 'location', 'rule'])
            ->where('company_id', $context->company->id)
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['type']), fn ($query) => $query->where('type', $filters['type']))
            ->orderByDesc('detected_at')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    /**
     * @param  array{status?: string|null, type?: string|null, per_page?: int|null}  $filters
     */
    public function paginateRules(TenantContext $context, array $filters): LengthAwarePaginator
    {
        return AlertRule::query()
            ->with(['variant', 'location'])
            ->where('company_id', $context->company->id)
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['type']), fn ($query) => $query->where('type', $filters['type']))
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createRule(TenantContext $context, array $data): AlertRule
    {
        return AlertRule::query()->create([
            'company_id' => $context->company->id,
            'type' => $data['type'],
            'variant_id' => $data['variant_id'],
            'location_id' => $data['location_id'],
            'threshold' => $data['type'] === AlertRule::TYPE_MIN_STOCK ? $data['threshold'] : null,
            'inactivity_days' => $data['type'] === AlertRule::TYPE_INACTIVITY ? $data['inactivity_days'] : null,
            'status' => AlertRule::STATUS_ACTIVE,
        ])->load(['variant', 'location']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateRule(AlertRule $rule, array $data): AlertRule
    {
        $rule->update([
            'type' => $data['type'],
            'variant_id' => $data['variant_id'],
            'location_id' => $data['location_id'],
            'threshold' => $data['type'] === AlertRule::TYPE_MIN_STOCK ? $data['threshold'] : null,
            'inactivity_days' => $data['type'] === AlertRule::TYPE_INACTIVITY ? $data['inactivity_days'] : null,
        ]);

        return $rule->fresh(['variant', 'location']);
    }

    public function findRule(TenantContext $context, int $ruleId): AlertRule
    {
        $rule = AlertRule::query()
            ->with(['variant', 'location'])
            ->where('company_id', $context->company->id)
            ->whereKey($ruleId)
            ->first();

        if ($rule === null) {
            throw (new ModelNotFoundException)->setModel(AlertRule::class, [$ruleId]);
        }

        return $rule;
    }

    public function findAlert(TenantContext $context, int $alertId): StockAlert
    {
        $alert = StockAlert::query()
            ->with(['variant', 'location', 'rule'])
            ->where('company_id', $context->company->id)
            ->whereKey($alertId)
            ->first();

        if ($alert === null) {
            throw (new ModelNotFoundException)->setModel(StockAlert::class, [$alertId]);
        }

        return $alert;
    }

    public function changeAlertStatus(StockAlert $alert, string $status): StockAlert
    {
        $alert->update([
            'status' => $status,
            'resolved_at' => $status === StockAlert::STATUS_RESOLVED ? now() : null,
        ]);

        return $alert->fresh(['variant', 'location', 'rule']);
    }

    public function evaluateCompany(Company $company): AlertEvaluationRun
    {
        $run = AlertEvaluationRun::query()->create([
            'company_id' => $company->id,
            'started_at' => now(),
            'status' => AlertEvaluationRun::STATUS_RUNNING,
        ]);

        $evaluated = 0;
        $generated = 0;
        $resolved = 0;

        try {
            $rules = AlertRule::query()
                ->with(['variant', 'location'])
                ->where('company_id', $company->id)
                ->where('status', AlertRule::STATUS_ACTIVE)
                ->get();

            foreach ($rules as $rule) {
                $evaluated++;
                $condition = $this->evaluateRule($company, $rule);

                $result = $condition['active']
                    ? $this->openAlert($company, $rule, $condition['message'])
                    : $this->resolveAlert($company, $rule);

                if ($condition['active'] && $result) {
                    $generated++;
                }

                if (! $condition['active'] && $result) {
                    $resolved++;
                }
            }

            $run->update([
                'finished_at' => now(),
                'status' => AlertEvaluationRun::STATUS_COMPLETED,
                'evaluated' => $evaluated,
                'generated' => $generated,
                'resolved' => $resolved,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            $run->update([
                'finished_at' => now(),
                'status' => AlertEvaluationRun::STATUS_FAILED,
                'evaluated' => $evaluated,
                'generated' => $generated,
                'resolved' => $resolved,
                'error_summary' => mb_substr($exception->getMessage(), 0, 500),
            ]);
        }

        return $run->fresh();
    }

    /**
     * @return array{active: bool, message: string}
     */
    private function evaluateRule(Company $company, AlertRule $rule): array
    {
        $balance = StockBalance::query()
            ->where('company_id', $company->id)
            ->where('variant_id', $rule->variant_id)
            ->where('location_id', $rule->location_id)
            ->first();

        $quantity = $balance?->quantity ?? '0';

        if ($rule->type === AlertRule::TYPE_MIN_STOCK) {
            $active = (float) $quantity <= (float) $rule->threshold;
            $message = sprintf(
                'Stock de %s en %s por debajo del mínimo (%s <= %s).',
                $rule->variant?->sku ?? 'variante',
                $rule->location?->code ?? 'ubicación',
                $quantity,
                $rule->threshold,
            );

            return ['active' => $active, 'message' => $message];
        }

        $cutoff = now()->subDays((int) $rule->inactivity_days);
        $hasMovement = DB::table('stock_movement_lines as lines')
            ->join('stock_movements as movements', 'movements.id', '=', 'lines.stock_movement_id')
            ->where('lines.company_id', $company->id)
            ->where('lines.variant_id', $rule->variant_id)
            ->where('lines.location_id', $rule->location_id)
            ->where('movements.status', 'confirmed')
            ->where('movements.confirmed_at', '>=', $cutoff)
            ->exists();

        $active = ! $hasMovement && (float) $quantity > 0;
        $message = sprintf(
            '%s en %s sin movimientos en los últimos %d días.',
            $rule->variant?->sku ?? 'variante',
            $rule->location?->code ?? 'ubicación',
            (int) $rule->inactivity_days,
        );

        return ['active' => $active, 'message' => $message];
    }

    private function openAlert(Company $company, AlertRule $rule, string $message): bool
    {
        $dedupKey = "{$rule->type}:{$rule->id}";

        return DB::transaction(function () use ($company, $rule, $message, $dedupKey): bool {
            $alert = StockAlert::query()
                ->where('company_id', $company->id)
                ->where('dedup_key', $dedupKey)
                ->lockForUpdate()
                ->first();

            if ($alert === null) {
                StockAlert::query()->create([
                    'company_id' => $company->id,
                    'alert_rule_id' => $rule->id,
                    'type' => $rule->type,
                    'variant_id' => $rule->variant_id,
                    'location_id' => $rule->location_id,
                    'message' => $message,
                    'status' => StockAlert::STATUS_OPEN,
                    'detected_at' => now(),
                    'dedup_key' => $dedupKey,
                ]);

                return true;
            }

            $wasResolved = $alert->status === StockAlert::STATUS_RESOLVED;

            $alert->update([
                'message' => $message,
                'status' => StockAlert::STATUS_OPEN,
                'detected_at' => $wasResolved ? now() : $alert->detected_at,
                'resolved_at' => null,
            ]);

            return $wasResolved;
        });
    }

    private function resolveAlert(Company $company, AlertRule $rule): bool
    {
        return StockAlert::query()
            ->where('company_id', $company->id)
            ->where('alert_rule_id', $rule->id)
            ->where('status', StockAlert::STATUS_OPEN)
            ->update([
                'status' => StockAlert::STATUS_RESOLVED,
                'resolved_at' => now(),
            ]) > 0;
    }
}
