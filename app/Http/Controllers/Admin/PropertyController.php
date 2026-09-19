<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Project;
use App\Models\Property;
use App\Support\MediaStore;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $q = Property::query()->orderBy('sort_order');

        if ($s = $request->get('q')) {
            $q->where(fn ($w) => $w->where('ref', 'like', "%$s%")->orWhere('title', 'like', "%$s%"));
        }
        if ($c = $request->get('category')) {
            $q->where('category_slug', $c);
        }
        if ($p = $request->get('project')) {
            $q->where('project_slug', $p);
        }
        if ($st = $request->get('status')) {
            $q->where('status', $st);
        }

        return view('admin.properties.index', [
            'properties' => $q->paginate(15)->withQueryString(),
            'categories' => Category::orderBy('sort_order')->get(),
            'projects' => Project::orderBy('sort_order')->get(),
            'filters' => $request->only(['q', 'category', 'project', 'status']),
        ]);
    }

    public function create()
    {
        return view('admin.properties.form', [
            'property' => new Property(),
            'categories' => Category::orderBy('sort_order')->get(),
            'projects' => Project::orderBy('sort_order')->get(),
            'imageAssets' => $this->imageAssets(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);
        Property::create($data);

        return redirect()->route('admin.properties.index')->with('ok', 'Property "'.$data['ref'].'" created.');
    }

    public function edit(Property $property)
    {
        return view('admin.properties.form', [
            'property' => $property,
            'categories' => Category::orderBy('sort_order')->get(),
            'projects' => Project::orderBy('sort_order')->get(),
            'imageAssets' => $this->imageAssets(),
        ]);
    }

    public function update(Request $request, Property $property)
    {
        $data = $this->validated($request, $property);
        $property->update($data);

        return redirect()->route('admin.properties.index')->with('ok', 'Property "'.$property->ref.'" updated.');
    }

    public function destroy(Property $property)
    {
        $ref = $property->ref;
        $property->delete();

        return redirect()->route('admin.properties.index')->with('ok', 'Property "'.$ref.'" deleted.');
    }

    private function validated(Request $request, ?Property $property): array
    {
        $refRule = 'required|string|max:40|unique:properties,ref';
        if ($property) {
            $refRule .= ','.$property->ref.',ref';
        }

        $v = $request->validate([
            'ref' => $refRule,
            'title' => 'required|string|max:190',
            'sub_type' => 'nullable|string|max:120',
            'category_slug' => 'required|exists:categories,slug',
            'project_slug' => 'nullable|exists:projects,slug',
            'purpose' => 'required|string|max:60',
            'investment' => 'nullable|boolean',
            'price_pkr' => 'nullable|integer|min:0',
            'price_formatted' => 'nullable|string|max:60',
            'size' => 'nullable|string|max:60',
            'size_yds' => 'nullable|integer|min:0',
            'status' => 'required|string|max:40',
            'featured' => 'nullable|boolean',
            'image_order' => 'nullable|string',
            'gallery_files' => 'nullable|array|max:10',
            'gallery_files.*' => 'image|max:8192',
            'location' => 'nullable|string|max:190',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'features' => 'nullable|string',
        ]);

        $project = ! empty($v['project_slug']) ? Project::where('slug', $v['project_slug'])->first() : null;

        // ---- Ordered image list. Client sends image_order as a JSON list of
        // tokens: an existing asset name, or "__upload_N" pointing at the Nth
        // file in gallery_files[]. First item = the main image. ------------
        $rawOrder = $request->input('image_order');
        if ($rawOrder === null || $rawOrder === '') {
            // widget not submitted (JS off) — leave images untouched
            $gallery = $property?->gallery ?? [];
        } else {
            $order = json_decode($rawOrder, true);
            $order = is_array($order) ? $order : [];
            $files = array_values($request->file('gallery_files', []));
            $gallery = [];
            foreach ($order as $tok) {
                $tok = (string) $tok;
                if (str_starts_with($tok, '__upload_')) {
                    $idx = (int) substr($tok, 9);
                    if (isset($files[$idx]) && ($b = MediaStore::image($files[$idx], ($v['ref'] ?? 'property')))) {
                        $gallery[] = $b;
                    }
                } elseif ($tok !== '') {
                    $gallery[] = $tok;
                }
            }
            $gallery = array_slice(array_values(array_unique($gallery)), 0, 10);
        }

        return [
            'ref' => $v['ref'],
            'title' => $v['title'],
            'sub_type' => $v['sub_type'] ?? null,
            'category_slug' => $v['category_slug'],
            'project_slug' => $v['project_slug'] ?? null,
            'project_name' => $project?->name,
            'purpose' => $v['purpose'],
            'investment' => $request->boolean('investment'),
            'price_pkr' => $v['price_pkr'] ?? 0,
            'price_formatted' => $v['price_formatted'] ?? null,
            'size' => $v['size'] ?? null,
            'size_yds' => $v['size_yds'] ?? 0,
            'status' => $v['status'],
            'status_class' => strtolower(str_replace(' ', '', $v['status'])),
            'featured' => $request->boolean('featured'),
            'image' => $gallery[0] ?? ($property?->image),
            'gallery' => $gallery,
            'location' => $v['location'] ?? null,
            'address' => $v['address'] ?? null,
            'description' => $this->splitList($v['description'] ?? '', "\n"),
            'features' => $this->splitList($v['features'] ?? '', ','),
        ];
    }

    private function splitList(string $raw, string $sep): array
    {
        return collect(explode($sep, $raw))->map(fn ($x) => trim($x))->filter()->values()->all();
    }

    private function imageAssets(): array
    {
        $dir = public_path('assets/images');
        $files = is_dir($dir) ? scandir($dir) : [];
        $bases = [];
        foreach ($files as $f) {
            if (preg_match('/^(.+)\.(webp|png|jpg|jpeg)$/i', $f, $m)) {
                if (! str_ends_with($m[1], '-sm')) {
                    $bases[$m[1]] = true;
                }
            }
        }
        ksort($bases);
        return array_keys($bases);
    }
}
