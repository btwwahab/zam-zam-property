<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaStore
{
    /**
     * Store an uploaded image as <base>.webp + <base>-sm.webp (WebP only — no PNG twin, keeps the disk and every page light)
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

        // Photos straight off a phone/camera are 3000-6000px; the site never shows them bigger
        // than ~1600px, so cap the size (keeps every page light, e.g. the home hero image).
        $maxEdge = 1600;
        if (max(imagesx($src), imagesy($src)) > $maxEdge) {
            $scaled = imagescale($src, imagesx($src) >= imagesy($src) ? $maxEdge : (int) round(imagesx($src) * $maxEdge / imagesy($src)), -1, IMG_BICUBIC);
            if ($scaled) {
                imagedestroy($src);
                $src = $scaled;
                imagealphablending($src, true);
                imagesavealpha($src, true);
            }
        }

        imagewebp($src, "$dir/$base.webp", 78);

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
     *
     * Phone / camera clips are huge (100MB+), so when ffmpeg is installed on the server the clip is
     * re-encoded to a web-friendly H.264 MP4 (max 1280px wide, ~1.5 Mbit/s, moov atom first so it starts
     * playing before it has fully downloaded). If ffmpeg is missing, fails, times out or makes the file
     * bigger, the original upload is kept — a video is never lost because of the optimisation.
     */
    public static function video(UploadedFile $file, string $hint = 'hero'): ?string
    {
        if (! $file->isValid()) {
            return null;
        }
        $ext = strtolower($file->getClientOriginalExtension() ?: 'mp4');
        if (! in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'm4v'], true)) {
            return null;
        }
        $dir = public_path('assets/videos');
        @mkdir($dir, 0775, true);

        $ffmpeg = static::ffmpegBinary();
        if ($ffmpeg) {
            $base = static::uniqueBase($hint, 'videos', 'mp4');
            $out = "$dir/$base.mp4";
            if (static::compressVideo($ffmpeg, $file->getRealPath(), $out) && filesize($out) < $file->getSize()) {
                return "$base.mp4";
            }
            @unlink($out);
        }

        // keep the original (only formats browsers play natively)
        if (! in_array($ext, ['mp4', 'webm', 'ogg'], true)) {
            return null;
        }
        $base = static::uniqueBase($hint, 'videos', $ext);
        $file->move($dir, "$base.$ext");

        return "$base.$ext";
    }

    /** Path of the ffmpeg binary, or null when it is not installed / exec() is disabled. */
    public static function ffmpegBinary(): ?string
    {
        static $bin = false;
        if ($bin !== false) {
            return $bin;
        }
        $bin = null;
        if (! function_exists('proc_open') || ! function_exists('exec')) {
            return null;
        }
        foreach (['/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', '/opt/homebrew/bin/ffmpeg'] as $p) {
            if (is_executable($p)) {
                return $bin = $p;
            }
        }

        return null;
    }

    private static function compressVideo(string $ffmpeg, string $in, string $out): bool
    {
        @set_time_limit(0);
        try {
            $p = new \Symfony\Component\Process\Process([
                $ffmpeg, '-y', '-i', $in,
                '-vf', "scale='min(1280,iw)':-2",
                '-c:v', 'libx264', '-preset', 'veryfast', '-crf', '28', '-pix_fmt', 'yuv420p',
                '-c:a', 'aac', '-b:a', '96k',
                '-movflags', '+faststart',
                $out,
            ]);
            $p->setTimeout(600);
            $p->run();

            return $p->isSuccessful() && is_file($out) && filesize($out) > 0;
        } catch (\Throwable $e) {
            return false;
        }
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

    /**
     * Store a gallery photo (finished work, site progress...) in public/assets/gallery as <name>.webp (max 1800px)
     * plus <name>-sm.webp (700px, used in the grid). Phone photos are rotated by EXIF tags, which GD ignores,
     * so the orientation is applied here. Returns the full file name or null.
     */
    public static function galleryImage(UploadedFile $file, string $hint = 'gallery'): ?string
    {
        if (! $file->isValid()) {
            return null;
        }
        @ini_set('memory_limit', '512M');

        $data = (string) file_get_contents($file->getRealPath());
        $src = @imagecreatefromstring($data);
        if (! $src) {
            return null;
        }

        if (function_exists('exif_read_data') && in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg'], true)) {
            $o = (int) (@exif_read_data($file->getRealPath())['Orientation'] ?? 1);
            $angle = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
            if ($angle && ($rot = imagerotate($src, $angle, 0))) {
                imagedestroy($src);
                $src = $rot;
            }
        }

        imagepalettetotruecolor($src);
        imagealphablending($src, true);
        imagesavealpha($src, true);

        $fit = function ($img, int $edge) {
            $w = imagesx($img);
            $h = imagesy($img);
            if (max($w, $h) <= $edge) {
                return $img;
            }
            $r = $edge / max($w, $h);
            $out = imagecreatetruecolor((int) round($w * $r), (int) round($h * $r));
            imagealphablending($out, false);
            imagesavealpha($out, true);
            imagecopyresampled($out, $img, 0, 0, 0, 0, imagesx($out), imagesy($out), $w, $h);

            return $out;
        };

        $dir = public_path('assets/gallery');
        @mkdir($dir, 0775, true);
        $base = Str::slug(Str::limit($hint, 40, '')) ?: 'gallery';
        $name = $base.'-'.substr(md5(uniqid('', true)), 0, 8).'.webp';

        $full = $fit($src, 1800);
        imagewebp($full, "$dir/$name", 80);
        $thumb = $fit($full, 700);
        imagewebp($thumb, preg_replace('/\.webp$/', '-sm.webp', "$dir/$name"), 80);

        foreach ([$thumb, $full, $src] as $img) {
            if ($img instanceof \GdImage) {
                @imagedestroy($img);
            }
        }

        return $name;
    }

    /**
     * Store a map uploaded as a PDF, untouched (a PDF is vector, so it stays sharp at any zoom), in
     * public/assets/maps. The file must really be a PDF (it starts with "%PDF-"). Returns the file name or null.
     */
    public static function mapPdf(UploadedFile $file, string $hint = 'map'): ?string
    {
        if (! $file->isValid()) {
            return null;
        }
        $head = (string) @file_get_contents($file->getRealPath(), false, null, 0, 5);
        if ($head !== '%PDF-') {
            return null;
        }
        $dir = public_path('assets/maps');
        @mkdir($dir, 0775, true);
        $name = (Str::slug(Str::limit($hint, 40, '')) ?: 'map').'-'.substr(md5(uniqid('', true)), 0, 6).'.pdf';

        return @copy($file->getRealPath(), "$dir/$name") ? $name : null;
    }

    /** Is this uploaded file a PDF? (judged by its content, not its name) */
    public static function isPdf(UploadedFile $file): bool
    {
        return $file->isValid() && (string) @file_get_contents($file->getRealPath(), false, null, 0, 5) === '%PDF-';
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
        'senior_realtor', 'gated_community_aerial', 'project-placeholder', 'designer_house', 'hero_mansion',
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

    /**
     * Uploads used to be stored as WebP + a heavy PNG twin. The site only ever serves the WebP now, so
     * remove the leftover PNG twins of uploaded images (seed art / protected assets are never touched).
     * Returns [files, bytes] removed (or that would be removed when $dry is true).
     */
    public static function prunePngTwins(bool $dry = true, ?callable $each = null): array
    {
        $dir = public_path('assets/images');
        $files = 0;
        $bytes = 0;
        foreach (glob("$dir/*.png") ?: [] as $png) {
            $base = basename($png, '.png');
            $protected = false;
            foreach (self::PROTECTED_PREFIXES as $p) {
                if (str_starts_with($base, $p)) {
                    $protected = true;
                    break;
                }
            }
            if ($protected || str_starts_with($base, 'logo') || ! is_file("$dir/$base.webp")) {
                continue;
            }
            $files++;
            $bytes += (int) filesize($png);
            if ($each) {
                $each(basename($png), (int) filesize($png));
            }
            if (! $dry) {
                @unlink($png);
            }
        }

        return [$files, $bytes];
    }
}
