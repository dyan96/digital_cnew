<?php

namespace App\Http\Controllers\Custom;

use App\BusinessLocation; 
use App\Custom\Settlement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AccountController;

class SettlemetController extends AccountController
{
    //

    

    public function dailyStmlIndex()
    {   
        
        //account.access
        
        // if ((!auth()->user()->can('account.access')) && (!auth()->user()->can('dashboard.data'))) {
        //     abort(403, 'Unauthorized action.');
        // }
        
        $business_id = session()->get('user.business_id');
        
        $business_locations = BusinessLocation::forDropdown($business_id, false);
                 
        return view('custom_reports.daily_settlement_report')
                    ->with(compact('business_locations'));
    }

    public function getSettlementData(Request $request)
    {   
        //return $request->all();
        // if ((!auth()->user()->can('account.access')) && (!auth()->user()->can('dashboard.data'))) {
        //     abort(403, 'Unauthorized action.');
        // }

            if($request->ajax()){

                $location_id = $request->location;
                $date = $request->date;

                $settlement = new Settlement();
                $settlement_data = $settlement->getSettlementByLocation($location_id, $date);


                return $settlement_data;
            }
        
           


    }
}

