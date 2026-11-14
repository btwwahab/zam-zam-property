<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A plan can be cash-only (no installment rate): the installment price becomes optional.
        Schema::table('plot_packages', function (Blueprint $table) {
            $table->unsignedBigInteger('price_installment_per_marla')->nullable()->change();
        });
    }

    public function down(): void
    {
        // cash-only rows get their cash rate so the column can be required again
        DB::table('plot_packages')->whereNull('price_installment_per_marla')->update(['price_installment_per_marla' => DB::raw('price_cash_per_marla')]);
        Schema::table('plot_packages', function (Blueprint $table) {
            $table->unsignedBigInteger('price_installment_per_marla')->nullable(false)->change();
        });
    }
};
