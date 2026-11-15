<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMedia;
use Illuminate\Http\Request;

/**
 * Admin "Gallery" section: pick a project, then add / remove its photos.
 * Uploading and deleting reuse ProjectMediaController (type "gallery").
 */
class GalleryController extends Controller
{
    public function index()
    {
        $projects = Project::with('media')->orderBy('sort_order')->get()->each(function (Project $p) {
            $p->gallery = $p->media->where('type', 'gallery')->values();
        });

        return view('admin.gallery.index', compact('projects'));
    }

    public function show(Project $project)
    {
        $gallery = $project->media()->where('type', 'gallery')->get();
        $projects = Project::orderBy('sort_order')->get();

        return view('admin.gallery.show', compact('project', 'gallery', 'projects'));
    }

    /**
     * Save a new photo order (drag & drop / "Set as cover"). The first photo is the project's cover on the
     * public Gallery page. Only this project's own gallery photos can be reordered; anything else in the list is ignored.
     */
    public function reorder(Request $request, Project $project)
    {
        $data = $request->validate(['order' => 'required|array|max:500', 'order.*' => 'integer']);

        $rows = ProjectMedia::where('project_id', $project->id)->where('type', 'gallery')->get()->keyBy('id');
        $position = 0;
        foreach (array_unique($data['order']) as $id) {
            if ($row = $rows->get((int) $id)) {
                $row->update(['sort_order' => ++$position]);
                $rows->forget((int) $id);
            }
        }
        // photos missing from the list (should not happen) keep their relative order after the listed ones
        foreach ($rows->sortBy('sort_order') as $row) {
            $row->update(['sort_order' => ++$position]);
        }

        return response()->json(['ok' => true]);
    }
}
