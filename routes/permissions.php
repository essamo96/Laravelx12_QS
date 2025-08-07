<?php

//Permissions Route
Route::get('permissions', ['as' => 'permissions.view', 'middleware' => ['permission:admin.permissions.view'], 'uses' => 'PermissionsController@getIndex']);
Route::get('permissions/list', ['as' => 'permissions.list', 'middleware' => ['permission:admin.permissions.view'], 'uses' => 'PermissionsController@getList']);
Route::get('permissions/add', ['as' => 'permissions.add', 'middleware' => ['permission:admin.permissions.add'], 'uses' => 'PermissionsController@getAdd']);
Route::post('permissions/add', ['as' => 'permissions.add', 'uses' => 'PermissionsController@postAdd']);
Route::get('permissions/edit/{id}', ['as' => 'permissions.edit', 'middleware' => ['permission:admin.permissions.edit'], 'uses' => 'PermissionsController@getEdit']);
Route::post('permissions/edit/{id}', ['as' => 'permissions.edit', 'middleware' => ['permission:admin.permissions.edit'], 'uses' => 'PermissionsController@postEdit']);
Route::post('permissions/delete', ['as' => 'permissions.delete', 'middleware' => ['permission:admin.permissions.delete'], 'uses' => 'PermissionsController@postDelete']);
Route::post('permissions/status', ['as' => 'permissions.status', 'middleware' => ['permission:admin.permissions.status'], 'uses' => 'PermissionsController@postStatus']);
