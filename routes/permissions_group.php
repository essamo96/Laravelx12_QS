<?php

//PermissionsGroup Route
Route::get('permissions_group', ['as' => 'permissions_group.view', 'middleware' => ['permission:admin.permissions_group.view'], 'uses' => 'PermissionsGroupController@getIndex']);
Route::get('permissions_group/list', ['as' => 'permissions_group.list', 'middleware' => ['permission:admin.permissions_group.view'], 'uses' => 'PermissionsGroupController@getList']);
Route::get('permissions_group/add', ['as' => 'permissions_group.add', 'middleware' => ['permission:admin.permissions_group.add'], 'uses' => 'PermissionsGroupController@getAdd']);
Route::post('permissions_group/add', ['as' => 'permissions_group.add', 'uses' => 'PermissionsGroupController@postAdd']);
Route::get('permissions_group/edit/{id}', ['as' => 'permissions_group.edit', 'middleware' => ['permission:admin.permissions_group.edit'], 'uses' => 'PermissionsGroupController@getEdit']);
Route::post('permissions_group/edit/{id}', ['as' => 'permissions_group.edit', 'middleware' => ['permission:admin.permissions_group.edit'], 'uses' => 'PermissionsGroupController@postEdit']);
Route::post('permissions_group/delete', ['as' => 'permissions_group.delete', 'middleware' => ['permission:admin.permissions_group.delete'], 'uses' => 'PermissionsGroupController@postDelete']);
Route::post('permissions_group/status', ['as' => 'permissions_group.status', 'middleware' => ['permission:admin.permissions_group.status'], 'uses' => 'PermissionsGroupController@postStatus']);
