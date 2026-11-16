<?php

namespace App\Modules\Metrics\Services;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Services\InventoryMovementService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InventoryMetricsService
{
    public function __construct(private readonly InventoryMovementService $inventory) {}

    /**
     * @param  array{location_id?: int|null, days?: int|null}  $filters
     * @return array<string, mixed>
     */
    public function overview(TenantContext $context, array $filters): array
    {
        $companyId = $context->company->id;
        $days = (int) ($filters['days'] ?? 30);
        $cutoff = now()->subDays($days);
        $locationId = $filters['location_id'] ?? null;

        $totalProducts = Product::query()
            ->where('company_id', $companyId)
            ->where('status', Product::STATUS_ACTIVE)
            ->count();

        $totalVariants = ProductVariant::query()
            ->where('company_id', $companyId)
            ->where('status', ProductVariant::STATUS_ACTIVE)
            ->count();

        $outOfStock = StockBalance::query()
            ->where('company_id', $companyId)
            ->when($locationId, fn ($query) => $query->where('location_id', $locationId))
            ->where('quantity', '<=', 0)
            ->count();

        $lowStock = DB::table('stock_balances')
            ->join('alert_rules', function ($join) use ($companyId): void {
                $join->on('alert_rules.variant_id', '=', 'stock_balances.variant_id')
                    ->on('alert_rules.location_id', '=', 'stock_balances.location_id')
                    ->where('alert_rules.company_id', '=', $companyId)
                    ->where('alert_rules.type', '=', 'min_stock')
                    ->where('alert_rules.status', '=', 'active');
            })
            ->where('stock_balances.company_id', $companyId)
            ->when($locationId, fn ($query) => $query->where('stock_balances.location_id', $locationId))
            ->whereColumn('stock_balances.quantity', '<=', 'alert_rules.threshold')
            ->count();

        $topOutflows = DB::table('stock_movement_lines as lines')
            ->join('stock_movements as movements', 'movements.id', '=', 'lines.stock_movement_id')
            ->join('product_variants as variants', 'variants.id', '=', 'lines.variant_id')
            ->where('lines.company_id', $companyId)
            ->where('movements.type', 'exit')
            ->where('movements.status', 'confirmed')
            ->where('movements.confirmed_at', '>=', $cutoff)
            ->when($locationId, fn ($query) => $query->where('lines.location_id', $locationId))
            ->select(
                'lines.variant_id',
                'variants.sku',
                'variants.name',
                DB::raw('SUM(lines.normalized_quantity) as quantity'),
            )
            ->groupBy('lines.variant_id', 'variants.sku', 'variants.name')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get()
            ->map(fn ($row): array => [
                'variant_id' => (int) $row->variant_id,
                'sku' => (string) $row->sku,
                'name' => (string) $row->name,
                'quantity' => (string) $row->quantity,
            ])
            ->all();

        $inactive = StockBalance::query()
            ->with(['variant', 'location'])
            ->where('company_id', $companyId)
            ->when($locationId, fn ($query) => $query->where('location_id', $locationId))
            ->where('quantity', '>', 0)
            ->whereNotExists(function ($query) use ($companyId, $cutoff): void {
                $query->select(DB::raw(1))
                    ->from('stock_movement_lines as lines')
                    ->join('stock_movements as movements', 'movements.id', '=', 'lines.stock_movement_id')
                    ->whereColumn('lines.variant_id', 'stock_balances.variant_id')
                    ->whereColumn('lines.location_id', 'stock_balances.location_id')
                    ->where('lines.company_id', $companyId)
                    ->where('movements.status', 'confirmed')
                    ->where('movements.confirmed_at', '>=', $cutoff);
            })
            ->orderBy('id')
            ->limit(10)
            ->get()
            ->map(fn (StockBalance $balance): array => [
                'variant_id' => $balance->variant_id,
                'sku' => $balance->variant?->sku,
                'name' => $balance->variant?->name,
                'location_id' => $balance->location_id,
                'location_code' => $balance->location?->code,
                'quantity' => (string) $balance->quantity,
            ])
            ->all();

        return [
            'total_products' => $totalProducts,
            'total_variants' => $totalVariants,
            'total_stock' => (string) StockBalance::query()
                ->where('company_id', $companyId)
                ->when($locationId, fn ($query) => $query->where('location_id', $locationId))
                ->sum('quantity'),
            'out_of_stock' => $outOfStock,
            'low_stock' => $lowStock,
            'total_value' => null,
            'valuation_status' => 'pending',
            'valuation_message' => 'Pendiente de definir método de valorización (DP-02).',
            'top_outflows' => $topOutflows,
            'inactive' => $inactive,
            'period_days' => $days,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function stockDetail(TenantContext $context, array $filters): LengthAwarePaginator
    {
        return $this->inventory->paginateStock($context, $filters);
    }
}
