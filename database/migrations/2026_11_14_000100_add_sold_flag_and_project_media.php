<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Sold properties stay in the catalogue (nothing is deleted) but move to the
            // "Sold" tab on their project page and drop out of the public listings.
            $table->boolean('is_sold')->default(false)->index();
        });

        Schema::create('project_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('type', 20);              // video | layout | location
            $table->string('title')->nullable();
            $table->text('url')->nullable();         // YouTube link (video) / Google Maps link (location)
            $table->string('file')->nullable();      // uploaded mp4 (video) or map image base name (layout)
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'type', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_media');
        Schema::table('properties', fn (Blueprint $t) => $t->dropColumn('is_sold'));
    }
};
