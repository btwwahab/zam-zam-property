<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plot_packages', function (Blueprint $table) {
            $table->id();
            $table->string('project_slug');
            $table->string('label');
            $table->decimal('size_kanal', 6, 2);
            $table->unsignedBigInteger('price_cash_per_marla');
            $table->unsignedBigInteger('price_installment_per_marla');
            $table->unsignedTinyInteger('advance_percent')->default(25);
            $table->unsignedTinyInteger('tenure_years')->default(3);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plot_packages');
    }
};
