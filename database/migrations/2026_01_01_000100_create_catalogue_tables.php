<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Horizon Properties');
            $table->string('legal_name')->nullable();
            $table->string('tagline')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_raw')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('address')->nullable();
            $table->string('address_short')->nullable();
            $table->string('office_hours')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('icon')->default('fa-house');
            $table->string('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('status')->default('Active');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('properties', function (Blueprint $table) {
            $table->string('ref')->primary();          // e.g. HP-101
            $table->string('title');
            $table->string('sub_type')->nullable();
            $table->string('category_slug');           // residential | commercial | farmhouse
            $table->string('project_slug')->nullable();
            $table->string('project_name')->nullable();
            $table->string('purpose')->default('For Sale');
            $table->boolean('investment')->default(false);
            $table->unsignedBigInteger('price_pkr')->default(0);
            $table->string('price_formatted')->nullable();
            $table->string('size')->nullable();
            $table->unsignedInteger('size_yds')->default(0);
            $table->unsignedInteger('beds')->default(0);
            $table->unsignedInteger('baths')->default(0);
            $table->unsignedInteger('cars')->default(0);
            $table->string('status')->default('Verified');
            $table->string('status_class')->default('verified');
            $table->boolean('featured')->default(false);
            $table->string('image')->nullable();
            $table->json('gallery')->nullable();
            $table->string('location')->nullable();
            $table->string('address')->nullable();
            $table->json('description')->nullable();    // array of paragraphs
            $table->json('features')->nullable();       // array of strings
            $table->string('agent')->default('team');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('interest')->nullable();
            $table->string('property_ref')->nullable();
            $table->string('property_title')->nullable();
            $table->string('society')->nullable();
            $table->string('budget')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('new');   // new | contacted | closed
            $table->timestamps();
        });

        Schema::create('page_contents', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_contents');
        Schema::dropIfExists('enquiries');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('settings');
    }
};
