<?php

//Roles Route
Route::get('roles', ['as' => 'roles.view', 'middleware' => ['permission:admin.roles.view'], 'uses' => 'RolesController@getIndex']);
Route::get('roles/list', ['as' => 'roles.list', 'middleware' => ['permission:admin.roles.view'], 'uses' => 'RolesController@getList']);
Route::get('roles/add', ['as' => 'roles.add', 'middleware' => ['permission:admin.roles.add'], 'uses' => 'RolesController@getAdd']);
Route::post('roles/add', ['as' => 'roles.add', 'uses' => 'RolesController@postAdd']);
Route::get('roles/edit/{id}', ['as' => 'roles.edit','middleware' => ['permission:admin.roles.edit'], 'uses' => 'RolesController@getEdit']); 
Route::post('roles/edit/{id}', ['as' => 'roles.edit', 'middleware' => ['permission:admin.roles.edit'],'uses' => 'RolesController@postEdit']); 
Route::post('roles/delete', ['as' => 'roles.delete', 'middleware' => ['permission:admin.roles.delete'],'uses' => 'RolesController@postDelete']); 
Route::post('roles/status', ['as' => 'roles.status','middleware' => ['permission:admin.roles.status'], 'uses' => 'RolesController@postStatus']); 
Route::get('roles/permissions/{id}', ['as' => 'roles.permissions', 'middleware' => ['permission:admin.roles.permissions'], 'uses' => 'RolesController@getPermissions']); 
Route::post('roles/permissions/{id}', ['as' => 'roles.permissions','middleware' => ['permission:admin.roles.permissions'], 'uses' => 'RolesController@postPermissions']); 