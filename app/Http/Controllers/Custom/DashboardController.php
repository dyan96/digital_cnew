<?php

namespace App\Http\Controllers\Custom;

use App\Currency;
use App\BusinessLocation;
use App\Utils\ModuleUtil;
use App\Utils\BusinessUtil;
use Illuminate\Http\Request;
use App\Utils\TransactionUtil;
use App\Http\Controllers\Controller;
use ConsoleTVs\Charts\Facades\Charts;

class DashboardController extends Controller
{


    protected $businessUtil;
    protected $transactionUtil;
    protected $moduleUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil
    ) {
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
    }
    
    //
    public function mngd_index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('dashboard.data')) {
            return view('home.index');
        }

        $fy = $this->businessUtil->getCurrentFinancialYear($business_id);
        $date_filters['this_fy'] = $fy;
        $date_filters['this_month']['start'] = date('Y-m-01');
        $date_filters['this_month']['end'] = date('Y-m-t');
        $date_filters['this_week']['start'] = date('Y-m-d', strtotime('monday this week'));
        $date_filters['this_week']['end'] = date('Y-m-d', strtotime('sunday this week'));

        $currency = Currency::where('id', request()->session()->get('business.currency_id'))->first();
        
        //Chart for sells last 30 days
        $sells_last_30_days = $this->transactionUtil->getSellsLast30Days($business_id);
        $labels = [];
        $all_sell_values = [];
        $dates = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = \Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = $date;

            $labels[] = date('j M Y', strtotime($date));

            if (!empty($sells_last_30_days[$date])) {
                $all_sell_values[] = $sells_last_30_days[$date];
            } else {
                $all_sell_values[] = 0;
            }
        }

        //Get sell for indivisual locations
        $all_locations = BusinessLocation::forDropdown($business_id);
        $location_sells = [];
        $sells_by_location = $this->transactionUtil->getSellsLast30Days($business_id, true);
        foreach ($all_locations as $loc_id => $loc_name) {
            $values = [];
            foreach ($dates as $date) {
                $sell = $sells_by_location->first(function ($item) use ($loc_id, $date) {
                    return $item->date == $date &&
                        $item->location_id == $loc_id;
                });
                
                if (!empty($sell)) {
                    $values[] = $sell->total_sells;
                } else {
                    $values[] = 0;
                }
            }
            $location_sells[$loc_id]['loc_label'] = $loc_name;
            $location_sells[$loc_id]['values'] = $values;
        }

        $sells_chart_1 = Charts::multi('line', 'highcharts')
                            ->title(' ')
                            ->template('material')
                            ->labels($labels)
                            ->elementLabel(__('home.total_sells', ['currency' => $currency->code]));

        if (!empty($location_sells)) {
            foreach ($location_sells as $location_sell) {
                $sells_chart_1->dataset($location_sell['loc_label'], $location_sell['values']);
            }
        }

        if (count($all_locations) > 1) {
            $sells_chart_1->dataset(__('report.all_locations'), $all_sell_values);
        }
        
       // return $all_locations;

        return view('custom_dashboard.mng_dashboard', compact('date_filters', 'sells_chart_1','all_locations'));
    }


    public function getTotals()
    {
        if (request()->ajax()) {
            $start = request()->start;
            $end = request()->end;
            $business_id = request()->session()->get('user.business_id');

            $purchase_details = $this->transactionUtil->getPurchaseTotals($business_id, $start, $end);

            $sell_details = $this->transactionUtil->getSellTotals($business_id, $start, $end);

            $transaction_types = [
                'purchase_return', 'stock_adjustment', 'sell_return'
            ];

            $transaction_totals = $this->transactionUtil->getTransactionTotals(
                $business_id,
                $transaction_types,
                $start,
                $end
            );

            $total_purchase_inc_tax = !empty($purchase_details['total_purchase_inc_tax']) ? $purchase_details['total_purchase_inc_tax'] : 0;
            $total_purchase_return_inc_tax = $transaction_totals['total_purchase_return_inc_tax'];
            $total_adjustment = $transaction_totals['total_adjustment'];

            $total_purchase = $total_purchase_inc_tax - $total_purchase_return_inc_tax - $total_adjustment;
            $output = $purchase_details;
            $output['total_purchase'] = $total_purchase;

            $total_sell_inc_tax = !empty($sell_details['total_sell_inc_tax']) ? $sell_details['total_sell_inc_tax'] : 0;
            $total_sell_return_inc_tax = !empty($transaction_totals['total_sell_return_inc_tax']) ? $transaction_totals['total_sell_return_inc_tax'] : 0;

            $output['total_sell'] = $total_sell_inc_tax - $total_sell_return_inc_tax;

            $output['invoice_due'] = $sell_details['invoice_due'];

            $all_locations = BusinessLocation::forDropdown($business_id);
            $branche_wise_total=[];

            $location_count=0;
            foreach($all_locations as $location_id => $location){
                $sale_data =  $this->transactionUtil->getSellTotals(
                    $business_id,                    
                    $start,
                    $end,
                    $location_id
                );

                $transaction_data = $this->transactionUtil->getTransactionTotals(
                    $business_id,
                    $transaction_types,
                    $start,
                    $end,
                    $location_id
                );


                $total_sell_inc_tax_bw = !empty($sale_data['total_sell_inc_tax']) ? $sale_data['total_sell_inc_tax'] : 0;
                $total_sell_return_inc_tax_bw = !empty($transaction_data['total_sell_return_inc_tax']) ? $transaction_data['total_sell_return_inc_tax'] : 0;
                $branche_wise_total[] = ['location_id'=>$location_id,'total_sell'=>$total_sell_inc_tax_bw - $total_sell_return_inc_tax_bw];
                $location_count++;
            }

            $output['branch_wise'] = $branche_wise_total;
            //$output['branch_count'] = $location_count;

            return $output;
        }
    }

    public function settmentIndex($location_id)
    {
        $business_id = session()->get('user.business_id');
        
        $business_locations = BusinessLocation::forDropdown($business_id, false);
                 
        return view('custom_reports.daily_settlement_report_mobile')
                    ->with(compact('business_locations','location_id'));
    }

}
