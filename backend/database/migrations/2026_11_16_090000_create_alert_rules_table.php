<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('type', 20);
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->decimal('threshold', 15, 4)->nullable();
            $table->unsignedSmallInteger('inactivity_days')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'type', 'variant_id', 'location_id']);
            $table->index(['company_id', 'status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_rules');
    }
};
