<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $primaryKey = 'ref';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'gallery' => 'array',
        'description' => 'array',
        'features' => 'array',
        'investment' => 'boolean',
        'featured' => 'boolean',
        'is_sold' => 'boolean',
        'price_pkr' => 'integer',
        'size_yds' => 'integer',
    ];

    /** Still for sale (not marked sold). */
    public function scopeAvailable($query)
    {
        return $query->where('is_sold', false);
    }

    public function scopeSold($query)
    {
        return $query->where('is_sold', true);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_slug', 'slug');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_slug', 'slug');
    }

    /**
     * Exact shape the front-end JS (properties.js / detail.js) expects,
     * mirroring the old static js/data.js records.
     */
    public function toFrontend(): array
    {
        return [
            'id'             => $this->ref,
            'title'          => $this->title,
            'subType'        => $this->sub_type,
            'category'       => $this->category_slug,
            'purpose'        => $this->purpose,
            'investment'     => (bool) $this->investment,
            'project'        => $this->project_slug,
            'projectName'    => $this->project_name,
            'location'       => $this->location,
            'address'        => $this->address,
            'pricePkr'       => (int) $this->price_pkr,
            'priceFormatted' => $this->price_formatted,
            'beds'           => (int) $this->beds,
            'baths'          => (int) $this->baths,
            'cars'           => (int) $this->cars,
            'size'           => $this->size,
            'sizeYds'        => (int) $this->size_yds,
            'status'         => $this->status,
            'statusClass'    => $this->status_class,
            'featured'       => (bool) $this->featured,
            'sold'           => (bool) $this->is_sold,
            'image'          => $this->image,
            'gallery'        => $this->gallery ?: [],
            'agent'          => $this->agent ?: 'team',
            'description'    => $this->description ?: [],
            'features'       => $this->features ?: [],
        ];
    }
}
