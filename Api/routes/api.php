<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'Api', 'prefix' => 'v1'], function () {
    // user
    Route::resource('user', 'UserApiController');
    Route::post('checkUserExist', [
        'as' => 'user.check',
        'uses' => 'UserApiController@checkUserExist',
    ]);
    Route::post('getUserByParam', [
        'as' => 'user.getUserByParam',
        'uses' => 'UserApiController@getUserByParam',
    ]);

    // customer
    Route::resource('customer', 'CustomerApiController');
    Route::post('getCustomerByUser', [
        'as' => 'customer.by.user',
        'uses' => 'CustomerApiController@getCustomerByUser',
    ]);
    Route::get('getCustomerById/{id}', [
        'as' => 'customer.by.id',
        'uses' => 'CustomerApiController@getCustomerById',
    ]);

    // daily
    Route::resource('daily', 'DailyApiController');
    Route::get('dailyLatestDate', [
        'as' => 'daily.latest.date',
        'uses' => 'DailyApiController@dailyLatestDate',
    ]);

    // customer_daily
    Route::resource('customerDaily', 'CustomerDailyApiController');
    Route::post('getListCustomerDaily', [
        'as' => 'customer.daily.list',
        'uses' => 'CustomerDailyApiController@getListCustomerDaily',
    ]);

    // ticket
    Route::resource('ticket', 'TicketApiController');
    Route::post('getTickets', [
        'as' => 'tickets',
        'uses' => 'TicketApiController@getTickets',
    ]);
    Route::post('getTicketByParam', [
        'as' => 'ticket.by.param',
        'uses' => 'TicketApiController@getTicketByParam',
    ]);

    // Summary of results
    Route::post('summaryOfResults', [
        'as' => 'summaryOfResults',
        'uses' => 'TicketApiController@summaryOfResults',
    ]);

    // Summary of results
    Route::post('ticketHandle', [
        'as' => 'ticketHandle',
        'uses' => 'TicketHandleApiController@ticketHandle',
    ]);

    // Point
    Route::post('listPoint', [
        'as' => 'listPoint',
        'uses' => 'PointApiController@listPoint',
    ]);

});