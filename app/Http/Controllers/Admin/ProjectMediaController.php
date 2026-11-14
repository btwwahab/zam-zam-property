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
        foreach (['video_file' => 60, 'layout_file' => 12] as $field => $limitMb) {
            $f = $request->file($field);
            if ($f && ! $f->isValid()) {
                $tooBig = in_array($f->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);
                throw ValidationException::withMessages([
                    $field => $tooBig ? "That file is too large (max {$limitMb} MB)." : 'That upload could not be read. Please try another file.',
                ]);
            }
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
            $request->validate(['layout_file' => 'required|image|max:12288'], [
                'layout_file.required' => 'Choose the map image to upload.',
                'layout_file.image' => 'The map must be a JPG, PNG or WEBP image.',
                'layout_file.max' => 'The map image is too large (max 12 MB).',
            ]);
            $name = MediaStore::mapImage($request->file('layout_file'), $project->slug.'-'.($title ?: 'map'));
            if (! $name) {
                throw ValidationException::withMessages(['layout_file' => 'That image could not be processed. Please try another file.']);
            }
            $row['file'] = $name;
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
        } elseif ($media->type === 'video') {
            MediaStore::forgetFile('videos', $media->file);
        }
    }
}
