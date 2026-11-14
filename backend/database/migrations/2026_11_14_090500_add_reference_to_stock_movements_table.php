<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('reference_type', 30)->nullable()->after('document_reference');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');

            $table->index(
                ['company_id', 'reference_type', 'reference_id'],
                'stock_movements_company_reference_index',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('stock_movements_company_reference_index');
            $table->dropColumn(['reference_type', 'reference_id']);
        });
    }
};
