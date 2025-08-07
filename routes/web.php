<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::group(['namespace' => 'App\Http\Controllers\Admin', 'prefix' => 'admin', 'middleware' => ['guest:admin']], function () {
    Route::get('login', ['as' => 'login', 'uses' => 'LoginController@getIndex']);
    Route::post('login', ['as' => 'login.post', 'uses' => 'LoginController@postIndex']);
});

Route::get('/admin', function () {
    return redirect('admin/dashboard');
});
Route::get('/layout/{slug}', [ProfileController::class, 'showPage'])->name('admin.dynamic.page');

Route::group(['namespace' => 'App\Http\Controllers\Admin', 'prefix' => 'admin', 'middleware' => ['auth:admin']], function () {
    Route::get('lang/{lang}', ['as' => 'dashboard.lang', 'uses' => 'DashboardController@getLang']);
    Route::get('logout', ['as' => 'app.logout', 'uses' => 'LoginController@getLogout']);
    Route::get('dashboard', ['as' => 'dashboard.view', 'middleware' => ['permission:admin.dashboard.view'], 'uses' => 'DashboardController@getIndex']);
    Route::get('notifications/', ['as' => 'notifications.view', 'uses' => 'DashboardController@getIndex']);
    Route::get('main_dashboard', ['as' => 'main_dashboard.view', 'middleware' => ['permission:admin.main_dashboard.view'], 'uses' => 'DashboardController@getIndex']);
    Route::get('constants/', ['as' => 'constants.view', 'uses' => 'DashboardController@getIndex']);
    Route::get('system_settings/', ['as' => 'system_settings.view', 'uses' => 'DashboardController@getIndex']);
    Route::get('users_menu/', ['as' => 'users_menu.view', 'uses' => 'DashboardController@getIndex']);
    Route::get('static_system', ['as' => 'static_system.view', 'middleware' => ['permission:admin.dashboard.view'], 'uses' => 'DashboardController@getIndex']);


    //Roles Route
    require __DIR__ . '/roles.php';

    // //Settings Route
    // require __DIR__ . '/settings.php';

    //permissions Route
    require __DIR__ . '/permissions.php';

    //permissions_group Route
    require __DIR__ . '/permissions_group.php';
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('lang/{lang}', ['as' => 'dashboard.lang', 'uses' => 'DashboardController@getLang']);
require __DIR__ . '/auth.php';
