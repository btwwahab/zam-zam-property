<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\ProjectMediaController;
use App\Models\PlotPackage;
use App\Models\Project;
use App\Models\ProjectMedia;
use App\Models\Property;
use App\Support\MediaStore;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;

/**
 * Fills every project with SAMPLE content (available + sold properties, a map, a location,
 * videos) so the whole project page can be shown in a demo. Everything it creates is
 * recorded in storage/app/demo-content.json, so `--clear` removes exactly that and nothing
 * else (and un-sells anything it marked as sold). Never runs on deploy.
 */
class DemoContent extends Command
{
    protected $signature = 'demo:content {--clear : Remove everything this command added}';

    protected $description = 'Fill every project with sample properties, maps, locations and videos for demos';

    public function handle(): int
    {
        return $this->option('clear') ? $this->clear() : $this->seed();
    }

    private function statePath(): string
    {
        return storage_path('app/demo-content.json');
    }

    private function seed(): int
    {
        if (is_file($this->statePath())) {
            $this->warn('Demo content is already in place. Run  php artisan demo:content --clear  first to reset it.');

            return self::FAILURE;
        }

        $state = ['property_refs' => [], 'sold_flipped' => [], 'media_ids' => [], 'package_ids' => []];
        $heroVideo = public_path('assets/videos/hero.mp4');

        foreach (Project::orderBy('sort_order')->get() as $i => $project) {
            $this->line("<info>{$project->name}</info>");

            // ---- properties: at least 4 available and 2 sold
            $avail = Property::available()->where('project_slug', $project->slug)->orderBy('sort_order')->get();
            $sold = Property::sold()->where('project_slug', $project->slug)->count();

            $flip = max(0, min(2 - $sold, $avail->count() - 3));
            foreach ($avail->take($flip) as $p) {
                $p->update(['is_sold' => true]);
                $state['sold_flipped'][] = $p->ref;
                $sold++;
                $this->line("  marked sold: {$p->ref}");
            }
            $availCount = $avail->count() - $flip;

            $n = 0;
            while ($availCount < 4 || $sold < 2) {
                $makeSold = $sold < 2 && ($availCount >= 4 || $n % 3 === 2);
                $p = $this->makeProperty($project, ++$n, $makeSold);
                $state['property_refs'][] = $p->ref;
                $makeSold ? $sold++ : $availCount++;
                $this->line('  added '.($makeSold ? 'sold     ' : 'available').": {$p->ref}");
            }

            // ---- plot plans (only for projects that have none yet)
            if (! PlotPackage::where('project_slug', $project->slug)->exists()) {
                foreach ($this->addSamplePackages($project) as $id) {
                    $state['package_ids'][] = $id;
                }
                $this->line('  added 3 sample plot plans');
            }

            // ---- media
            $order = (int) ProjectMedia::where('project_id', $project->id)->max('sort_order');
            $add = function (array $row) use ($project, &$order, &$state) {
                $m = ProjectMedia::create($row + ['project_id' => $project->id, 'sort_order' => ++$order]);
                $state['media_ids'][] = $m->id;
                $this->line("  added {$m->type}: ".($m->title ?: '-'));
            };

            if (is_file($heroVideo)) {
                $copy = "demo-{$project->slug}.mp4";
                copy($heroVideo, public_path("assets/videos/$copy"));
                $add(['type' => 'video', 'title' => 'Project walkthrough', 'file' => $copy]);
            }
            if (! $project->media()->where('type', 'layout')->exists()) {
                $png = $this->drawLayout($project->name);
                $name = MediaStore::mapImage(new UploadedFile($png, 'layout.png', 'image/png', null, true), $project->slug.'-layout-sample');
                @unlink($png);
                $name && $add(['type' => 'layout', 'title' => 'Layout plan (sample)', 'file' => $name]);
            }

            $query = trim($project->name.', '.($project->location ?: 'Multan'));
            $add(['type' => 'location', 'title' => 'Location on Google Maps', 'url' => 'https://www.google.com/maps?q='.rawurlencode($query).'&output=embed']);
        }

        file_put_contents($this->statePath(), json_encode($state, JSON_PRETTY_PRINT));
        $this->newLine();
        $this->info('Done. Remove it all later with:  php artisan demo:content --clear');

        return self::SUCCESS;
    }

