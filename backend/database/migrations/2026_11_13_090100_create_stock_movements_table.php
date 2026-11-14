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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('type', 30)->index();
            $table->string('status', 20)->default('confirmed')->index();
            $table->foreignId('user_id')->constrained('users');
            $table->string('reason', 255);
            $table->string('document_reference', 100)->nullable();
            $table->foreignId('original_movement_id')->nullable()->constrained('stock_movements')->nullOnDelete();
            $table->foreignId('reversal_of_movement_id')->nullable()->unique()->constrained('stock_movements')->nullOnDelete();
            $table->string('idempotency_key', 100)->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'idempotency_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
