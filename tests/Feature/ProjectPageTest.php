<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectMedia;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProjectPageTest extends TestCase
{
    use RefreshDatabase;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        Category::create(['slug' => 'residential', 'name' => 'Residential']);
        $this->project = Project::create(['slug' => 'demo-city', 'name' => 'Demo City', 'location' => 'Multan']);
    }

    private function property(string $ref, bool $sold = false): Property
    {
        return Property::create([
            'ref' => $ref, 'title' => "Plot $ref", 'category_slug' => 'residential',
            'project_slug' => 'demo-city', 'project_name' => 'Demo City',
            'price_pkr' => 1000000, 'price_formatted' => 'PKR 10 Lakh', 'size' => '5 Marla',
            'status' => 'Verified', 'status_class' => 'verified', 'is_sold' => $sold,
        ]);
    }

    public function test_available_properties_show_by_default_and_sold_ones_go_to_the_sold_tab(): void
    {
        $this->property('AV-1');
        $this->property('SO-1', true);

        $html = $this->get('/projects')->assertOk()->getContent();

        // the Available pane is the one that is open by default
        $this->assertStringContainsString('class="tab-pane fade show active" id="tab-available-demo-city"', $html);
        $this->assertStringContainsString('class="tab-pane fade" id="tab-sold-demo-city"', $html);
        [$availablePane, $soldPane] = explode('id="tab-sold-demo-city"', $html);
        $this->assertStringContainsString('Plot AV-1', $availablePane);
        $this->assertStringNotContainsString('Plot SO-1', $availablePane);
        $this->assertStringContainsString('Plot SO-1', $soldPane);
    }

    public function test_old_project_detail_links_redirect_to_the_projects_page(): void
    {
        $this->get('/project-detail.html?id=demo-city')->assertRedirect('/projects#demo-city')->assertStatus(301);
    }

    public function test_no_sold_tab_when_nothing_is_sold(): void
    {
        $this->property('AV-1');
        $this->get('/projects')->assertOk()->assertDontSee('id="tab-sold-btn-demo-city"', false);
    }

    public function test_sold_properties_are_hidden_from_public_listings_but_their_page_still_resolves(): void
    {
        $this->property('AV-1');
        $this->property('SO-1', true);

        $listing = $this->get('/properties')->assertOk()->getContent();
        $this->assertStringContainsString('"AV-1"', $listing);
        $this->assertStringNotContainsString('"SO-1"', $listing);

        $detail = $this->get('/property-detail?id=SO-1')->assertOk()->getContent();
        $this->assertStringContainsString('"SO-1"', $detail);
    }

    public function test_admin_can_toggle_sold_and_it_is_kept_not_deleted(): void
    {
        $this->property('AV-1');
        $admin = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.properties.sold', 'AV-1'))->assertRedirect();
        $this->assertTrue(Property::find('AV-1')->is_sold);

        $this->actingAs($admin)->patch(route('admin.properties.sold', 'AV-1'))->assertRedirect();
        $this->assertFalse(Property::find('AV-1')->is_sold);
    }

    public function test_admin_can_add_video_link_location_and_map_image_and_remove_them(): void
    {
        $admin = User::factory()->create();
        $store = route('admin.projects.media.store', $this->project);

        $this->actingAs($admin)->post($store, ['type' => 'video', 'title' => 'Tour', 'video_url' => 'https://youtu.be/dQw4w9WgXcQ'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post($store, ['type' => 'location', 'location_url' => '<iframe src="https://www.google.com/maps/embed?pb=abc" width="600"></iframe>'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post($store, ['type' => 'layout', 'title' => 'Master plan', 'layout_file' => UploadedFile::fake()->image('plan.png', 1200, 800)])->assertSessionHasNoErrors();

        $media = $this->project->media()->get();
        $this->assertCount(3, $media);
        $this->assertSame('dQw4w9WgXcQ', $media->firstWhere('type', 'video')->youtube_id);
        $this->assertTrue($media->firstWhere('type', 'location')->is_embeddable_map);

        $layout = $media->firstWhere('type', 'layout');
        $path = public_path('assets/maps/'.$layout->file);
        $thumb = public_path('assets/maps/'.$layout->thumb_file);
        $this->assertFileExists($path);
        $this->assertFileExists($thumb);

        $html = $this->get('/projects')->assertOk()->getContent();
        $this->assertStringContainsString('data-yt="dQw4w9WgXcQ"', $html);
        $this->assertStringContainsString('/assets/maps/'.$layout->file, $html);
        $this->assertStringContainsString('google.com/maps/embed', $html);

        $this->actingAs($admin)->delete(route('admin.projects.media.destroy', $layout))->assertRedirect();
        $this->assertFileDoesNotExist($path);
        $this->assertFileDoesNotExist($thumb);
        $this->assertDatabaseMissing('project_media', ['id' => $layout->id]);
    }

    public function test_homepage_shows_every_projects_plans_in_tabs_with_the_first_project_open(): void
    {
        Project::create(['slug' => 'other-city', 'name' => 'Other City', 'location' => 'Multan', 'sort_order' => 5]);
        $mk = fn (string $slug, string $label, int $order) => \App\Models\PlotPackage::create([
            'project_slug' => $slug, 'label' => $label, 'size_kanal' => 1, 'price_cash_per_marla' => 500000,
            'price_installment_per_marla' => 600000, 'advance_percent' => 25, 'tenure_years' => 3, 'sort_order' => $order,
        ]);
        $mk('demo-city', 'DemoCity Plan', 1);
        $mk('other-city', 'OtherCity Plan', 2);

        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('DemoCity Plan', $home);
        $this->assertStringContainsString('OtherCity Plan', $home);
        // two tabs; the project whose plans come first is the one that is open
        $this->assertStringContainsString('id="pkgtab-btn-demo-city"', $home);
        $this->assertStringContainsString('id="pkgtab-btn-other-city"', $home);
        $this->assertStringContainsString('show active" id="pkgtab-demo-city"', $home);
        $this->assertStringNotContainsString('show active" id="pkgtab-other-city"', $home);

        // each project also shows only its own plans on the Projects page
        $this->get('/projects')->assertOk()->assertSee('DemoCity Plan')->assertSee('OtherCity Plan');
    }

    public function test_cash_only_plan_shows_just_the_cash_price(): void
    {
        $mk = fn (string $label, ?int $inst) => \App\Models\PlotPackage::create([
            'project_slug' => 'demo-city', 'label' => $label, 'size_kanal' => 0.25, 'price_cash_per_marla' => 900000,
            'price_installment_per_marla' => $inst, 'advance_percent' => 25, 'tenure_years' => 3, 'sort_order' => $inst ? 2 : 1,
        ]);
        $cash = $mk('CashOnly Plot', null);
        $both = $mk('Both Plot', 1000000);

        $this->assertFalse($cash->has_installment);
        $this->assertSame(4500000.0, $cash->cash_total);       // 5 marla x 900,000
        $this->assertNull($cash->installment_total);
        $this->assertNull($cash->monthly_installment);
        $this->assertTrue($both->has_installment);

        $html = $this->get('/projects')->assertOk()->getContent();
        [$cashCard, $bothCard] = [
            substr($html, strpos($html, 'CashOnly Plot'), 1800),
            substr($html, strpos($html, 'Both Plot'), 1800),
        ];
        $this->assertStringContainsString('PKR 45 Lakh', $cashCard);
        $this->assertStringContainsString('Full payment in cash', $cashCard);
        $this->assertStringNotContainsString('Installments', substr($cashCard, 0, strpos($cashCard, 'Book This Plot')));
        $this->assertStringContainsString('Installments', $bothCard);
    }

    public function test_several_videos_render_as_a_slider_and_one_video_stays_a_plain_player(): void
    {
        ProjectMedia::create(['project_id' => $this->project->id, 'type' => 'video', 'title' => 'One', 'url' => 'https://youtu.be/dQw4w9WgXcQ', 'sort_order' => 1]);
        $html = $this->get('/projects')->assertOk()->getContent();
        $this->assertStringNotContainsString('<div class="video-slider" data-video-slider>', $html);
        $this->assertStringContainsString('data-yt="dQw4w9WgXcQ"', $html);

        ProjectMedia::create(['project_id' => $this->project->id, 'type' => 'video', 'title' => 'Two', 'url' => 'https://youtu.be/LXb3EKWsInQ', 'sort_order' => 2]);
        $html = $this->get('/projects')->assertOk()->getContent();
        $this->assertStringContainsString('<div class="video-slider" data-video-slider>', $html);
        $this->assertSame(2, substr_count($html, 'class="swiper-slide" data-title='));
        $this->assertStringNotContainsString('video-thumb', $html);
        $this->assertStringContainsString('data-title="Two"', $html);
    }

    public function test_bad_media_input_is_rejected(): void
    {
        $admin = User::factory()->create();
        $store = route('admin.projects.media.store', $this->project);

        $this->actingAs($admin)->post($store, ['type' => 'video', 'video_url' => 'https://example.com/not-youtube'])->assertSessionHasErrors('video_url');
        $this->actingAs($admin)->post($store, ['type' => 'location', 'location_url' => 'https://evil.example.com/maps'])->assertSessionHasErrors('location_url');
        $this->actingAs($admin)->post($store, ['type' => 'layout'])->assertSessionHasErrors('layout_file');
        $this->assertSame(0, ProjectMedia::count());
    }

    public function test_media_routes_need_login(): void
    {
        $this->post(route('admin.projects.media.store', $this->project), ['type' => 'video'])->assertRedirect();
        $this->assertSame(0, ProjectMedia::count());
    }

    public function test_contact_form_offers_projects_as_preferred_location(): void
    {
        $html = $this->get('/contact')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<select id="c-society" name="society"[^>]*>.*<option value="Demo City"[^>]*>Demo City<\/option>/s', $html);
    }

    public function test_home_hero_form_has_a_project_dropdown(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<select name="society"[^>]*>.*<option value="Demo City">Demo City<\/option>/s', $html);
    }

    public function test_old_html_addresses_redirect_to_clean_urls(): void
    {
        $this->get('/projects.html')->assertRedirect('/projects')->assertStatus(301);
        $this->get('/about.html')->assertRedirect('/about')->assertStatus(301);
        $this->get('/index.html')->assertRedirect('/')->assertStatus(301);
        $this->get('/property-detail.html?id=SO-1')->assertRedirect('/property-detail?id=SO-1')->assertStatus(301);
        $this->get('/contact')->assertOk();
    }

    public function test_admin_can_add_several_gallery_photos_and_they_show_on_the_project_page(): void
    {
        $admin = User::factory()->create();
        $store = route('admin.projects.media.store', $this->project);

        $this->actingAs($admin)->post($store, ['type' => 'gallery', 'gallery_files' => [
            UploadedFile::fake()->image('one.jpg', 2400, 1600),
            UploadedFile::fake()->image('two.png', 900, 1200),
        ]])->assertSessionHasNoErrors()->assertSessionHas('ok', '2 photos added to the gallery.');

        $photos = $this->project->media()->where('type', 'gallery')->get();
        $this->assertCount(2, $photos);
        foreach ($photos as $g) {
            $this->assertFileExists(public_path('assets/gallery/'.$g->file));
            $this->assertFileExists(public_path('assets/gallery/'.$g->gallery_thumb));
        }
        $this->assertLessThanOrEqual(1800, getimagesize(public_path('assets/gallery/'.$photos[0]->file))[0]);

        // the photos live on the Gallery pages, not on the Projects page
        $projectsPage = $this->get('/projects')->assertOk()->getContent();
        $this->assertStringNotContainsString('/assets/gallery/', $projectsPage);
        $this->assertStringContainsString('href="'.route('gallery.project', 'demo-city').'"', $projectsPage);

        $landing = $this->get('/gallery')->assertOk()->assertSee('Project Galleries')->assertSee('2 photos')->getContent();
        $this->assertStringContainsString(route('gallery.project', 'demo-city'), $landing);

        $page = $this->get('/gallery/demo-city')->assertOk()->getContent();
        $this->assertStringContainsString('/assets/gallery/'.$photos[0]->file, $page);
        $this->assertStringContainsString('/assets/gallery/'.$photos[1]->file, $page);

        // navbar menu: "Gallery" with a link per project
        $this->assertStringContainsString('href="'.route('gallery.project', 'demo-city').'"', $this->get('/')->getContent());
        $this->get('/gallery/no-such-project')->assertNotFound();

        // admin: sidebar entry, per-project gallery manager, and a link from the project's edit page
        $this->actingAs($admin)->get(route('admin.gallery.index'))->assertOk()->assertSee('2 photos');
        $this->actingAs($admin)->get(route('admin.gallery.show', $this->project))->assertOk()->assertSee('Photo gallery')->assertSee('gallery_files[]', false);
        $this->actingAs($admin)->get(route('admin.projects.edit', $this->project))->assertOk()->assertSee(route('admin.gallery.show', $this->project), false)->assertSee('Gallery');

        $first = $photos[0];
        $this->actingAs($admin)->delete(route('admin.projects.media.destroy', $first))->assertRedirect();
        $this->assertFileDoesNotExist(public_path('assets/gallery/'.$first->file));
        $this->assertFileDoesNotExist(public_path('assets/gallery/'.$first->gallery_thumb));
        $this->actingAs($admin)->delete(route('admin.projects.media.destroy', $photos[1]))->assertRedirect();

        $this->get('/gallery/demo-city')->assertOk()->assertSee('Photos coming soon');
    }

    public function test_admin_gallery_pages_need_login(): void
    {
        $this->get(route('admin.gallery.index'))->assertRedirect();
        $this->get(route('admin.gallery.show', $this->project))->assertRedirect();
    }

    public function test_gallery_rejects_non_images(): void
    {
        $admin = User::factory()->create();
        $store = route('admin.projects.media.store', $this->project);
        $this->actingAs($admin)->post($store, ['type' => 'gallery', 'gallery_files' => [UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')]])
            ->assertSessionHasErrors('gallery_files.0');
        $this->actingAs($admin)->post($store, ['type' => 'gallery'])->assertSessionHasErrors('gallery_files');
        $this->assertSame(0, $this->project->media()->where('type', 'gallery')->count());
    }

    public function test_gallery_order_sets_the_cover_photo_shown_on_the_gallery_landing_page(): void
    {
        $admin = User::factory()->create();
        $store = route('admin.projects.media.store', $this->project);
        $this->actingAs($admin)->post($store, ['type' => 'gallery', 'gallery_files' => [
            UploadedFile::fake()->image('a.jpg', 900, 600), UploadedFile::fake()->image('b.jpg', 900, 600),
        ]])->assertSessionHasNoErrors();
        [$a, $b] = $this->project->media()->where('type', 'gallery')->get()->all();

        // first uploaded = cover
        $this->assertStringContainsString($a->gallery_thumb, $this->get('/gallery')->getContent());
        $this->assertStringNotContainsString($b->gallery_thumb, $this->get('/gallery')->getContent());

        // drag B to the front
        $this->actingAs($admin)->postJson(route('admin.gallery.reorder', $this->project), ['order' => [$b->id, $a->id]])->assertOk();
        $landing = $this->get('/gallery')->getContent();
        $this->assertStringContainsString($b->gallery_thumb, $landing);
        $this->assertStringNotContainsString($a->gallery_thumb, $landing);

        // and the project's own gallery follows the same order
        $page = $this->get('/gallery/demo-city')->getContent();
        $this->assertLessThan(strpos($page, $a->file), strpos($page, $b->file));

        // ids from another project, or junk, cannot be reordered / are ignored
        $this->actingAs($admin)->postJson(route('admin.gallery.reorder', $this->project), ['order' => [999999, $a->id, $b->id]])->assertOk();
        $this->assertSame([$a->id, $b->id], $this->project->media()->where('type', 'gallery')->pluck('id')->all());
        $this->actingAs($admin)->postJson(route('admin.gallery.reorder', $this->project), [])->assertStatus(422);

        foreach ([$a, $b] as $m) {
            $this->actingAs($admin)->delete(route('admin.projects.media.destroy', $m));
        }
    }

    public function test_gallery_reorder_needs_login(): void
    {
        $this->postJson(route('admin.gallery.reorder', $this->project), ['order' => [1]])->assertUnauthorized();
    }
}
