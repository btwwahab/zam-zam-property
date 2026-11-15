<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMedia;
use App\Support\MediaStore;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProjectMediaController extends Controller
{
    public function store(Request $request, Project $project)
    {
        // An upload bigger than PHP's limits arrives empty with an error code; say so
        // plainly instead of "saving" nothing.
        foreach (['video_file' => 60, 'layout_file' => 20] as $field => $limitMb) {
            $f = $request->file($field);
            if ($f && ! $f->isValid()) {
                $tooBig = in_array($f->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);
                throw ValidationException::withMessages([
                    $field => $tooBig ? "That file is too large (max {$limitMb} MB)." : 'That upload could not be read. Please try another file.',
                ]);
            }
        }

        // a form bigger than the server's post limit arrives completely empty
        if (! $request->has('type') && (int) $request->server('CONTENT_LENGTH') > 0) {
            throw ValidationException::withMessages(['gallery_files' => 'That upload was too large for the server. Add fewer photos at a time (about 10).']);
        }

        $type = $request->validate(['type' => 'required|in:'.implode(',', ProjectMedia::TYPES)])['type'];
        $title = $request->validate(['title' => 'nullable|string|max:150'])['title'] ?? null;

        $row = ['project_id' => $project->id, 'type' => $type, 'title' => $title];

        if ($type === 'video') {
            $request->validate([
                'video_url' => 'nullable|string|max:500',
                'video_file' => 'nullable|file|mimes:mp4,webm,ogg|max:61440',
            ], ['video_file.mimes' => 'Please upload an MP4 or WEBM video.', 'video_file.max' => 'The video is too large (max 60 MB).']);

            if ($request->hasFile('video_file')) {
                $name = MediaStore::video($request->file('video_file'), 'project-'.$project->slug);
                if (! $name) {
                    throw ValidationException::withMessages(['video_file' => 'The video could not be saved. Please try another file.']);
                }
                $row['file'] = $name;
            } elseif (ProjectMedia::youtubeId($request->input('video_url'))) {
                $row['url'] = trim($request->input('video_url'));
            } else {
                throw ValidationException::withMessages(['video_url' => 'Paste a YouTube link, or upload an MP4/WEBM file.']);
            }
        } elseif ($type === 'layout') {
            // a map can be an image, or a PDF (vector: stays sharp however far the visitor zooms in)
            $request->validate(['layout_file' => 'required|file'], ['layout_file.required' => 'Choose the map (image or PDF) to upload.']);
            $upload = $request->file('layout_file');

            if (MediaStore::isPdf($upload)) {
                $request->validate(['layout_file' => 'max:20480'], ['layout_file.max' => 'The PDF is too large (max 20 MB).']);
                $name = MediaStore::mapPdf($upload, $project->slug.'-'.($title ?: 'map'));
                $fail = 'That PDF could not be saved. Please try another file.';
            } else {
                $request->validate(['layout_file' => 'image|max:12288'], [
                    'layout_file.image' => 'The map must be a PDF, or a JPG, PNG or WEBP image.',
                    'layout_file.max' => 'The map image is too large (max 12 MB).',
                ]);
                $name = MediaStore::mapImage($upload, $project->slug.'-'.($title ?: 'map'));
                $fail = 'That image could not be processed. Please try another file.';
            }
            if (! $name) {
                throw ValidationException::withMessages(['layout_file' => $fail]);
            }
            $row['file'] = $name;
        } elseif ($type === 'gallery') {
            $files = array_values(array_filter((array) $request->file('gallery_files')));
            foreach ($files as $f) {
                if (! $f->isValid()) {
                    $tooBig = in_array($f->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);
                    throw ValidationException::withMessages([
                        'gallery_files' => $tooBig ? 'One of the photos is too large for the server. Try a smaller photo.' : 'One of the uploads could not be read. Please try again.',
                    ]);
                }
            }
            $request->validate([
                'gallery_files' => 'required|array|min:1|max:20',
                'gallery_files.*' => 'image|max:12288',
            ], [
                'gallery_files.required' => 'Choose at least one photo.',
                'gallery_files.max' => 'Add up to 20 photos at a time.',
                'gallery_files.*.image' => 'Only JPG, PNG or WEBP photos can be added.',
                'gallery_files.*.max' => 'A photo is too large (max 12 MB each).',
            ]);

            $order = (int) ProjectMedia::where('project_id', $project->id)->max('sort_order');
            $added = 0;
            foreach ($files as $f) {
                $name = MediaStore::galleryImage($f, $project->slug.'-gallery');
                if (! $name) {
                    continue;
                }
                ProjectMedia::create([
                    'project_id' => $project->id, 'type' => 'gallery', 'file' => $name,
                    'title' => count($files) === 1 ? $title : null, 'sort_order' => ++$order,
                ]);
                $added++;
            }
            if (! $added) {
                throw ValidationException::withMessages(['gallery_files' => 'Those photos could not be processed. Please try other files.']);
            }

            return back()->with('ok', $added === 1 ? 'Photo added to the gallery.' : "$added photos added to the gallery.");
        } else { // location
            $url = ProjectMedia::cleanMapUrl($request->input('location_url'));
            if (! $url) {
                throw ValidationException::withMessages(['location_url' => 'Paste a Google Maps link (or the "Embed a map" code from Google Maps).']);
            }
            $row['url'] = $url;
        }

        $row['sort_order'] = (int) ProjectMedia::where('project_id', $project->id)->max('sort_order') + 1;
        ProjectMedia::create($row);

        return back()->with('ok', ['video' => 'Video added.', 'layout' => 'Map added.', 'location' => 'Location map added.'][$type]);
    }

    public function destroy(ProjectMedia $media)
    {
        $this->removeFiles($media);
        $media->delete();

        return back()->with('ok', 'Removed.');
    }

    /** Delete an uploaded file belonging to this media row (map image or uploaded video). */
    public static function removeFiles(ProjectMedia $media): void
    {
        if ($media->type === 'layout') {
            MediaStore::forgetFile('maps', $media->file);
            MediaStore::forgetFile('maps', $media->thumb_file);
        } elseif ($media->type === 'gallery') {
            MediaStore::forgetFile('gallery', $media->file);
            MediaStore::forgetFile('gallery', $media->gallery_thumb);
        } elseif ($media->type === 'video') {
            MediaStore::forgetFile('videos', $media->file);
        }
    }
}
