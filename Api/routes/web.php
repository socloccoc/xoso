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

Route::get('/', 'HomeController@index');
Route::group(['prefix' => 'ajax'], function () {
    Route::post('/de', 'AjaxController@De');
    Route::post('/lo', 'AjaxController@lo');
});

Route::get('/updated-activity', 'TelegramBotController@updatedActivity');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
