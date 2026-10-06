<?php
Route::get('/repair-status', 'Modules\Repair\Http\Controllers\CustomerRepairStatusController@index')->name('repair-status');
Route::post('/post-repair-status', 'Modules\Repair\Http\Controllers\CustomerRepairStatusController@postRepairStatus')->name('post-repair-status');
Route::group(['middleware' => ['web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu'], 'prefix' => 'repair', 'namespace' => 'Modules\Repair\Http\Controllers'], function () {
    Route::get('edit-repair/{id}/status', 'RepairController@editRepairStatus');
    Route::post('update-repair-status', 'RepairController@updateRepairStatus');
    Route::get('delete-media/{id}', 'RepairController@deleteMedia');
    Route::get('print-label/{id}', 'RepairController@printLabel');
    Route::resource('/repair', 'RepairController')->except(['create', 'edit','index','getdata']);
    Route::resource('/status', 'RepairStatusController', ['except' => ['show']]);
    
    Route::get('/repair','RepairController@index');
    Route::post('/repair-get-data','RepairController@getdata');
    
    Route::resource('/repair-settings', 'RepairSettingsController', ['only' => ['index', 'store']]);

    Route::get('/install', 'InstallController@index');
    Route::post('/install', 'InstallController@install');
    Route::get('/install/uninstall', 'InstallController@uninstall');
    Route::get('/install/update', 'InstallController@update');

    Route::get('get-device-models', 'DeviceModelController@getDeviceModels');
    Route::get('models-repair-checklist', 'DeviceModelController@getRepairChecklists');
    Route::resource('device-models', 'DeviceModelController')->except(['show']);
    Route::resource('dashboard', 'DashboardController');
    
    Route::get('job-sheet/print/{id}', 'JobSheetController@print');
    Route::get('job-sheet/delete/{id}/image', 'JobSheetController@deleteJobSheetImage');
    Route::get('job-sheet/{id}/status', 'JobSheetController@editStatus');
    Route::put('job-sheet-update/{id}/status', 'JobSheetController@updateStatus');
    Route::get('job-sheet/add-parts/{id}', 'JobSheetController@addParts');
    Route::post('job-sheet/save-parts/{id}', 'JobSheetController@saveParts');
    Route::post('job-sheet/get-part-row', 'JobSheetController@jobsheetPartRow');
    Route::resource('job-sheet', 'JobSheetController');
    Route::post('job-sheet/get-data', 'JobSheetController@getData');
});
