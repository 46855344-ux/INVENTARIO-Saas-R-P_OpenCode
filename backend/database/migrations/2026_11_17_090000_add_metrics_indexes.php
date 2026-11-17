<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(
                ['company_id', 'type', 'confirmed_at'],
                'stock_movements_company_type_confirmed_index',
            );
        });

        Schema::table('stock_movement_lines', function (Blueprint $table) {
            $table->index(
                ['company_id', 'variant_id', 'location_id'],
                'stock_movement_lines_company_variant_location_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('stock_movements_company_type_confirmed_index');
        });

        Schema::table('stock_movement_lines', function (Blueprint $table) {
            $table->dropIndex('stock_movement_lines_company_variant_location_index');
        });
    }
};
