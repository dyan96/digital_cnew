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
Route::group(['middleware' => ['web', 'authh', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu'], 'prefix' => 'productserial'], function () {

    Route::GET('/', 'ProductSerialController@index');
    // Route::GET('/serials', 'ProductSerialController@serials');
    Route::GET('/add', 'ProductSerialController@addNew');
    Route::GET('/transfers', 'ProductSerialController@transfer');
    Route::POST('/transfer-store', 'ProductSerialController@transferStore');
    Route::POST('/check', 'ProductSerialController@checkSerialIsAvailable');
    Route::POST('/store', 'ProductSerialController@store');
    Route::GET('/getdata', 'ProductSerialController@getData');
    Route::PUT('/restore', 'ProductSerialController@restoreSerial');
    Route::DELETE('/destroy', 'ProductSerialController@destroy');
    

    //install
    Route::get('/install', 'InstallController@index');
    Route::post('/install', 'InstallController@install');
    Route::get('/install/uninstall', 'InstallController@uninstall');
    Route::get('/install/update', 'InstallController@update');
});
 
 
 
