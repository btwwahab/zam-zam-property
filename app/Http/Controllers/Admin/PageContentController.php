<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use Illuminate\Http\Request;

class PageContentController extends Controller
{
    /**
     * The only page text exposed for editing. Plain text only — no HTML,
     * no JSON. Everything else keeps its default and still renders on site.
     * Row: [key, label, type(text|textarea|textarea-lg), where-it-shows]
     */
    public static function fields(): array
    {
        return [
            'Home page' => [
                ['home_hero_heading', 'Main headline', 'text', 'The big heading on the homepage banner'],
                ['home_hero_subheading', 'Line under the headline', 'textarea', 'Short sentence below the main headline'],
                ['home_packages_badge', 'Plot Packages — small badge', 'text', 'Above the pricing cards section'],
                ['home_packages_heading', 'Plot Packages — heading', 'text', 'Title above the pricing cards section'],
                ['home_about_heading', 'About block — heading', 'text', 'The short "about us" section further down the homepage'],
                ['home_about_paragraph', 'About block — text', 'textarea', ''],
                ['home_cta_heading', 'Bottom banner — heading', 'text', 'The dark call-to-action band just above the footer'],
                ['home_cta_text', 'Bottom banner — text', 'textarea', ''],
            ],
            'About page' => [
                ['about_hero_badge', 'Small badge pill (above the title)', 'text', ''],
                ['about_hero_heading', 'Page title', 'text', 'Heading at the top of the About page'],
                ['about_hero_sub', 'Line under the title', 'textarea', ''],
                ['about_overview', 'Company overview — paragraph 1', 'textarea', ''],
                ['about_overview_p2', 'Company overview — paragraph 2', 'textarea', ''],
                ['about_mission', 'Our Mission', 'textarea', ''],
                ['about_vision', 'Our Vision', 'textarea', ''],
            ],
            'Contact page' => [
                ['contact_hero_badge', 'Small badge pill (above the title)', 'text', ''],
                ['contact_hero_heading', 'Page title', 'text', ''],
                ['contact_hero_sub', 'Line under the title', 'textarea', ''],
                ['contact_form_note', 'Note above the enquiry form', 'textarea', ''],
            ],
            'Projects page' => [
                ['projects_hero_badge', 'Small badge pill (above the title)', 'text', ''],
                ['projects_hero_heading', 'Page title', 'text', ''],
                ['projects_hero_sub', 'Line under the title', 'textarea', ''],
            ],
            'Privacy & Terms' => [
                ['privacy_body', 'Privacy Policy', 'textarea-lg', 'A short line on its own = a heading. Blank line between blocks.'],
                ['terms_body', 'Terms & Conditions', 'textarea-lg', 'A short line on its own = a heading. Blank line between blocks.'],
            ],
        ];
    }

    public function edit()
    {
        return view('admin.pages', [
            'groups' => static::fields(),
            'pages' => PageContent::map(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'pages' => 'required|array',
            'pages.*' => 'nullable|string',
        ]);

        $allowed = collect(static::fields())->flatMap(fn ($rows) => collect($rows)->pluck(0))->all();

        foreach ($data['pages'] as $key => $value) {
            if (in_array($key, $allowed, true)) {
                PageContent::updateOrCreate(['key' => $key], ['value' => (string) $value]);
            }
        }

        return back()->with('ok', 'Page text saved.');
    }
}
