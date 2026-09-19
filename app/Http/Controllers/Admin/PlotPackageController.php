<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlotPackage;
use App\Models\Project;
use Illuminate\Http\Request;

class PlotPackageController extends Controller
{
    public function index()
    {
        $packages = PlotPackage::with('project')->orderBy('sort_order')->get();

        return view('admin.plot-packages.index', compact('packages'));
    }

    public function create()
    {
        $projects = Project::orderBy('name')->pluck('name', 'slug');

        return view('admin.plot-packages.form', ['package' => new PlotPackage(), 'projects' => $projects]);
    }

    public function store(Request $request)
    {
        PlotPackage::create($this->validated($request, null));

        return redirect()->route('admin.plot-packages.index')->with('ok', 'Plot package created.');
    }

    public function edit(PlotPackage $plotPackage)
    {
        $projects = Project::orderBy('name')->pluck('name', 'slug');

        return view('admin.plot-packages.form', ['package' => $plotPackage, 'projects' => $projects]);
    }

    public function update(Request $request, PlotPackage $plotPackage)
    {
        $plotPackage->update($this->validated($request, $plotPackage));

        return redirect()->route('admin.plot-packages.index')->with('ok', 'Plot package updated.');
    }

    public function destroy(PlotPackage $plotPackage)
    {
        $plotPackage->delete();

        return redirect()->route('admin.plot-packages.index')->with('ok', 'Plot package removed.');
    }

    private function validated(Request $request, ?PlotPackage $package): array
    {
        $v = $request->validate([
            'project_slug' => 'required|string|exists:projects,slug',
            'label' => 'required|string|max:120',
            'size_kanal' => 'required|numeric|min:0.25|max:1000',
            'price_cash_per_marla' => 'required|integer|min:1000',
            'price_installment_per_marla' => 'required|integer|min:1000',
            'advance_percent' => 'nullable|integer|min:0|max:100',
            'tenure_years' => 'nullable|integer|min:1|max:15',
            'sort_order' => 'nullable|integer',
        ]);

        $v['advance_percent'] = $v['advance_percent'] ?? 25;
        $v['tenure_years'] = $v['tenure_years'] ?? 3;
        $v['is_featured'] = $request->boolean('is_featured');
        $v['sort_order'] = $v['sort_order'] ?? ($package?->sort_order ?? (PlotPackage::max('sort_order') + 1));

        return $v;
    }
}
