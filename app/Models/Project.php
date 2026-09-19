<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = [];

    public function properties()
    {
        return $this->hasMany(Property::class, 'project_slug', 'slug');
    }
}
