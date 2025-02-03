<?php
    
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\LanguageController;

use App\Models\Subscription;
use Illuminate\Support\Facades\Route;
    
// Routes for dervox.com
Route::domain('dervox.com')->group(function () {
    Route::get('/', function () {
        return view('dervox');
    })->name('dervox');  // Named route for home
    Route::get('/about', function () {
        return view('about');
    })->name('about');  // Named route for about
    Route::get('/services', function () {
        return view('services');
    })->name('services');
    Route::get('/solutions', function () {
        return view('solutions');
    })->name('solutions');
});

Route::domain('dim.dervox.com')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('lang', [LanguageController::class, 'change'])->name('change.lang');
    
    Route::get('/dashboard', [DashboardController::class, 'index'])
         ->middleware(['auth'])
         ->name('dashboard');

    Route::get('/a/admin', function(){
        return redirect('/admin');
    })
         ->middleware(['auth', 'verified','admin'])
         ->name('admin');


    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    require __DIR__.'/auth.php';

   

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');

Route::get('/download', [App\Http\Controllers\DownloadController::class, 'index'])->name('download');
Route::get('/download/{os}/{type?}', [App\Http\Controllers\DownloadController::class, 'download'])->name('download.os');


Route::prefix('policies')->group(function () {
    Route::get('/terms', [App\Http\Controllers\PolicyController::class, 'terms'])->name('policies.terms');
    Route::get('/privacy', [App\Http\Controllers\PolicyController::class, 'privacy'])->name('policies.privacy');
    Route::get('/cookies', [App\Http\Controllers\PolicyController::class, 'cookies'])->name('policies.cookies');
    Route::get('/guidelines', [App\Http\Controllers\PolicyController::class, 'guidelines'])->name('policies.guidelines');
    Route::get('/acknowledgments', [App\Http\Controllers\PolicyController::class, 'acknowledgments'])->name('policies.acknowledgments');
    Route::get('/licenses', [App\Http\Controllers\PolicyController::class, 'licenses'])->name('policies.licenses');
    Route::get('/moderation', [App\Http\Controllers\PolicyController::class, 'moderation'])->name('policies.moderation');
}); 

}); 