    private function clear(): int
    {
        if (! is_file($this->statePath())) {
            $this->warn('Nothing to clear (no demo content recorded).');

            return self::SUCCESS;
        }
        $state = json_decode(file_get_contents($this->statePath()), true) ?: [];

        foreach (ProjectMedia::whereIn('id', $state['media_ids'] ?? [])->get() as $m) {
            ProjectMediaController::removeFiles($m);
            $m->delete();
        }
        PlotPackage::whereIn('id', $state['package_ids'] ?? [])->delete();
        Property::whereIn('ref', $state['property_refs'] ?? [])->delete();
        Property::whereIn('ref', $state['sold_flipped'] ?? [])->update(['is_sold' => false]);

        @unlink($this->statePath());
        $this->info('Demo content removed.');

        return self::SUCCESS;
    }

    /**
     * Three SAMPLE plans (5 Marla, 10 Marla, 1 Kanal) with placeholder rates, so a project's
     * "Available Plans" section can be shown in a demo. Returns the new package ids.
     */
    public function addSamplePackages(Project $project): array
    {
        [$cash, $installment] = match ($project->slug) {
            'al-falah-model-city' => [700000, 800000],
            default => [850000, 950000],
        };
        $ids = [];
        foreach ([['5 Marla Residential Plot', 0.25], ['10 Marla Residential Plot', 0.5], ['1 Kanal Residential Plot', 1.0]] as [$label, $kanal]) {
            $ids[] = PlotPackage::create([
                'project_slug' => $project->slug,
                'label' => $label,
                'size_kanal' => $kanal,
                'price_cash_per_marla' => $cash,
                'price_installment_per_marla' => $installment,
                'advance_percent' => 25,
                'tenure_years' => 3,
                'is_featured' => false,
                'sort_order' => (int) PlotPackage::max('sort_order') + 1,
            ])->id;
        }

        return $ids;
    }

    private function makeProperty(Project $project, int $n, bool $sold): Property
    {
        $farm = str_contains(strtolower($project->slug.' '.$project->name.' '.$project->tag), 'orchard')
            || str_contains(strtolower((string) $project->tag), 'farm');

        // [label, sq yd, marla, category, image]
        $sizes = $farm
            ? [['2 Kanal', 1000, 40, 'farmhouse', 'golf_community_estate'], ['4 Kanal', 2000, 80, 'farmhouse', 'golf_community_estate'], ['1 Kanal', 500, 20, 'farmhouse', 'golf_community_estate']]
            : [['5 Marla', 125, 5, 'residential', 'gated_community_aerial'], ['7 Marla', 175, 7, 'residential', 'designer_house_10marla'], ['10 Marla', 250, 10, 'residential', 'modern_villa_1kanal'], ['1 Kanal', 500, 20, 'residential', 'modern_villa_1kanal'], ['4 Marla', 100, 4, 'commercial', 'prime_commercial_arcade']];
        [$label, $yds, $marla, $cat, $img] = $sizes[($n - 1) % count($sizes)];

        $perMarla = match ($project->slug) {
            'zam-zam-orchard' => 230000,
            'al-falah-model-city' => 700000,
            default => 900000,
        };
        $price = $marla * $perMarla;
        $type = $cat === 'commercial' ? 'Commercial Plot' : ($cat === 'farmhouse' ? 'Farmhouse Plot' : 'Residential Plot');
        $status = [['Verified', 'verified'], ['Hot Deal', 'hot'], ['Featured', 'featured']][$n % 3];
        $abbr = strtoupper(collect(preg_split('/[\s-]+/', $project->slug))->map(fn ($w) => substr($w, 0, 1))->join(''));

        return Property::create([
            'ref' => "DEMO-$abbr-$n",
            'title' => "$label $type",
            'sub_type' => $type,
            'category_slug' => $cat,
            'project_slug' => $project->slug,
            'project_name' => $project->name,
            'purpose' => 'For Sale',
            'investment' => $n % 2 === 0,
            'price_pkr' => $price,
            'price_formatted' => money_pk($price),
            'size' => $label,
            'size_yds' => $yds,
            'status' => $status[0],
            'status_class' => $status[1],
            'featured' => false,
            'is_sold' => $sold,
            'image' => $img,
            'gallery' => [$img],
            'location' => $project->name.', '.$project->location,
            'address' => $project->name.', '.$project->location,
            'description' => ['Sample listing created for demonstration purposes.', 'Prices, sizes and availability are indicative. Please confirm current details with the sales office.'],
            'features' => ['Planned layout', 'Wide internal roads', 'Parks and green belts', 'Secure gated community', 'Near main road'],
            'agent' => 'team',
            'sort_order' => (int) Property::max('sort_order') + 1,
        ]);
    }

