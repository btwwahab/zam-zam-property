<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;

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
}
