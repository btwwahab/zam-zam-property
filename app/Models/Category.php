<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    public function properties()
    {
        return $this->hasMany(Property::class, 'category_slug', 'slug');
    }
}
