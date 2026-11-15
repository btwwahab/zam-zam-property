<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\HeroSlide;
use App\Models\PlotPackage;
use App\Models\Project;
use App\Models\Property;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    /** Sold properties stay out of the public listings; only their detail page still resolves. */
    private function propertiesJson(bool $withSold = false): array
    {
        return Property::when(! $withSold, fn ($q) => $q->available())
            ->orderBy('sort_order')->get()
            ->map(fn (Property $p) => $p->toFrontend())->all();
    }

    private function agentsJson(): array
    {
        $out = [];
        foreach (TeamMember::orderBy('sort_order')->get() as $t) {
            $key = $t->id === 1 ? 'team' : 'team' . $t->id;
            $out[$key] = [
                'name' => $t->name,
                'role' => $t->role,
                'image' => $t->image,
                'phoneRaw' => $t->phone ? preg_replace('/[^+\d]/', '', $t->phone) : null,
                'whatsapp' => $t->whatsapp,
            ];
        }
        if (! isset($out['team'])) {
            $out['team'] = ['name' => 'Property Team', 'role' => 'Property Team', 'image' => 'senior_realtor_male', 'phoneRaw' => null, 'whatsapp' => null];
        }
        return $out;
    }

    public function home()
    {
        return view('public.home', [
            'active' => 'home',
            'team' => \App\Models\TeamMember::orderBy('sort_order')->get(),
            // plans grouped by project, in package order: the project whose plans came first (Zam Zam Orchard) is the first, default tab
            'packageGroups' => \App\Models\PlotPackage::with('project')->orderBy('sort_order')->get()->groupBy('project_slug'),
            'propertiesJson' => $this->propertiesJson(),
            'agentsJson' => $this->agentsJson(),
            'heroSlides' => HeroSlide::orderBy('sort_order')->get(),
        ]);
    }

    public function properties()
    {
        return view('public.properties', [
            'active' => 'properties',
            'propertiesJson' => $this->propertiesJson(),
            'agentsJson' => $this->agentsJson(),
        ]);
    }

    public function propertyDetail()
    {
        return view('public.property-detail', [
            'active' => 'properties',
            'propertiesJson' => $this->propertiesJson(true),
            'agentsJson' => $this->agentsJson(),
        ]);
    }

    /** "5 Marla" -> 5, "1 Kanal" -> 20, "2.5 Kanal" -> 50. Unparseable text sorts last. */
    private function sizeInMarla(string $size): float
    {
        if (preg_match('/([\d.]+)\s*(marla|kanal)/i', $size, $m)) {
            return (float) $m[1] * (strtolower($m[2]) === 'kanal' ? 20 : 1);
        }

        return INF;
    }

    /** Attach everything the Projects page shows for one project (info, plans, media, available + sold properties). */
    private function decorateProject(Project $p, bool $flip = false): Project
    {
        $props = Property::where('project_slug', $p->slug)->orderBy('sort_order')->get();
        $p->availableProps = $props->where('is_sold', false)->values();
        $p->soldProps = $props->where('is_sold', true)->values();

        $p->property_count = $p->availableProps->count();
        // a project without its own photo gets the neutral brand placeholder, never another project's picture
        $p->img = $p->image ?: 'project-placeholder';
        $hay = strtolower(($p->tag ?? '') . ' ' . $p->name . ' ' . $p->slug);
        $p->is_farm = str_contains($hay, 'farm') || str_contains($hay, 'orchard');
        $p->flip = $flip;
        $p->packages = PlotPackage::where('project_slug', $p->slug)->orderBy('sort_order')->get();

        // Plot sizes on offer = sizes of the available listings plus every size the project has a plan for
        // (a plan-only size, or one whose listings are all sold, must still show), smallest first.
        $p->sizes = $p->availableProps->pluck('size')
            ->merge($p->packages->map(fn ($pkg) => $pkg->size_label))
            ->map(fn ($s) => trim((string) $s))
            ->filter()
            ->unique(fn ($s) => strtolower($s))
            ->sortBy(fn ($s) => $this->sizeInMarla($s))
            ->values();

        $media = $p->media;
        $p->videos = $media->where('type', 'video')->values();
        $p->layouts = $media->where('type', 'layout')->values();
        $p->locations = $media->where('type', 'location')->values();
        $p->gallery = $media->where('type', 'gallery')->values();

        return $p;
    }

    public function projects()
    {
        $projects = Project::orderBy('sort_order')->get()->values()
            ->map(fn (Project $p, $i) => $this->decorateProject($p, $i % 2 === 1));

        return view('public.projects', [
            'active' => 'projects',
            'projects' => $projects,
            // the slider library (~165 KB) is only sent when a project actually has several videos
            'needsSwiper' => $projects->contains(fn (Project $p) => $p->videos->count() > 1),
        ]);
    }

    /** Gallery landing page: one card per project, each opening that project's own gallery. */
    public function gallery()
    {
        $projects = Project::with('media')->orderBy('sort_order')->get()->each(function (Project $p) {
            $p->gallery = $p->media->where('type', 'gallery')->values();
        });

        return view('public.gallery', ['active' => 'gallery', 'projects' => $projects]);
    }

    public function galleryProject(string $slug)
    {
        $project = Project::with('media')->where('slug', $slug)->firstOrFail();
        $project->gallery = $project->media->where('type', 'gallery')->values();

        return view('public.gallery-project', ['active' => 'gallery', 'project' => $project]);
    }

    public function about()
    {
        return view('public.about', [
            'active' => 'about',
            'team' => \App\Models\TeamMember::orderBy('sort_order')->get(),
        ]);
    }

    public function contact()
    {
        return view('public.contact', [
            'active' => 'contact',
            'propertiesJson' => $this->propertiesJson(),
            'agentsJson' => $this->agentsJson(),
        ]);
    }

    public function privacy()
    {
        return view('public.privacy', ['active' => '']);
    }

    public function terms()
    {
        return view('public.terms', ['active' => '']);
    }

    /** JSON endpoint hit by js/main.js when an enquiry form is submitted. */
    public function storeEnquiry(Request $request)
    {
        $wantsJson = $request->ajax() || $request->wantsJson();

        // honeypot
        if ($request->filled('zzhp_field')) {
            return $wantsJson ? response()->json(['ok' => true]) : back()->with('enquiry_ok', 1);
        }

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:60',
            'email' => 'nullable|email|max:190',
            'interest' => 'nullable|string|max:120',
            'society' => 'nullable|string|max:190',
            'budget' => 'nullable|string|max:120',
            'propertyId' => 'nullable|string|max:60',
            'propertyTitle' => 'nullable|string|max:190',
            'message' => 'nullable|string|max:2000',
        ]);

        Enquiry::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'interest' => $data['interest'] ?? null,
            'society' => $data['society'] ?? null,
            'budget' => $data['budget'] ?? null,
            'property_ref' => $data['propertyId'] ?? null,
            'property_title' => $data['propertyTitle'] ?? null,
            'message' => $data['message'] ?? null,
            'status' => 'new',
        ]);

        return $wantsJson
            ? response()->json(['ok' => true])
            : back()->with('enquiry_ok', 1);
    }
}
