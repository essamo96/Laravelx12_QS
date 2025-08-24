<?php

use Illuminate\Support\Facades\Route;

// Test Routes
Route::get('tests', [
    'as' => 'tests.view',
    'middleware' => ['permission:admin.tests.view'],
    'uses' => 'TestsController@getIndex'
]);

Route::get('tests/list', [
    'as' => 'tests.list',
    'middleware' => ['permission:admin.tests.view'],
    'uses' => 'TestsController@getList'
]);

Route::get('tests/add', [
    'as' => 'tests.add',
    'middleware' => ['permission:admin.tests.add'],
    'uses' => 'TestsController@getAdd'
]);

Route::post('tests/add', [
    'as' => 'tests.add',
    'uses' => 'TestsController@postAdd'
]);

Route::get('tests/edit/{id}', [
    'as' => 'tests.edit',
    'middleware' => ['permission:admin.tests.edit'],
    'uses' => 'TestsController@getEdit'
]);

Route::post('tests/edit/{id}', [
    'as' => 'tests.edit',
    'middleware' => ['permission:admin.tests.edit'],
    'uses' => 'TestsController@postEdit'
]);

Route::post('tests/delete', [
    'as' => 'tests.delete',
    'middleware' => ['permission:admin.tests.delete'],
    'uses' => 'TestsController@postDelete'
]);

Route::post('tests/status', [
    'as' => 'tests.status',
    'middleware' => ['permission:admin.tests.status'],
    'uses' => 'TestsController@postStatus'
]);
