<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaStore
{
    /**
     * Store an uploaded image as <base>.webp + <base>.png + <base>-sm.webp
     * in public/assets/images and return the base name (no extension),
     * matching the naming convention the templates expect.
     */
    public static function image(UploadedFile $file, string $hint = 'upload'): ?string
    {
        if (! $file->isValid()) {
            return null;
        }

        $data = file_get_contents($file->getRealPath());
        $src = @imagecreatefromstring($data);
        if (! $src) {
            return null;
        }

        $base = static::uniqueBase($hint ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), 'images');
        $dir = public_path('assets/images');
        @mkdir($dir, 0775, true);

        imagepalettetotruecolor($src);
        imagealphablending($src, true);
        imagesavealpha($src, true);

        imagewebp($src, "$dir/$base.webp", 82);
        imagepng($src, "$dir/$base.png");

        // small variant (max 640w) used by card thumbnails
        $w = imagesx($src);
        $h = imagesy($src);
        if ($w > 640) {
            $nw = 640;
            $nh = (int) round($h * (640 / $w));
            $sm = imagecreatetruecolor($nw, $nh);
            imagealphablending($sm, false);
            imagesavealpha($sm, true);
            imagecopyresampled($sm, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagewebp($sm, "$dir/$base-sm.webp", 80);
            imagedestroy($sm);
        } else {
            imagewebp($src, "$dir/$base-sm.webp", 80);
        }

        imagedestroy($src);

        return $base;
    }

    /**
     * Store an uploaded video in public/assets/videos and return the file name.
     */
    public static function video(UploadedFile $file, string $hint = 'hero'): ?string
    {
        if (! $file->isValid()) {
            return null;
        }
        $ext = strtolower($file->getClientOriginalExtension() ?: 'mp4');
        if (! in_array($ext, ['mp4', 'webm', 'ogg'], true)) {
            return null;
        }
        $base = static::uniqueBase($hint, 'videos', $ext);
        $file->move(public_path('assets/videos'), "$base.$ext");

        return "$base.$ext";
    }

    /**
     * Store a layout / master-plan image for a project. Maps are big and need to stay
     * readable when zoomed (plot numbers!), so: WebP only (no PNG twin), long edge capped
     * at 4000px, in assets/maps, plus a 1400px thumbnail (<name>-sm.webp) for the page grid.
     * Returns the file name (with extension) or null.
     */
    public static function mapImage(UploadedFile $file, string $hint = 'map'): ?string
    {
        if (! $file->isValid()) {
            return null;
        }
        // big plans decode to a lot of RAM; give GD room (no-op if the host forbids it)
        @ini_set('memory_limit', '512M');

        $src = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if (! $src) {
            return null;
        }

        imagepalettetotruecolor($src);
        imagealphablending($src, true);
        imagesavealpha($src, true);

        $w = imagesx($src);
        $h = imagesy($src);
        $max = 4000;
        if (max($w, $h) > $max) {
            $r = $max / max($w, $h);
            $nw = (int) round($w * $r);
            $nh = (int) round($h * $r);
            $resized = imagecreatetruecolor($nw, $nh);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($src);
            $src = $resized;
        }

        $dir = public_path('assets/maps');
        @mkdir($dir, 0775, true);
        $base = Str::slug(Str::limit($hint, 40, '')) ?: 'map';
        $name = $base.'-'.substr(md5(uniqid('', true)), 0, 6).'.webp';
        imagewebp($src, "$dir/$name", 85);

        // thumbnail for the project page grid (the full file opens in the zoom view)
        $w = imagesx($src);
        $h = imagesy($src);
        $thumbName = preg_replace('/\.webp$/', '-sm.webp', $name);
        if ($w > 1400) {
            $th = (int) round($h * (1400 / $w));
            $thumb = imagecreatetruecolor(1400, $th);
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            imagecopyresampled($thumb, $src, 0, 0, 0, 0, 1400, $th, $w, $h);
            imagewebp($thumb, "$dir/$thumbName", 82);
            imagedestroy($thumb);
        } else {
            imagewebp($src, "$dir/$thumbName", 82);
        }
        imagedestroy($src);

        return $name;
    }

    /** Remove a stored map image or uploaded project video by file name. */
    public static function forgetFile(string $sub, ?string $name): void
    {
        if (! $name || str_contains($name, '/') || str_contains($name, '..')) {
            return;
        }
        $path = public_path("assets/$sub/$name");
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private static function uniqueBase(string $hint, string $sub, string $ext = 'webp'): string
    {
        $base = Str::slug(Str::limit($hint, 40, '')) ?: 'upload';
        $dir = public_path("assets/$sub");
        $try = $base;
        $i = 1;
        while (file_exists("$dir/$try.$ext") || file_exists("$dir/$try.png")) {
            $try = $base . '-' . $i++;
        }

        return $try;
    }

    /** Seed / shared assets that shipped with the app — never auto-deleted. */
    private const PROTECTED_PREFIXES = [
        'senior_realtor', 'gated_community_aerial', 'designer_house', 'hero_mansion',
        'modern_villa', 'golf_community', 'prime_commercial', 'plots',
        'architectural_floorplan', 'floorplan_sample', 'logo',
    ];

    /** Is this image base still referenced anywhere (or a protected seed asset)? */
    public static function imageReferenced(string $base): bool
    {
        foreach (self::PROTECTED_PREFIXES as $p) {
            if (str_starts_with($base, $p)) {
                return true;
            }
        }

        return \App\Models\Category::where('image', $base)->exists()
            || \App\Models\Project::where('image', $base)->exists()
            || \App\Models\TeamMember::where('image', $base)->exists()
            || \App\Models\HeroSlide::where('image', $base)->exists()
            || \App\Models\Setting::where('hero_poster', $base)->exists()
            || \App\Models\Property::where('image', $base)
                ->orWhere('gallery', 'like', '%"'.$base.'"%')->exists();
    }

    /** Delete <base>.webp / .png / -sm.webp (and jpg/jpeg) — only if nothing else uses it. */
    public static function forgetImageIfUnused(?string $base): void
    {
        if (! $base || self::imageReferenced($base)) {
            return;
        }
        $dir = public_path('assets/images');
        foreach (['webp', 'png', 'jpg', 'jpeg'] as $ext) {
            foreach ([$base, $base.'-sm'] as $n) {
                $path = "$dir/$n.$ext";
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }
    }
}
