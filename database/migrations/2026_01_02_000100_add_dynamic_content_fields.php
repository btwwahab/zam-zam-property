<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('hero_type')->default('slider');   // slider | image | video
            $table->string('hero_video')->nullable();          // asset filename or full URL
            $table->string('hero_poster')->nullable();         // fallback image for video
            $table->string('hero_badge')->nullable();
            $table->string('hero_cta_primary_text')->nullable();
            $table->string('hero_cta_primary_link')->nullable();
            $table->string('hero_cta_secondary_text')->nullable();
            $table->string('hero_cta_secondary_link')->nullable();
            $table->string('hero_note')->nullable();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('image')->nullable()->after('icon');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('image')->nullable()->after('status');
            $table->string('tag')->nullable()->after('image');
        });

        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
        Schema::table('projects', fn (Blueprint $t) => $t->dropColumn(['image', 'tag']));
        Schema::table('categories', fn (Blueprint $t) => $t->dropColumn('image'));
        Schema::table('settings', fn (Blueprint $t) => $t->dropColumn([
            'hero_type', 'hero_video', 'hero_poster', 'hero_badge',
            'hero_cta_primary_text', 'hero_cta_primary_link',
            'hero_cta_secondary_text', 'hero_cta_secondary_link', 'hero_note',
        ]));
    }
};
