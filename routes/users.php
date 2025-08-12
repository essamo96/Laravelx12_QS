<?php
use Illuminate\Support\Facades\Route;


//Users Route
 Route::get('users', ['as' => 'users.view', 'middleware' => ['permission:admin.users.view'], 'uses' => 'UsersController@getIndex']);
 Route::get('users/list', ['as' => 'users.list', 'middleware' => ['permission:admin.users.view|admin.users.add|admin.users.edit|admin.users.delete|admin.users.status|admin.users.password'], 'uses' => 'UsersController@getList']);
 Route::get('users/add', ['as' => 'users.add', 'middleware' => ['permission:admin.users.add'], 'uses' => 'UsersController@getAdd']);
 Route::post('users/add', ['as' => 'users.add',  'uses' => 'UsersController@postAdd']);
 Route::get('users/edit/{id}', ['as' => 'users.edit','middleware' => ['permission:admin.users.edit'],  'uses' => 'UsersController@getEdit']);//
 Route::post('users/edit/{id}', ['as' => 'users.edit','middleware' => ['permission:admin.users.edit'],  'uses' => 'UsersController@postEdit']);
 Route::get('users/password/{id}', ['as' => 'users.password','middleware' => ['permission:admin.users.password'],  'uses' => 'UsersController@getPassword']);
 Route::post('users/password/{id}', ['as' => 'users.password','middleware' => ['permission:admin.users.password'],  'uses' => 'UsersController@postPassword']);
 Route::post('users/delete', ['as' => 'users.delete', 'middleware' => ['permission:admin.users.delete'], 'uses' => 'UsersController@postDelete']);
 Route::post('users/status', ['as' => 'users.status', 'middleware' => ['permission:admin.users.status'], 'uses' => 'UsersController@postStatus']);
