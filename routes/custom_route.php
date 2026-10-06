<?php

//Custom Reports Routes
Route::get('/custom-reports/stock-report', 'Custom\CustomRportController@getStockReport');

Route::get('/custom-reports/daily-settlement', 'Custom\SettlemetController@dailyStmlIndex');
Route::post('/custom-reports/daily-settlement', 'Custom\SettlemetController@getSettlementData');

//SalesReport
Route::get('/custom-reports/sales-report/invoiceby', 'Custom\SaleReportControler@inv_by_report');
Route::get('/custom-reports/sales-report/get/data', 'Custom\SaleReportControler@getDataInv_by_report');


//Management Dashbord
Route::get('/dashboard/management', 'Custom\DashboardController@mngd_index');
Route::get('/dashboard/management/get-data', 'Custom\DashboardController@getTotals');
Route::get('/dashboard/daily-settlement/{location_id}', 'Custom\DashboardController@settmentIndex');


?>