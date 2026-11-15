<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMedia extends Model
{
    protected $table = 'project_media';

    protected $guarded = [];

    public const TYPES = ['video', 'layout', 'location', 'gallery'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /** YouTube video id from any common URL shape (watch, youtu.be, shorts, embed, live). */
    public static function youtubeId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        if (preg_match('~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Accepts a plain Google Maps link or a pasted <iframe ...> embed snippet and returns
     * a clean URL, or null if it is not a Google Maps address.
     */
    public static function cleanMapUrl(?string $input): ?string
    {
        $input = trim((string) $input);
        if ($input === '') {
            return null;
        }
        if (preg_match('~<iframe[^>]+src=["\']([^"\']+)["\']~i', $input, $m)) {
            $input = html_entity_decode($m[1]);
        }
        $parts = parse_url($input);
        $host = strtolower($parts['host'] ?? '');
        $ok = ($parts['scheme'] ?? '') === 'https'
            && ($host === 'maps.app.goo.gl' || $host === 'goo.gl' || $host === 'maps.google.com'
                || preg_match('~(^|\.)google\.[a-z.]+$~', $host));

        return $ok ? $input : null;
    }

    /** True when the stored URL can be shown inside an iframe (Google "Embed a map" link). */
    public function getIsEmbeddableMapAttribute(): bool
    {
        return $this->type === 'location' && $this->url
            && (str_contains($this->url, '/maps/embed') || str_contains($this->url, 'output=embed'));
    }

    /** Small version of a map image used in the page grid (the full file opens in the zoom view). */
    public function getThumbFileAttribute(): ?string
    {
        return $this->type === 'layout' && $this->file
            ? preg_replace('/\.webp$/', '-sm.webp', $this->file)
            : null;
    }

    /** Small version of a gallery photo (the full file opens in the viewer). */
    public function getGalleryThumbAttribute(): ?string
    {
        return $this->type === 'gallery' && $this->file
            ? preg_replace('/\.webp$/', '-sm.webp', $this->file)
            : null;
    }

    public function getYoutubeIdAttribute(): ?string
    {
        return $this->type === 'video' ? static::youtubeId($this->url) : null;
    }
}
