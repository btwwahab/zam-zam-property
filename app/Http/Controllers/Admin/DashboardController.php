<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Project;
use App\Models\Property;

class DashboardController extends Controller
{
    public function index()
    {
        $byCategory = Category::orderBy('sort_order')->get()->map(fn ($c) => [
            'name' => $c->name,
            'count' => Property::where('category_slug', $c->slug)->count(),
        ]);

        $byProject = Project::orderBy('sort_order')->get()->map(fn ($p) => [
            'name' => $p->name,
            'count' => Property::where('project_slug', $p->slug)->count(),
        ]);

        return view('admin.dashboard', [
            'totalProperties' => Property::count(),
            'featuredCount' => Property::where('featured', true)->count(),
            'projectsCount' => Project::count(),
            'newEnquiries' => Enquiry::where('status', 'new')->count(),
            'totalEnquiries' => Enquiry::count(),
            'byCategory' => $byCategory,
            'byProject' => $byProject,
            'recentEnquiries' => Enquiry::latest()->take(5)->get(),
            'latestProperties' => Property::with(['category', 'project'])->orderBy('sort_order')->take(5)->get(),
        ]);
    }
}
