<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Public URLs no longer end in ".html"; tidy the hero button links saved in settings
// (the old addresses still redirect, so nothing breaks if this has no effect).
return new class extends Migration
{
    public function up(): void
    {
        foreach (['hero_cta_primary_link', 'hero_cta_secondary_link'] as $col) {
            foreach (DB::table('settings')->whereNotNull($col)->where($col, 'like', '%.html%')->get(['id', $col]) as $row) {
                DB::table('settings')->where('id', $row->id)->update([
                    $col => preg_replace('/\.html(?=$|[?#])/', '', $row->$col),
                ]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
