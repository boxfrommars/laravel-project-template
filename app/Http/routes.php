<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', ['as' => 'main', 'uses' => function () {
    return view('welcome');
}]);

// Authentication routes
Route::get('auth/login', ['uses' => 'Auth\AuthController@getLogin', 'as' => 'login']);
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', ['uses' => 'Auth\AuthController@getLogout', 'as' => 'logout']);

// Dashboard routes
Route::group(['middleware' => 'auth', 'namespace' => 'Dashboard', 'prefix' => 'dashboard'], function () {
    Route::get('/', ['uses' => 'MainController@index', 'as' => 'dashboard.main']);
//    Route::resource('article', 'ArticleControllerA');
//    Route::resource('article.section', 'ArticleSectionControllerA');
});