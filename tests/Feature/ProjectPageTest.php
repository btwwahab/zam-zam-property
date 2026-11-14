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

        $html = $this->get('/projects.html')->assertOk()->getContent();

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
        $this->get('/project-detail.html?id=demo-city')->assertRedirect('/projects.html#demo-city')->assertStatus(301);
    }

    public function test_no_sold_tab_when_nothing_is_sold(): void
    {
        $this->property('AV-1');
        $this->get('/projects.html')->assertOk()->assertDontSee('id="tab-sold-btn-demo-city"', false);
    }

    public function test_sold_properties_are_hidden_from_public_listings_but_their_page_still_resolves(): void
    {
        $this->property('AV-1');
        $this->property('SO-1', true);

        $listing = $this->get('/properties.html')->assertOk()->getContent();
        $this->assertStringContainsString('"AV-1"', $listing);
        $this->assertStringNotContainsString('"SO-1"', $listing);

        $detail = $this->get('/property-detail.html?id=SO-1')->assertOk()->getContent();
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

        $html = $this->get('/projects.html')->assertOk()->getContent();
        $this->assertStringContainsString('data-yt="dQw4w9WgXcQ"', $html);
        $this->assertStringContainsString('/assets/maps/'.$layout->file, $html);
        $this->assertStringContainsString('google.com/maps/embed', $html);

        $this->actingAs($admin)->delete(route('admin.projects.media.destroy', $layout))->assertRedirect();
        $this->assertFileDoesNotExist($path);
        $this->assertFileDoesNotExist($thumb);
        $this->assertDatabaseMissing('project_media', ['id' => $layout->id]);
    }

    public function test_several_videos_render_as_a_slider_and_one_video_stays_a_plain_player(): void
    {
        ProjectMedia::create(['project_id' => $this->project->id, 'type' => 'video', 'title' => 'One', 'url' => 'https://youtu.be/dQw4w9WgXcQ', 'sort_order' => 1]);
        $html = $this->get('/projects.html')->assertOk()->getContent();
        $this->assertStringNotContainsString('<div class="video-slider" data-video-slider>', $html);
        $this->assertStringContainsString('data-yt="dQw4w9WgXcQ"', $html);

        ProjectMedia::create(['project_id' => $this->project->id, 'type' => 'video', 'title' => 'Two', 'url' => 'https://youtu.be/LXb3EKWsInQ', 'sort_order' => 2]);
        $html = $this->get('/projects.html')->assertOk()->getContent();
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
}
