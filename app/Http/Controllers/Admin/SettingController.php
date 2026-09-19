<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Models\Setting;
use App\Support\MediaStore;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings', [
            'setting' => Setting::current(),
            'heroSlides' => HeroSlide::orderBy('sort_order')->get(),
            'imageAssets' => $this->imageAssets(),
            'videoAssets' => $this->videoAssets(),
            'heroSwitchable' => (bool) config('app.hero_switchable'),
        ]);
    }

    public function update(Request $request)
    {
        // Catch an oversized upload *before* validation: when a file exceeds PHP's
        // upload_max_filesize / post_max_size it arrives empty with an error code,
        // and Laravel would otherwise wave it through and "save" nothing.
        foreach (['hero_video_file' => 60, 'hero_poster_file' => 8] as $field => $limitMb) {
            $f = $request->file($field);
            if ($f && ! $f->isValid()) {
                $err = $f->getError();
                $msg = in_array($err, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                    ? "That file is too large. Maximum size is {$limitMb} MB — compress the ".($field === 'hero_video_file' ? 'video' : 'image')." and try again."
                    : 'That upload could not be read. Please try a different file.';

                return back()->withErrors([$field => $msg])->withInput();
            }
        }

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'legal_name' => 'nullable|string|max:150',
            'tagline' => 'nullable|string|max:190',
            'phone' => 'nullable|string|max:60',
            'phone_raw' => 'nullable|string|max:40',
            'whatsapp' => 'nullable|string|max:40',
            'address' => 'nullable|string|max:500',
            'address_short' => 'nullable|string|max:190',
            'office_hours' => 'nullable|string|max:120',
            'facebook' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'hero_type' => 'required|in:slider,image,video',
            'hero_video' => 'nullable|string|max:255',
            'hero_poster' => 'nullable|string|max:120',
            'hero_badge' => 'nullable|string|max:120',
            'hero_cta_primary_text' => 'nullable|string|max:60',
            'hero_cta_primary_link' => 'nullable|string|max:255',
            'hero_cta_secondary_text' => 'nullable|string|max:60',
            'hero_cta_secondary_link' => 'nullable|string|max:255',
            'hero_note' => 'nullable|string|max:255',
            'hero_video_file' => 'nullable|file|mimes:mp4,webm,ogg|max:61440',
            'hero_poster_file' => 'nullable|image|max:8192',
        ], [
            'hero_video_file.mimes' => 'That file is not a video we can use. Please upload an MP4 or WEBM.',
            'hero_video_file.max' => 'The video is too large. Maximum size is 60 MB.',
        ]);

        unset($data['hero_video_file'], $data['hero_poster_file']);

        if ($request->hasFile('hero_video_file')) {
            // Only one hero video is kept — delete the previous file first so the
            // new upload takes a clean name and nothing piles up in assets/videos.
            $old = Setting::current()->hero_video;
            if ($old && ! \Illuminate\Support\Str::startsWith($old, ['http://', 'https://', '/'])
                && is_file(public_path("assets/videos/{$old}"))) {
                @unlink(public_path("assets/videos/{$old}"));
            }

            $name = MediaStore::video($request->file('hero_video_file'), 'hero');
            if (! $name) {
                return back()->withErrors(['hero_video_file' => 'The video could not be saved — please try a different file.'])->withInput();
            }
            $data['hero_video'] = $name;
            $data['hero_type'] = 'video';
        }
        if ($request->hasFile('hero_poster_file')) {
            $base = MediaStore::image($request->file('hero_poster_file'), 'hero-poster');
            if (! $base) {
                return back()->withErrors(['hero_poster_file' => 'The image could not be saved — please try a different file.'])->withInput();
            }
            $data['hero_poster'] = $base;
        }

        Setting::current()->update($data);

        return back()->with('ok', 'Site settings saved.');
    }

    public function addSlide(Request $request)
    {
        $data = $request->validate([
            'image' => 'nullable|string|max:120',
            'image_file' => 'nullable|image|max:8192',
        ]);

        $max = 5;
        if (HeroSlide::count() >= $max) {
            return response()->json(['message' => "You can have at most {$max} hero slides."], 422);
        }

        $name = null;
        if ($request->hasFile('image_file')) {
            $name = MediaStore::image($request->file('image_file'), 'hero-slide');
        } elseif (! empty($data['image'])) {
            $name = $data['image'];
        }

        if (! $name) {
            return response()->json(['message' => 'No image was received.'], 422);
        }

        $slide = HeroSlide::create(['image' => $name, 'sort_order' => (HeroSlide::max('sort_order') ?? 0) + 1]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'image' => $name, 'count' => HeroSlide::count()]);
        }

        return back()->with('ok', 'Hero slide added.');
    }

    public function reorderSlides(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer',
        ]);

        foreach (array_values($data['order']) as $i => $id) {
            HeroSlide::where('id', $id)->update(['sort_order' => $i + 1]);
        }

        return response()->json(['ok' => true]);
    }

    public function removeSlide(HeroSlide $slide)
    {
        $image = $slide->image;
        $slide->delete();

        // Delete the underlying image files too — but only if nothing else
        // (another slide, a category, a project, a property, the hero poster)
        // still points at that image, so shared assets stay intact.
        if ($image && ! $this->imageStillUsed($image)) {
            foreach (['webp', 'png', 'jpg', 'jpeg'] as $ext) {
                foreach ([$image, $image.'-sm'] as $base) {
                    $path = public_path("assets/images/{$base}.{$ext}");
                    if (is_file($path)) {
                        @unlink($path);
                    }
                }
            }
        }

        return back()->with('ok', 'Hero slide removed.');
    }

    private function imageStillUsed(string $image): bool
    {
        if (HeroSlide::where('image', $image)->exists()) {
            return true;
        }
        if (\App\Models\Category::where('image', $image)->exists()) {
            return true;
        }
        if (\App\Models\Project::where('image', $image)->exists()) {
            return true;
        }
        if (Setting::where('hero_poster', $image)->exists()) {
            return true;
        }

        // properties: `image` column or anywhere inside the `gallery` JSON
        return \App\Models\Property::where('image', $image)
            ->orWhere('gallery', 'like', '%"'.$image.'"%')
            ->exists();
    }

    private function imageAssets(): array
    {
        $dir = public_path('assets/images');
        $out = [];
        foreach (is_dir($dir) ? scandir($dir) : [] as $f) {
            if (preg_match('/^(.+)\.(webp|png|jpg|jpeg)$/i', $f, $m) && ! str_ends_with($m[1], '-sm')) {
                $out[$m[1]] = true;
            }
        }
        ksort($out);

        return array_keys($out);
    }

    private function videoAssets(): array
    {
        $dir = public_path('assets/videos');
        $out = [];
        foreach (is_dir($dir) ? scandir($dir) : [] as $f) {
            if (preg_match('/\.(mp4|webm|ogg)$/i', $f)) {
                $out[] = $f;
            }
        }

        return $out;
    }
}
