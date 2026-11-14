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
            'packages' => \App\Models\PlotPackage::with('project')->orderBy('sort_order')->get(),
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

    /** Attach everything the Projects page shows for one project (info, plans, media, available + sold properties). */
    private function decorateProject(Project $p, bool $flip = false): Project
    {
        $props = Property::where('project_slug', $p->slug)->orderBy('sort_order')->get();
        $p->availableProps = $props->where('is_sold', false)->values();
        $p->soldProps = $props->where('is_sold', true)->values();

        $p->property_count = $p->availableProps->count();
        $p->img = $p->image ?: 'gated_community_aerial';
        $hay = strtolower(($p->tag ?? '') . ' ' . $p->name . ' ' . $p->slug);
        $p->is_farm = str_contains($hay, 'farm') || str_contains($hay, 'orchard');
        $p->sizes = $p->availableProps->pluck('size')->unique()->filter()->values();
        $p->flip = $flip;
        $p->packages = PlotPackage::where('project_slug', $p->slug)->orderBy('sort_order')->get();

        $media = $p->media;
        $p->videos = $media->where('type', 'video')->values();
        $p->layouts = $media->where('type', 'layout')->values();
        $p->locations = $media->where('type', 'location')->values();

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
