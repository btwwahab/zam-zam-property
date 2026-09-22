<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Enquiry;
use App\Models\PageContent;
use App\Models\PlotPackage;
use App\Models\Project;
use App\Models\Property;
use App\Models\Setting;
use App\Models\TeamMember;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('pagination.admin');

        // Share site chrome data with every view (public + admin).
        View::composer('*', function ($view) {
            static $shared = null;
            if ($shared === null) {
                $site = Setting::current();
                $shared = [
                    'site' => $site,
                    'siteJs' => [
                        'name' => $site->name,
                        'legalName' => $site->legal_name ?: $site->name,
                        'tagline' => $site->tagline,
                        'phone' => $site->phone,
                        'phoneRaw' => $site->phone_raw,
                        'whatsapp' => $site->whatsapp,
                        'address' => $site->address,
                        'addressShort' => $site->address_short,
                    ],
                    'navCategories' => Category::orderBy('sort_order')->get(),
                    'navProjects' => Project::orderBy('sort_order')->get(),
                    'pages' => PageContent::map(),
                    'imageAssets' => static::scanImages(),
                ];
            }
            $view->with($shared);
        });

        // Sidebar item counts — only queried when the admin layout actually renders.
        View::composer('layouts.admin', function ($view) {
            static $counts = null;
            if ($counts === null) {
                $counts = [
                    'propertiesCount' => Property::count(),
                    'projectsCount' => Project::count(),
                    'categoriesCount' => Category::count(),
                    'plotPackagesCount' => PlotPackage::count(),
                    'teamCount' => TeamMember::count(),
                    'enquiriesCount' => Enquiry::count(),
                    'newEnquiriesCount' => Enquiry::where('status', 'new')->count(),
                ];
            }
            $view->with($counts);
        });
    }

    public static function scanImages(): array
    {
        $dir = public_path('assets/images');
        $out = [];
        foreach (is_dir($dir) ? scandir($dir) : [] as $f) {
            if (preg_match('/^(.+)\.(webp|png|jpg|jpeg)$/i', $f, $m) && ! str_ends_with($m[1], '-sm')) {
                $out[$m[1]] = true;
            }
        }
        ksort($out);

        return array_keys($out);
    }
}
