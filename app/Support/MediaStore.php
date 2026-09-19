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
