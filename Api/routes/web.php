<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/home', 'HomeController@index');
Route::group(['prefix' => 'ajax'], function () {
    Route::post('/de', 'AjaxController@De');
    Route::post('/lo', 'AjaxController@lo');
    Route::post('/cross-setting', 'AjaxController@crossSetting');
    Route::post('/schedule-setting', 'AjaxController@scheduleSetting');
});

Route::get('/updated-activity', 'TelegramBotController@updatedActivity');

Auth::routes();
