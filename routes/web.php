<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('admin.dashboard.view');
})->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/layout/{slug}', [ProfileController::class, 'showPage'])->name('admin.dynamic.page');
Route::group(['namespace' => 'App\Http\Controllers\Admin', 'prefix' => 'admin', 'middleware' => ['auth:admin']], function () {
    Route::get('lang/{lang}', ['as' => 'dashboard.lang', 'uses' => 'DashboardController@getLang']);
    Route::get('dashboard', ['as' => 'dashboard.view', 'middleware' => ['permission:admin.dashboard.view'], 'uses' => 'DashboardController@getIndex']);
    Route::get('logout', ['as' => 'app.logout', 'uses' => 'LoginController@getLogout']);
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('lang/{lang}', ['as' => 'dashboard.lang', 'uses' => 'DashboardController@getLang']);
require __DIR__ . '/auth.php';
