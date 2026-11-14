<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exact office pin. All three are optional: when they are empty the site
     * falls back to searching Google Maps by the short address text, as before.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('address_short');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('maps_url', 500)->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'maps_url']);
        });
    }
};
