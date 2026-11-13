<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Identity\Services\TenantContext;
use App\Modules\Inventory\Exceptions\ConversionNotDefinedException;
use App\Modules\Inventory\Exceptions\IncompatibleUnitsException;
use App\Modules\Inventory\Models\Unit;
use App\Modules\Inventory\Models\UnitConversion;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

class UnitConversionService
{
    /**
     * @param  array{status?: string|null, per_page?: int|null}  $filters
     */
    public function paginate(TenantContext $context, array $filters): LengthAwarePaginator
    {
        return UnitConversion::query()
            ->with(['fromUnit', 'toUnit'])
            ->where('company_id', $context->company->id)
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    /**
     * @param  array{from_unit_id: int, to_unit_id: int, factor: string}  $data
     */
    public function create(TenantContext $context, array $data): UnitConversion
    {
        $from = $this->resolveUnit($context, $data['from_unit_id']);
        $to = $this->resolveUnit($context, $data['to_unit_id']);

        if ($from->dimension !== $to->dimension) {
            throw new IncompatibleUnitsException;
        }

        $conversion = UnitConversion::query()->create([
            'company_id' => $context->company->id,
            'from_unit_id' => $from->id,
            'to_unit_id' => $to->id,
            'factor' => $data['factor'],
            'status' => UnitConversion::STATUS_ACTIVE,
        ]);

        return $conversion->load(['fromUnit', 'toUnit']);
    }

    public function find(TenantContext $context, int $conversionId): UnitConversion
    {
        $conversion = UnitConversion::query()
            ->with(['fromUnit', 'toUnit'])
            ->where('company_id', $context->company->id)
            ->whereKey($conversionId)
            ->first();

        if ($conversion === null) {
            throw (new ModelNotFoundException)->setModel(UnitConversion::class, [$conversionId]);
        }

        return $conversion;
    }

    /**
     * @param  array{factor: string}  $data
     */
    public function update(UnitConversion $conversion, array $data): UnitConversion
    {
        $attributes = Arr::only($data, ['factor']);

        if ($attributes !== []) {
            $conversion->update($attributes);
        }

        return $conversion->fresh(['fromUnit', 'toUnit']);
    }

    public function changeStatus(UnitConversion $conversion, string $status): UnitConversion
    {
        $conversion->update(['status' => $status]);

        return $conversion->fresh(['fromUnit', 'toUnit']);
    }

    /**
     * @return array<string, mixed>
     */
    public function convert(Unit $from, Unit $to, string $quantity): array
    {
        if ($from->dimension !== $to->dimension) {
            throw new IncompatibleUnitsException;
        }

        [$factor, $rule] = $this->resolveFactor($from, $to);

        $quantityDecimal = BigDecimal::of($quantity);
        $result = $quantityDecimal
            ->multipliedBy($factor)
            ->toScale(4, RoundingMode::HalfUp);

        return [
            'from_unit' => $this->unitSummary($from),
            'to_unit' => $this->unitSummary($to),
            'quantity' => (string) $quantityDecimal->toScale(4, RoundingMode::HalfUp),
            'factor' => (string) $factor->toScale(6, RoundingMode::HalfUp),
            'result' => (string) $result,
            'rule' => $rule,
            'rounding' => 'HALF_UP',
            'scale' => 4,
        ];
    }

    /**
     * @return array{0: BigDecimal, 1: string}
     */
    private function resolveFactor(Unit $from, Unit $to): array
    {
        if ((int) $from->id === (int) $to->id) {
            return [BigDecimal::of(1), 'same_unit'];
        }

        $direct = UnitConversion::query()
            ->where('company_id', $from->company_id)
            ->where('from_unit_id', $from->id)
            ->where('to_unit_id', $to->id)
            ->where('status', UnitConversion::STATUS_ACTIVE)
            ->first();

        if ($direct !== null) {
            return [BigDecimal::of($direct->factor), 'direct'];
        }

        $inverse = UnitConversion::query()
            ->where('company_id', $from->company_id)
            ->where('from_unit_id', $to->id)
            ->where('to_unit_id', $from->id)
            ->where('status', UnitConversion::STATUS_ACTIVE)
            ->first();

        if ($inverse !== null) {
            return [
                BigDecimal::of(1)->dividedBy(BigDecimal::of($inverse->factor), 12, RoundingMode::HalfUp),
                'inverse',
            ];
        }

        throw new ConversionNotDefinedException;
    }

    /**
     * @return array<string, mixed>
     */
    private function unitSummary(Unit $unit): array
    {
        return [
            'id' => $unit->id,
            'code' => $unit->code,
            'name' => $unit->name,
            'dimension' => $unit->dimension,
            'precision' => $unit->precision,
        ];
    }

    private function resolveUnit(TenantContext $context, int $unitId): Unit
    {
        $unit = Unit::query()
            ->where('company_id', $context->company->id)
            ->whereKey($unitId)
            ->first();

        if ($unit === null) {
            throw (new ModelNotFoundException)->setModel(Unit::class, [$unitId]);
        }

        return $unit;
    }
}
