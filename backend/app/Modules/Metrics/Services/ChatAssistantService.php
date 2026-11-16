<?php

namespace App\Modules\Metrics\Services;

use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Metrics\Models\ChatQuery;
use Illuminate\Support\Str;

/**
 * Asistente determinista de solo lectura. No usa LLM externo ni SQL libre.
 */
class ChatAssistantService
{
    public function __construct(private readonly InventoryMetricsService $metrics) {}

    /**
     * @return array<string, mixed>
     */
    public function ask(TenantContext $context, ?User $user, string $question): array
    {
        $start = microtime(true);
        $normalized = Str::of($question)->lower()->ascii()->toString();
        $intent = $this->detectIntent($normalized);

        $result = match (true) {
            $this->isForbidden($normalized) => $this->rejected(),
            $intent === 'stock_lookup' => $this->stockLookup($context, $question, $normalized),
            $intent === 'out_of_stock' => $this->metricsIntent($context, 'out_of_stock'),
            $intent === 'low_stock' => $this->metricsIntent($context, 'low_stock'),
            $intent === 'top_outflows' => $this->metricsIntent($context, 'top_outflows'),
            $intent === 'summary' => $this->metricsIntent($context, 'summary'),
            default => $this->help(),
        };

        ChatQuery::query()->create([
            'company_id' => $context->company->id,
            'user_id' => $user?->id,
            'intent' => $result['intent'],
            'status' => $result['status'],
            'duration_ms' => (int) round((microtime(true) - $start) * 1000),
            'created_at' => now(),
        ]);

        return [
            'intent' => $result['intent'],
            'answer' => $result['answer'],
            'evidence' => $result['evidence'] ?? [],
            'status' => $result['status'],
            'read_only' => true,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    private function detectIntent(string $text): string
    {
        return match (true) {
            str_contains($text, 'agotado') => 'out_of_stock',
            str_contains($text, 'minimo') || str_contains($text, 'bajo stock') => 'low_stock',
            str_contains($text, 'salida') || str_contains($text, 'movimiento') => 'top_outflows',
            str_contains($text, 'resumen') || str_contains($text, 'total') => 'summary',
            str_contains($text, 'stock') || str_contains($text, 'existencia') || str_contains($text, 'sku') || str_contains($text, 'producto') => 'stock_lookup',
            default => 'help',
        };
    }

    private function isForbidden(string $text): bool
    {
        foreach (['ajusta', 'actualiza', 'elimina', 'borra', 'crea', 'confirma', 'revierte', 'modifica', 'sql', 'drop', 'update', 'delete', 'insert', 'ignora', 'otra empresa'] as $word) {
            if (str_contains($text, $word)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function stockLookup(TenantContext $context, string $original, string $normalized): array
    {
        if (! $context->hasPermission('inventory.view')) {
            return $this->forbidden('inventory.view');
        }

        $term = trim(Str::of($original)->replaceMatches('/.*?(stock|existencia|sku|producto|de)\s*/i', '')->toString());
        $term = trim($term) === '' ? trim($original) : $term;

        $balances = StockBalance::query()
            ->with(['variant.product', 'location'])
            ->where('company_id', $context->company->id)
            ->where(function ($query) use ($term): void {
                $query->whereHas('variant', function ($variant) use ($term): void {
                    $variant->where('sku', 'like', "%{$term}%")->orWhere('name', 'like', "%{$term}%")
                        ->orWhereHas('product', fn ($product) => $product->where('name', 'like', "%{$term}%"));
                })->orWhereHas('location', fn ($location) => $location->where('code', 'like', "%{$term}%"));
            })
            ->orderBy('id')
            ->limit(10)
            ->get();

        if ($balances->isEmpty()) {
            return ['intent' => 'stock_lookup', 'status' => 'empty', 'answer' => "No encontré existencias para «{$term}».", 'evidence' => []];
        }

        $lines = $balances->map(fn (StockBalance $balance): string => sprintf(
            '%s (%s) en %s: %s',
            $balance->variant?->name ?? 'Variante',
            $balance->variant?->sku ?? '—',
            $balance->location?->code ?? '—',
            $balance->quantity,
        ));

        return [
            'intent' => 'stock_lookup',
            'status' => 'ok',
            'answer' => "Existencias encontradas:\n".$lines->implode("\n"),
            'evidence' => $balances->map(fn (StockBalance $balance): array => [
                'variant_id' => $balance->variant_id,
                'sku' => $balance->variant?->sku,
                'location_id' => $balance->location_id,
                'quantity' => $balance->quantity,
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function metricsIntent(TenantContext $context, string $intent): array
    {
        if (! $context->hasPermission('metrics.view')) {
            return $this->forbidden('metrics.view');
        }

        $overview = $this->metrics->overview($context, ['days' => 30]);

        $answer = match ($intent) {
            'out_of_stock' => "Hay {$overview['out_of_stock']} combinaciones variante/ubicación agotadas.",
            'low_stock' => "Hay {$overview['low_stock']} existencias por debajo del mínimo configurado.",
            'top_outflows' => $overview['top_outflows'] === [] ? 'No hay salidas confirmadas en los últimos 30 días.' : 'Más salidas: '.collect($overview['top_outflows'])->map(fn ($item) => "{$item['sku']} ({$item['quantity']})")->implode(', '),
            'summary' => "Resumen: {$overview['total_products']} productos, {$overview['total_variants']} variantes, {$overview['out_of_stock']} agotados, {$overview['low_stock']} bajo mínimo y {$overview['total_stock']} unidades en stock.",
            default => 'Consulta completada.',
        };

        return [
            'intent' => $intent,
            'status' => 'ok',
            'answer' => $answer,
            'evidence' => [
                'total_products' => $overview['total_products'],
                'out_of_stock' => $overview['out_of_stock'],
                'low_stock' => $overview['low_stock'],
                'total_stock' => $overview['total_stock'],
                'period_days' => 30,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function rejected(): array
    {
        return [
            'intent' => 'rejected',
            'status' => 'rejected',
            'answer' => 'Solo puedo responder consultas de lectura. No puedo modificar stock, ejecutar SQL ni acceder a otra empresa.',
            'evidence' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function forbidden(string $permission): array
    {
        return [
            'intent' => 'forbidden',
            'status' => 'forbidden',
            'answer' => "No tiene permiso para consultar esa información ({$permission}).",
            'evidence' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function help(): array
    {
        return [
            'intent' => 'help',
            'status' => 'ok',
            'answer' => 'Puedo consultar: stock por producto o SKU, productos agotados, stock bajo mínimo, más salidas del periodo y un resumen. Ejemplo: «stock de café».',
            'evidence' => [],
        ];
    }
}
