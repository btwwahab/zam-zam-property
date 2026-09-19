<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Property;
use App\Support\MediaStore;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::orderBy('sort_order')->get()->map(function ($p) {
            $p->property_count = Property::where('project_slug', $p->slug)->count();
            return $p;
        });

        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('admin.projects.form', ['project' => new Project()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);
        Project::create($data);

        return redirect()->route('admin.projects.index')->with('ok', 'Project created.');
    }

    public function edit(Project $project)
    {
        return view('admin.projects.form', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $this->validated($request, $project);
        $old = $project->slug;
        $oldImage = $project->image;
        $project->update($data);

        if ($old !== $project->slug) {
            Property::where('project_slug', $old)->update([
                'project_slug' => $project->slug,
                'project_name' => $project->name,
            ]);
        } else {
            Property::where('project_slug', $project->slug)->update(['project_name' => $project->name]);
        }

        if ($oldImage && $oldImage !== $project->image) {
            MediaStore::forgetImageIfUnused($oldImage);
        }

        return redirect()->route('admin.projects.index')->with('ok', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $image = $project->image;
        $project->delete();
        MediaStore::forgetImageIfUnused($image);

        return redirect()->route('admin.projects.index')->with('ok', 'Project deleted.');
    }

    private function validated(Request $request, ?Project $project): array
    {
        $v = $request->validate([
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:150',
            'location' => 'nullable|string|max:190',
            'status' => 'required|string|max:40',
            'image_file' => 'nullable|image|max:8192',
            'tag' => 'nullable|string|max:80',
            'description' => 'nullable|string|max:2000',
            'sort_order' => 'nullable|integer',
        ]);

        unset($v['image_file']);
        if ($request->hasFile('image_file') && $base = MediaStore::image($request->file('image_file'), $request->input('slug') ?: $request->input('name'))) {
            $v['image'] = $base;
        }

        $v['slug'] = Str::slug(($v['slug'] ?? '') ?: $v['name']);
        $v['sort_order'] = $v['sort_order'] ?? ($project->sort_order ?? Project::max('sort_order') + 1);

        return $v;
    }
}
