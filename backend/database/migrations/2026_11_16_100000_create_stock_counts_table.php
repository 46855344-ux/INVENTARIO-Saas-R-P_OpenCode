<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations');
            $table->string('status', 20)->default('open');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('counted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('reason', 255)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status', 'counted_at']);
        });

        Schema::create('stock_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('stock_count_id')->constrained('stock_counts')->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants');
            $table->decimal('theoretical_quantity', 15, 4);
            $table->decimal('counted_quantity', 15, 4);
            $table->decimal('difference', 15, 4);
            $table->timestamps();

            $table->unique(['stock_count_id', 'variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_count_items');
        Schema::dropIfExists('stock_counts');
    }
};
