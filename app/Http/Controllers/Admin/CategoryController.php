<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Property;
use App\Support\MediaStore;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('sort_order')->get()->map(function ($c) {
            $c->property_count = Property::where('category_slug', $c->slug)->count();
            return $c;
        });

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => new Category()]);
    }

    public function store(Request $request)
    {
        Category::create($this->validated($request, null));

        return redirect()->route('admin.categories.index')->with('ok', 'Category created.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request, $category);
        $old = $category->slug;
        $oldImage = $category->image;
        $category->update($data);

        if ($old !== $category->slug) {
            Property::where('category_slug', $old)->update(['category_slug' => $category->slug]);
        }

        if ($oldImage && $oldImage !== $category->image) {
            MediaStore::forgetImageIfUnused($oldImage);
        }

        return redirect()->route('admin.categories.index')->with('ok', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $image = $category->image;
        $category->delete();
        MediaStore::forgetImageIfUnused($image);

        return redirect()->route('admin.categories.index')->with('ok', 'Category deleted.');
    }

    private function validated(Request $request, ?Category $category): array
    {
        $v = $request->validate([
            'name' => 'required|string|max:120',
            'slug' => 'nullable|string|max:120',
            'icon' => 'nullable|string|max:60',
            'image_file' => 'nullable|image|max:8192',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
        ]);

        unset($v['image_file']);
        if ($request->hasFile('image_file') && $base = MediaStore::image($request->file('image_file'), $request->input('slug') ?: $request->input('name'))) {
            $v['image'] = $base;
        }

        $v['slug'] = Str::slug(($v['slug'] ?? '') ?: $v['name']);
        $v['icon'] = ($v['icon'] ?? '') ?: 'fa-house';
        $v['sort_order'] = $v['sort_order'] ?? ($category->sort_order ?? Category::max('sort_order') + 1);

        return $v;
    }
}
