<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    /** True once the exact office pin has been entered in the admin. */
    public function hasPin(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /** "lat,lng" for Google Maps URLs. */
    protected function pin(): string
    {
        return $this->latitude.','.$this->longitude;
    }

    /**
     * Where "open in maps" links go: the pasted Google Maps link if there is one,
     * else the exact pin, else a text search on the short address (the old behaviour).
     */
    protected function mapsLink(): Attribute
    {
        return Attribute::get(function () {
            if ($this->maps_url) {
                return $this->maps_url;
            }
            if ($this->hasPin()) {
                return 'https://www.google.com/maps?q='.$this->pin();
            }

            return 'https://maps.google.com/?q='.urlencode((string) $this->address_short);
        });
    }

    /** Source for the embedded map on the contact page. */
    protected function mapsEmbed(): Attribute
    {
        return Attribute::get(function () {
            $q = $this->hasPin() ? $this->pin() : urlencode((string) $this->address_short);

            return 'https://www.google.com/maps?q='.$q.'&output=embed';
        });
    }

    /** Turn-by-turn directions to the exact pin (falls back to the plain map link). */
    protected function directionsLink(): Attribute
    {
        return Attribute::get(function () {
            return $this->hasPin()
                ? 'https://www.google.com/maps/dir/?api=1&destination='.$this->pin()
                : $this->maps_link;
        });
    }
}
