<?php

Route::middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin'])->group(function () {


    Route::get('/reports/cost-of-sales','CustomReportController@costOfSales');
    Route::get('/reports/cost-of-sales/get-data','CustomReportController@getCostOfSaleData');
    
    //Custom Reports Routes
Route::get('/custom-reports/stock-report', 'Custom\CustomRportController@getStockReport');

Route::get('/custom-reports/daily-settlement', 'Custom\SettlemetController@dailyStmlIndex');
Route::post('/custom-reports/daily-settlement', 'Custom\SettlemetController@getSettlementData');

//SalesReport
Route::get('/custom-reports/sales-report/invoiceby', 'Custom\SaleReportControler@inv_by_report');
Route::get('/custom-reports/sales-report/get/data', 'Custom\SaleReportControler@getDataInv_by_report');

 //Stock Report
    Route::get('/reports/stock-detail-report','CustomReportController@stockReportAtDate');
    Route::get('/reports/stock-detail-report/get-data','CustomReportController@getStockReportAtDateData');
    
    Route::get('/reports/stock-bin-card','CustomReportController@getStockBinCard');
    Route::get('/reports/stock-bin-card/get-data','CustomReportController@getStockBinCardData');
    

//Management Dashbord
Route::get('/dashboard/management', 'Custom\DashboardController@mngd_index');
Route::get('/dashboard/management/get-data', 'Custom\DashboardController@getTotals');
Route::get('/dashboard/daily-settlement/{location_id}', 'Custom\DashboardController@settmentIndex');


});