    /** A simple plot-grid layout drawing, clearly marked as a sample. Returns a temp PNG path. */
    private function drawLayout(string $title): string
    {
        $w = 2000;
        $h = 1400;
        $im = imagecreatetruecolor($w, $h);
        $c = fn (string $hex) => imagecolorallocate($im, hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
        $bg = $c('F7F0DD');
        $road = $c('BDBDBD');
        $ink = $c('1A1A1A');
        $edge = $c('6B6B6B');
        $gold = $c('9A7516');
        $green = $c('2E9B4F');
        $cols = [$c('BFE3F7'), $c('C9E8C9'), $c('FFF3A8'), $c('E2B98C')];
        $labels = ['5 Marla', '7 Marla', '10 Marla', '20 Marla'];
        imagefill($im, 0, 0, $bg);

        $font = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        $ttf = is_file($font);
        $text = function (int $size, int $x, int $y, int $col, string $s, bool $center = false) use ($im, $ttf, $font) {
            if (! $ttf) {
                imagestring($im, 5, $x, $y - 14, $s, $col);

                return;
            }
            if ($center) {
                $b = imagettfbbox($size, 0, $font, $s);
                $x -= (int) (($b[2] - $b[0]) / 2);
            }
            imagettftext($im, $size, 0, $x, $y, $col, $font, $s);
        };

        $text(46, $w / 2, 105, $gold, strtoupper($title).' - LAYOUT PLAN', true);
        imagesetthickness($im, 3);
        imagerectangle($im, 60, 170, $w - 60, $h - 130, $edge);

        // roads
        imagefilledrectangle($im, 970, 171, 1030, $h - 131, $road);
        imagefilledrectangle($im, 61, 700, $w - 61, 760, $road);
        $text(20, 985, 330, $ink, 'ROAD 40-0 WIDE');
        $text(20, 140, 738, $ink, 'ROAD 40-0 WIDE');

        mt_srand(11);
        $no = 1;
        foreach ([[80, 190, 960, 690], [1040, 190, 1920, 690], [80, 770, 960, 1250], [1040, 770, 1920, 1250]] as $q => [$x1, $y1, $x2, $y2]) {
            $cols_n = 8;
            $rows_n = 5;
            $pw = ($x2 - $x1) / $cols_n;
            $ph = ($y2 - $y1) / $rows_n;
            for ($r = 0; $r < $rows_n; $r++) {
                $tone = $cols[mt_rand(0, 3)];
                for ($k = 0; $k < $cols_n; $k++) {
                    $px = (int) ($x1 + $k * $pw);
                    $py = (int) ($y1 + $r * $ph);
                    // leave a park + masjid in the last block
                    if ($q === 3 && $r >= 1 && $r <= 3 && $k >= 2 && $k <= 5) {
                        continue;
                    }
                    imagefilledrectangle($im, $px, $py, (int) ($px + $pw), (int) ($py + $ph), $tone);
                    imagerectangle($im, $px, $py, (int) ($px + $pw), (int) ($py + $ph), $edge);
                    $text(19, (int) ($px + $pw / 2), (int) ($py + $ph / 2 + 8), $ink, (string) $no, true);
                    $no++;
                }
            }
            if ($q === 3) {
                $gx1 = (int) ($x1 + 2 * $pw);
                $gy1 = (int) ($y1 + $ph);
                imagefilledrectangle($im, $gx1, $gy1, (int) ($x1 + 6 * $pw), (int) ($y1 + 4 * $ph), $green);
                $text(34, (int) ($x1 + 4 * $pw), (int) ($y1 + 2.5 * $ph + 12), imagecolorallocate($im, 255, 255, 255), 'PARK', true);
            }
        }

        // legend + sample notice
        foreach ($labels as $i => $label) {
            $lx = 100 + $i * 260;
            imagefilledrectangle($im, $lx, $h - 100, $lx + 44, $h - 66, $cols[$i]);
            imagerectangle($im, $lx, $h - 100, $lx + 44, $h - 66, $edge);
            $text(20, $lx + 58, $h - 72, $ink, $label);
        }
        $text(20, $w - 560, $h - 72, $edge, 'SAMPLE LAYOUT - NOT TO SCALE');

        $path = tempnam(sys_get_temp_dir(), 'layout').'.png';
        imagepng($im, $path);
        imagedestroy($im);

        return $path;
    }
}
