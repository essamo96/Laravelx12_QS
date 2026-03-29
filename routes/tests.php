<?php

use Illuminate\Support\Facades\Route;

Route::get('tests/list', ['as' => 'tests.list', 'uses' => 'TestsController@list']);
Route::post('tests/status', ['as' => 'tests.status', 'uses' => 'TestsController@status']);
Route::post('tests/delete', ['as' => 'tests.delete', 'middleware' => ['permission:admin.tests.delete'], 'uses' => 'TestsController@postDelete']);

Route::resource('tests', 'TestsController')
    ->parameters(['tests' => 'test'])
    ->names([
        'index' => 'tests.view',
        'create' => 'tests.create',
        'store' => 'tests.store',
        'edit' => 'tests.edit',
        'update' => 'tests.update',
        'destroy' => 'tests.destroy',
    ])
    ->except(['show']);