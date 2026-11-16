<?php

namespace App\Console\Commands;

use App\Modules\Identity\Models\Company;
use App\Modules\Metrics\Services\InventoryAlertService;
use Illuminate\Console\Command;

class EvaluateAlerts extends Command
{
    protected $signature = 'alerts:evaluate {--company= : ID de empresa opcional}';

    protected $description = 'Evalúa reglas de stock mínimo e inactividad y actualiza alertas';

    public function handle(InventoryAlertService $alerts): int
    {
        $query = Company::query()->where('status', Company::STATUS_ACTIVE);

        if ($this->option('company') !== null) {
            $query->whereKey((int) $this->option('company'));
        }

        $companies = $query->get();

        if ($companies->isEmpty()) {
            $this->warn('No hay empresas activas para evaluar.');

            return self::SUCCESS;
        }

        foreach ($companies as $company) {
            $run = $alerts->evaluateCompany($company);

            $this->line(sprintf(
                'Empresa %d: %s | evaluadas=%d generadas=%d resueltas=%d%s',
                $company->id,
                $run->status,
                $run->evaluated,
                $run->generated,
                $run->resolved,
                $run->error_summary ? ' | error: '.$run->error_summary : '',
            ));
        }

        return self::SUCCESS;
    }
}
