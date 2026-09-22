<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnquiryController;
use App\Http\Controllers\Admin\PageContentController;
use App\Http\Controllers\Admin\PlotPackageController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

/* -------------------------------------------------------------------------
 |  Public site
 | ------------------------------------------------------------------------ */
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/index.html', [PublicController::class, 'home']);
Route::get('/properties.html', [PublicController::class, 'properties'])->name('properties');
Route::get('/property-detail.html', [PublicController::class, 'propertyDetail'])->name('property.detail');
Route::get('/projects.html', [PublicController::class, 'projects'])->name('projects');
Route::get('/project-detail.html', [PublicController::class, 'projectDetail'])->name('project.detail');
Route::get('/about.html', [PublicController::class, 'about'])->name('about');
Route::get('/contact.html', [PublicController::class, 'contact'])->name('contact');
Route::get('/privacy.html', [PublicController::class, 'privacy'])->name('privacy');
Route::get('/terms.html', [PublicController::class, 'terms'])->name('terms');
Route::post('/enquiries', [PublicController::class, 'storeEnquiry'])->name('enquiries.store');

/* -------------------------------------------------------------------------
 |  Admin panel
 | ------------------------------------------------------------------------ */
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('properties', PropertyController::class)->except(['show']);
        Route::resource('projects', ProjectController::class)->except(['show']);
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('plot-packages', PlotPackageController::class)->except(['show']);
        Route::resource('team', TeamController::class)->except(['show'])->parameters(['team' => 'member']);

        Route::get('enquiries', [EnquiryController::class, 'index'])->name('enquiries.index');
        Route::patch('enquiries/{enquiry}', [EnquiryController::class, 'update'])->name('enquiries.update');
        Route::delete('enquiries/{enquiry}', [EnquiryController::class, 'destroy'])->name('enquiries.destroy');

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('hero-slides', [SettingController::class, 'addSlide'])->name('hero.slide.add');
        Route::post('hero-slides/reorder', [SettingController::class, 'reorderSlides'])->name('hero.slide.reorder');
        Route::delete('hero-slides/{slide}', [SettingController::class, 'removeSlide'])->name('hero.slide.remove');

        Route::get('pages', [PageContentController::class, 'edit'])->name('pages.edit');
        Route::put('pages', [PageContentController::class, 'update'])->name('pages.update');
    });
});
