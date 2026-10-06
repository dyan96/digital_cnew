<?php

namespace App\Http\Controllers;

use Datatables;
use App\Contact;
use App\Category;
use App\BusinessLocation;
use App\TransactionSellLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomReportController extends Controller
{
    //
    public function costOfSales(Request $request)
    {
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        if ($request->ajax()) {
            
        }
        $categories = Category::forDropdown($business_id, 'product');
        $business_locations = BusinessLocation::forDropdown($business_id);
        //$customers = Contact::customersDropdown($business_id);

        return view('reports_custom.cost_of_sales') 
            ->with(compact('business_locations','categories'));
    }

    public function getCostOfSaleData(Request $request)
    {           
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $location_id = $request->get('location_id', null);

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date'); 
         
        $vld_str = '';
        $vld_str2 = '';
        if (!empty($location_id)) {
            $vld_str = "AND vld.location_id=$location_id";
            //$vld_str2 = "AND vld.location_id IN ($location_id,1)";

            $vld_str2 = "AND TRNS.location_id= $location_id";
        }

        if ($request->ajax()) {
            $variation_id = $request->get('variation_id', null);
            $query = TransactionSellLine::join(
                'transactions as t',
                'transaction_sell_lines.transaction_id',
                '=',
                't.id'
            )
                ->join(
                    'variations as v',
                    'transaction_sell_lines.variation_id',
                    '=',
                    'v.id'
                )
                ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('products as p', 'pv.product_id', '=', 'p.id')
                ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                
               

                ->where('t.business_id', $business_id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->select(                   
                    'transaction_sell_lines.id',
                    'p.name as product_name',
                    'p.enable_stock',
                    'p.type as product_type',
                    'pv.name as product_variation',
                    'v.name as variation_name',
                    'v.sub_sku',
                    't.id as transaction_id',
                    't.transaction_date as transaction_date',
                    DB::raw('DATE_FORMAT(t.transaction_date, "%Y-%m-%d") as formated_date'),
                    DB::raw("(SELECT SUM(vld.qty_available) FROM variation_location_details as vld WHERE vld.variation_id=v.id $vld_str) as current_stock"), //current_stock
                    DB::raw("(SELECT SUM(vld.qty_available) FROM variation_location_details as vld WHERE vld.variation_id=v.id AND vld.location_id = 1) as current_stock_mc"),

                     //[$start_date, $end_date]

                    // DB::raw("(SELECT SUM(TSL.quantity - TSL.quantity_returned) FROM transactions AS TRNS
                    //             JOIN transaction_sell_lines AS TSL ON TRNS.id=TSL.transaction_id
                    //             WHERE TRNS.status='final' AND TRNS.type='sell' $vld_str2 
                    //             AND TSL.variation_id=v.id AND TRNS.transaction_date = $end_date ) as current_stock"),
//

                    DB::raw('SUM(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) as total_qty_sold'),
                    'u.short_name as unit',
                        DB::raw('SUM((transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax) as subtotal'),

                        DB::raw('SUM((SELECT SUM((TSPL.quantity - TSPL.qty_returned) * PL.purchase_price_inc_tax) FROM transaction_sell_lines_purchase_lines as TSPL 
                                 INNER JOIN purchase_lines as PL ON PL.id = TSPL.purchase_line_id WHERE TSPL.sell_line_id = transaction_sell_lines.id)) as total_cost_as_val')
                        
                )
                 ->groupBy('v.id');
                // ->groupBy('formated_date');
                    

                

            if (!empty($variation_id)) {
                $query->where('transaction_sell_lines.variation_id', $variation_id);
            }
           
            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
            }

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            if (!empty($location_id)) {
                $query->where('t.location_id', $location_id);
            }

            $customer_id = $request->get('customer_id', null);
            if (!empty($customer_id)) {
                $query->where('t.contact_id', $customer_id);
            }

            $category_id = request()->get('category_id', null);
            if (!empty($category_id)) {
                $query->where('p.category_id', $category_id);
            }
         //   $dataset =$query->get();
          //  Log::emergency($dataset);

           // return null;

            return Datatables::of($query)
                ->editColumn('product_name', function ($row) {
                    $product_name = $row->product_name;
                    if ($row->product_type == 'variable') {
                        $product_name .= ' - ' . $row->product_variation . ' - ' . $row->variation_name;
                    }

                    return $product_name;
                })
                ->addColumn('current_stock_all',function ($row) use ($location_id) {
                    if ($row->enable_stock) {
                        if (!empty($location_id)) {
                            return  '<span data-is_quantity="true" class="display_currency current_stock_all" data-currency_symbol=false data-orig-value="' . ((float)($row->current_stock_mc+$row->current_stock)) . '" data-unit="' . $row->unit . '" >' . ((float)($row->current_stock_mc+$row->current_stock)) . '</span> ' .$row->unit;
                        }else{
                            return  '<span data-is_quantity="true" class="display_currency current_stock_all" data-currency_symbol=false data-orig-value="' . ((float)$row->current_stock) . '" data-unit="' . $row->unit . '" >' . ((float) $row->current_stock) . '</span> ' .$row->unit;
                        }
                     }else{
                        return '';
                     }
                })
                ->editColumn('total_qty_sold', function ($row) {
                    return '<span data-is_quantity="true" class="display_currency sell_qty" data-currency_symbol=false data-orig-value="' . (float)$row->total_qty_sold . '" data-unit="' . $row->unit . '" >' . (float) $row->total_qty_sold . '</span> ' .$row->unit;
                })
                ->editColumn('current_stock', function ($row) {
                    if ($row->enable_stock) {
                        return '<span data-is_quantity="true" class="display_currency current_stock" data-currency_symbol=false data-orig-value="' . (float)$row->current_stock . '" data-unit="' . $row->unit . '" >' . (float) $row->current_stock . '</span> ' .$row->unit;
                    } else {
                        return '';
                    }
                })
                 ->editColumn('subtotal', function ($row) {
                     return '<span class="display_currency row_subtotal" data-currency_symbol = true data-orig-value="' . $row->subtotal . '">' . number_format($row->subtotal,2,'.',',') . '</span>';
                 })

                 ->editColumn('total_cost_as_val', function ($row) {
                    // total_cost_as_val
                    return '<span class="display_currency row_total_cost_val" data-currency_symbol = true data-orig-value="' . $row->total_cost_as_val . '">' . number_format($row->total_cost_as_val,2,'.',',') . '</span>';
                    //return '<span class="display_currency row_total_cost_val" data-currency_symbol = true data-orig-value="' . $row->get_cost_val() . '">' . $row->get_cost_val() . '</span>';

                    //($row->get_cost_val($row->id))
                })
                ->addColumn('gross_profit', function ($row) {
                    return '<span class="display_currency row_gross_profit float-" data-currency_symbol = true data-orig-value="' .  ($row->subtotal-$row->total_cost_as_val) . '">' . number_format($row->subtotal-$row->total_cost_as_val,2,'.',',') . '</span>';
                    //($row->get_cost_val($row->id))
                })
                ->addColumn('gross_profit_perc', function ($row) {
                    if($row->subtotal>0){
                        
                       return '<span class="row_gross_profit_perc float-" data-orig-value="' .  (($row->subtotal - $row->total_cost_as_val)/$row->subtotal * 100.0) . '">' . number_format(((($row->subtotal - $row->total_cost_as_val)/$row->subtotal) * 100.0),2,'.',',') . '%</span>';
                    
                    }else{
                        return '<span class="row_gross_profit_perc float-" data-orig-value="' .  0.00 . '">' . 0.00 . '%</span>';
                    }
                   
                    //($row->get_cost_val($row->id)) 
                })
                
                ->rawColumns(['current_stock', 'subtotal', 'total_qty_sold','total_cost_as_val','current_stock_all','gross_profit','gross_profit_perc'])
                ->make(true);
        }
    }
    
    
public function stockReportAtDate(Request $request)
{
    if (!auth()->user()->can('stock_report.view')) {
        abort(403, 'Unauthorized action.');
    }

    $business_id = $request->session()->get('user.business_id');
    if ($request->ajax()) {
        
    }

    $categories = Category::forDropdown($business_id, 'product');
    $business_locations = BusinessLocation::forDropdown($business_id);
    //$customers = Contact::customersDropdown($business_id);

    return view('reports_custom.stock_details') 
        ->with(compact('business_locations','categories'));
}

public function getStockReportAtDateData(Request $request)
{   

        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $location_id = $request->get('location_id', null);
        $category_id = $request->get('category_id', null);

        $date_of_report = date("Y-m-d", strtotime($request->get('date_of_report')));
       
     
        if ($request->ajax()) {    
            
               // $permitted_locations = auth()->user()->permitted_locations();
                $location_id_str="";
                if (isset($location_id)) {                    
                    $location_id_str="AND transactions.location_id = '$location_id'";                    
                }


                $category_id_str="";
                if (isset($category_id)) {                    
                    $category_id_str="AND products.category_id = '$category_id'";                    
                }
            
                $stockDetails = DB::select("SELECT products.id,products.sku as sub_sku,products.name as product_name,transactions.location_id,business_locations.name as location_name,
                (SUM(IF(transactions.type in ('opening_stock'),purchase_lines.quantity,0))) AS opening_stock,
                (SUM(IF(transactions.type in ('purchase'),purchase_lines.quantity,0))) AS purchase,
                (SUM(IF(transactions.type = 'purchase_transfer',purchase_lines.quantity,0))) AS transfer_in,
                (SUM(IF(transactions.type = 'sell_transfer',transaction_sell_lines.quantity,0))) AS transfer_out,
                (SUM(IF(transactions.type = 'purchase_return' AND transactions.return_parent_id IS NULL ,purchase_lines.quantity_returned,0))) AS purchase_return_nref,
                (SUM(IF(transactions.type = 'purchase_return' AND transactions.return_parent_id IS NOT NULL ,purchase_lines.quantity_returned,0))) AS purchase_return_ref,
                (SUM(IF(transactions.type='sell',transaction_sell_lines.quantity,0))) as sold,
                (SUM(IF(transactions.type = 'sell_return' AND transactions.return_parent_id IS NOT NULL  ,transaction_sell_lines.quantity_returned,0))) AS sell_return,
                (SUM(IF(transactions.type = 'stock_adjustment' ,stock_adjustment_lines.quantity,0))) AS stock_adjustment
                FROM `transactions` 
                INNER JOIN business_locations ON transactions.location_id = business_locations.id 
                LEFT JOIN purchase_lines ON purchase_lines.transaction_id = IF(transactions.return_parent_id IS NOT NULL AND transactions.type='purchase_return',transactions.return_parent_id,transactions.id)
                LEFT JOIN transaction_sell_lines ON transaction_sell_lines.transaction_id = IF(transactions.return_parent_id IS NOT NULL AND transactions.type='sell_return',transactions.return_parent_id,transactions.id)
                LEFT JOIN stock_adjustment_lines ON stock_adjustment_lines.transaction_id = transactions.id
                INNER JOIN products ON products.id = IF(transactions.type IN ('purchase','opening_stock','purchase_transfer','purchase_return'),purchase_lines.product_id,  IF(transactions.type!='stock_adjustment',transaction_sell_lines.product_id,stock_adjustment_lines.product_id)) 
                WHERE transactions.status NOT IN ('draft','ordered') AND transactions.business_id = $business_id AND DATE(transactions.transaction_date) <= '$date_of_report'  $location_id_str  $category_id_str
                GROUP BY products.id,transactions.location_id");


            
            return Datatables::of($stockDetails)
                ->editColumn('product_name', function ($row) {
                    $product_name = $row->product_name;                   
                    return $product_name;
                })
                ->editColumn('opening_stock',function($row){
                    return number_format($row->opening_stock,4,'.',',');
                })
                ->editColumn('purchase',function($row){
                    return number_format($row->purchase,4,'.',',');
                })
                ->editColumn('transfer_in',function($row){
                    return number_format($row->transfer_in,4,'.',',');
                })                
                ->editColumn('transfer_out',function($row){
                    return number_format($row->transfer_out,4,'.',',');
                })
                ->addColumn('purchase_return', function ($row) {                                     
                    return number_format($row->purchase_return_nref +  $row->purchase_return_ref,4,'.',',');
                 })
                ->editColumn('sold',function($row){
                    return number_format($row->sold,4,'.',',');
                })
                ->editColumn('sell_return',function($row){
                    return number_format($row->sell_return,4,'.',',');
                })
                ->editColumn('stock_adjustment',function($row){
                    return number_format($row->stock_adjustment,4,'.',',');
                })
                ->addColumn('balance', function ($row) {                                     
                    $balance =  ($row->opening_stock+$row->purchase+$row->transfer_in+$row->sell_return) - ($row->transfer_out + $row->purchase_return_nref +  $row->purchase_return_ref + $row->sold+$row->stock_adjustment);
                    return number_format($balance,4,'.',',');
                 })
                
                ->rawColumns(['balance','purchase_return'])
                ->make(true);
        }
}

public function getStockBinCard(Request $request)
{
    if (!auth()->user()->can('stock_report.view')) {
        abort(403, 'Unauthorized action.');
    }

    $business_id = $request->session()->get('user.business_id');
    if ($request->ajax()) {
        
    }

    $categories = Category::forDropdown($business_id, 'product');
    $business_locations = BusinessLocation::forDropdown($business_id);
    //$customers = Contact::customersDropdown($business_id);

    return view('reports_custom.stock_bin_card') 
        ->with(compact('business_locations','categories'));
}

public function getStockBinCardData(Request $request)
{   

        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $location_id = $request->get('location_id', null);
        $category_id = $request->get('category_id', null);
        $product_id = $request->get('product_id');

        $start_date = date("Y-m-d", strtotime($request->get('start_date')));
        $end_date = date("Y-m-d", strtotime($request->get('end_date')));
       
        
     
        if ($request->ajax()) {    
            
               // $permitted_locations = auth()->user()->permitted_locations();
                $location_id_str="";
                if (isset($location_id)) {                    
                    $location_id_str="AND transactions.location_id = '$location_id'";                    
                }


                $category_id_str="";
                if (isset($category_id)) {                    
                    $category_id_str="AND products.category_id = '$category_id'";                    
                }

                $opening_balance = $this->getProductStockOpeningBalance($business_id,$product_id,$start_date,$location_id);
            
                $stockDetails = DB::select("SELECT transactions.id,business_locations.name as location_name,products.id,products.sku,products.name as product_name,transactions.type, 
                IF(transactions.type ='sell',transactions.invoice_no,transactions.ref_no) as ref_no,transactions.transaction_date,
               (CASE 
                WHEN transactions.type IN ('opening_stock','purchase','purchase_transfer') THEN purchase_lines.quantity
                WHEN transactions.type IN ('sell_transfer','sell') THEN transaction_sell_lines.quantity
                WHEN transactions.type = 'purchase_return' THEN purchase_lines.quantity_returned
                WHEN transactions.type IN ('sell_return','sell') THEN transaction_sell_lines.quantity_returned
                WHEN transactions.type ='stock_adjustment' THEN stock_adjustment_lines.quantity
                ELSE
                 0
                END) as quantity,
               (SUM(CASE 
                WHEN transactions.type IN ('opening_stock','purchase','purchase_transfer') THEN purchase_lines.quantity
                WHEN transactions.type IN ('sell_transfer','sell') THEN  -1 * transaction_sell_lines.quantity
                WHEN transactions.type = 'purchase_return' THEN -1 * purchase_lines.quantity_returned
                WHEN transactions.type IN ('sell_return') THEN  transaction_sell_lines.quantity_returned
                WHEN transactions.type ='stock_adjustment' THEN -1 * stock_adjustment_lines.quantity
                ELSE
                 0
                END) OVER (ORDER BY transactions.transaction_date ASC ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) + $opening_balance )  as balance
                FROM `transactions` 
                INNER JOIN business_locations ON transactions.location_id = business_locations.id 
                LEFT JOIN purchase_lines ON purchase_lines.transaction_id = IF(transactions.return_parent_id IS NOT NULL AND transactions.type='purchase_return',transactions.return_parent_id,transactions.id)
                LEFT JOIN transaction_sell_lines ON transaction_sell_lines.transaction_id = IF(transactions.return_parent_id IS NOT NULL AND transactions.type='sell_return',transactions.return_parent_id,transactions.id)
                LEFT JOIN stock_adjustment_lines ON stock_adjustment_lines.transaction_id = transactions.id
                INNER JOIN products ON products.id = IF(transactions.type IN ('purchase','opening_stock','purchase_transfer','purchase_return'),purchase_lines.product_id,  IF(transactions.type!='stock_adjustment',transaction_sell_lines.product_id,stock_adjustment_lines.product_id)) 
                WHERE transactions.status NOT IN ('draft','ordered') AND transactions.business_id = $business_id AND products.id = '$product_id' AND 
                 DATE(transactions.transaction_date) >= '$start_date' AND DATE(transactions.transaction_date) <= '$end_date' $location_id_str  $category_id_str
                ");

                         
            return Datatables::of($stockDetails)
                ->editColumn('product_name', function ($row) {
                    $product_name = $row->product_name;                   
                    return $product_name;
                })
                ->addColumn('in_qty', function ($row) {                                     
                    if(in_array($row->type,['opening_stock','purchase','purchase_transfer','sell_return'])){
                        return number_format($row->quantity,4,'.',',');
                    }else{
                        return '-';
                    }
                    
                 })
                 ->addColumn('out_qty', function ($row) {                                     
                    if(in_array($row->type,['opening_stock','purchase','purchase_transfer','sell_return'])){
                       return '-';
                    }else{
                        return number_format($row->quantity,4,'.',',');
                    }
                    
                 })
                ->editColumn('balance', function ($row) {                                                        
                    return number_format($row->balance,4,'.',',');
                 })
                
                ->rawColumns(['balance','in_qty','out_qty'])
                ->make(true);
        }
}

public function getProductStockOpeningBalance($business_id,$product_id, $date, $location_id=null)
{   
    
    $location_id_str="";
    if (isset($location_id)) {                    
        $location_id_str="AND transactions.location_id = '$location_id'";                    
    }

    if($product_id==null){
        return 0;
    }
   
    $stockDetails = DB::select("SELECT  SUM(CASE 
    WHEN transactions.type IN ('opening_stock','purchase','purchase_transfer') THEN purchase_lines.quantity
    WHEN transactions.type IN ('sell_transfer','sell') THEN  -1 * transaction_sell_lines.quantity
    WHEN transactions.type = 'purchase_return' THEN -1 * purchase_lines.quantity_returned
    WHEN transactions.type IN ('sell_return') THEN  transaction_sell_lines.quantity_returned
    WHEN transactions.type ='stock_adjustment' THEN -1 * stock_adjustment_lines.quantity
    ELSE
     0
    END) as balance   
    FROM `transactions` 
    INNER JOIN business_locations ON transactions.location_id = business_locations.id 
    LEFT JOIN purchase_lines ON purchase_lines.transaction_id = IF(transactions.return_parent_id IS NOT NULL AND transactions.type='purchase_return',transactions.return_parent_id,transactions.id)
    LEFT JOIN transaction_sell_lines ON transaction_sell_lines.transaction_id = IF(transactions.return_parent_id IS NOT NULL AND transactions.type='sell_return',transactions.return_parent_id,transactions.id)
    LEFT JOIN stock_adjustment_lines ON stock_adjustment_lines.transaction_id = transactions.id
    INNER JOIN products ON products.id = IF(transactions.type IN ('purchase','opening_stock','purchase_transfer','purchase_return'),purchase_lines.product_id,  IF(transactions.type!='stock_adjustment',transaction_sell_lines.product_id,stock_adjustment_lines.product_id)) 
    WHERE transactions.status NOT IN ('draft','ordered') AND transactions.business_id = $business_id AND products.id = '$product_id' AND DATE(transactions.transaction_date) < '$date'  $location_id_str");
    
    
    return isset($stockDetails[0]) ? $stockDetails[0]->balance : 0;
}

     /**
     * Shows report for Supplier
     *
     * @return \Illuminate\Http\Response
     */
    public function getCustomerSuppliersAgeAnaliysis(Request $request)
    {
        if (!auth()->user()->can('contacts_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        
        $at_date_ft = date('Y-m-d',strtotime($request->input('at_date')));
        // \Log::emergency($at_date_ft);
        $at_date =$at_date_ft;
        
        $date_at_30 = date('Y-m-d', strtotime($at_date. ' - 30 days'));
        $date_at_60 = date('Y-m-d', strtotime($at_date. ' - 60 days'));
        $date_at_90 = date('Y-m-d', strtotime($at_date. ' - 90 days'));
        $date_at_120 = date('Y-m-d', strtotime($at_date. ' - 120 days'));

    //    \Log::emergency(
    //     $at_date.
    //     $date_at_30.
    //     $date_at_60 .
    //     $date_at_90.
    //     $date_at_120
    //    );
        
        //Return the details in ajax call
        if ($request->ajax()) {
            $contacts = Contact::where('contacts.business_id', $business_id)
                ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                ->active()
                
                ->groupBy('contacts.id');
                if ($request->input('contact_type')=='supplier') {
                    $contacts->havingRaw("(SUM(IF(t.type = 'purchase' AND t.contact_id = contacts.id , final_total, 0)) 
                                           - (COALESCE(SUM(
                                               IF(t.type = 'purchase' AND t.contact_id = contacts.id, (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)
                                                ),0))  ) > 0");

                    $contacts->select(
                        DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total"),
                        DB::raw("SUM(IF(t.type = 'purchase' AND date(t.transaction_date) >= '$date_at_30' AND date(t.transaction_date) <= '$at_date' , final_total, 0)) as total_current"),
                        DB::raw("SUM(IF(t.type = 'purchase' AND date(t.transaction_date) >= '$date_at_60' AND date(t.transaction_date) < '$date_at_30' , final_total, 0)) as total_30_60"),
                        DB::raw("SUM(IF(t.type = 'purchase' AND date(t.transaction_date) >= '$date_at_90' AND date(t.transaction_date) < '$date_at_60' , final_total, 0)) as total_61_90"),
                        DB::raw("SUM(IF(t.type = 'purchase' AND date(t.transaction_date) >= '$date_at_120' AND date(t.transaction_date) < '$date_at_90' , final_total, 0)) as total_91_120"),
                        DB::raw("SUM(IF(t.type = 'purchase' AND date(t.transaction_date) < '$date_at_120' , final_total, 0)) as total_120_more"),
                        

                        DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as paid_total"),

                        DB::raw("SUM(IF(t.type = 'purchase' AND date(t.transaction_date) >= '$date_at_30' AND date(t.transaction_date) <= '$at_date', (SELECT SUM(amount) FROM transaction_payments 
                                        WHERE transaction_payments.transaction_id=t.id), 0)) as paid_current"),

                        DB::raw("SUM(IF(t.type = 'purchase' AND date(t.transaction_date) >= '$date_at_60' AND date(t.transaction_date) < '$date_at_30', (SELECT SUM(amount) FROM transaction_payments 
                                        WHERE transaction_payments.transaction_id=t.id), 0)) as paid_30_60"),

                        DB::raw("SUM(IF(t.type = 'purchase' AND date(t.transaction_date) >= '$date_at_90' AND date(t.transaction_date) < '$date_at_60', (SELECT SUM(amount) FROM transaction_payments 
                                        WHERE transaction_payments.transaction_id=t.id), 0)) as paid_61_90"),

                        DB::raw("SUM(IF(t.type = 'purchase' AND date(t.transaction_date) >= '$date_at_120' AND date(t.transaction_date) < '$date_at_90', (SELECT SUM(amount) FROM transaction_payments 
                                        WHERE transaction_payments.transaction_id=t.id), 0)) as paid_91_120"),

                        DB::raw("SUM(IF(t.type = 'purchase' AND date(t.transaction_date) < '$date_at_120', (SELECT SUM(amount) FROM transaction_payments 
                                        WHERE transaction_payments.transaction_id=t.id), 0)) as paid_120_more"),

                        'contacts.supplier_business_name',
                        'contacts.name',
                        'contacts.id',
                        'contacts.contact_id',
                        'contacts.type as contact_type'
                    );
                }else{                   
                    $contacts->havingRaw("(SUM(IF(t.type = 'sell' AND t.status ='final' AND t.contact_id = contacts.id, final_total, 0)) 
                                            - (COALESCE(SUM(IF(t.type = 'sell' AND t.status ='final' AND t.contact_id = contacts.id, (SELECT SUM(IF(is_return = 1, -1*amount,amount)) 
                                                    FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)),0)) ) > 0 ");
                    $contacts->select(                        
                        DB::raw("SUM(IF(t.type = 'sell' AND t.status ='final', final_total, 0)) as total"),
                        DB::raw("SUM(IF(t.type = 'sell' AND t.status ='final' AND date(t.transaction_date) >= '$date_at_30' AND date(t.transaction_date) <= '$at_date' , final_total, 0)) as total_current"),
                        DB::raw("SUM(IF(t.type = 'sell' AND t.status ='final' AND date(t.transaction_date) >= '$date_at_60' AND date(t.transaction_date) < '$date_at_30' , final_total, 0)) as total_30_60"),
                        DB::raw("SUM(IF(t.type = 'sell' AND t.status ='final' AND date(t.transaction_date) >= '$date_at_90' AND date(t.transaction_date) < '$date_at_60' , final_total, 0)) as total_61_90"),
                        DB::raw("SUM(IF(t.type = 'sell' AND t.status ='final' AND date(t.transaction_date) >= '$date_at_120' AND date(t.transaction_date) < '$date_at_90' , final_total, 0)) as total_91_120"),
                        DB::raw("SUM(IF(t.type = 'sell' AND t.status ='final' AND date(t.transaction_date) < '$date_at_120' , final_total, 0)) as total_120_more"),
                        

                        DB::raw("SUM(IF(t.type = 'sell' AND t.status ='final', (SELECT SUM(IF(is_return = 1, -1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as paid_total"),

                        DB::raw("SUM(IF(t.type = 'sell' AND t.status ='final' AND date(t.transaction_date) >= '$date_at_30' AND date(t.transaction_date) <= '$at_date', (SELECT SUM(IF(is_return = 1, -1*amount,amount)) FROM transaction_payments 
                                        WHERE transaction_payments.transaction_id=t.id), 0)) as paid_current"),

                        DB::raw("SUM(IF(t.type = 'sell' AND t.status ='final' AND date(t.transaction_date) >= '$date_at_60' AND date(t.transaction_date) < '$date_at_30', (SELECT SUM(IF(is_return = 1, -1*amount,amount)) FROM transaction_payments 
                                        WHERE transaction_payments.transaction_id=t.id), 0)) as paid_30_60"),

                        DB::raw("SUM(IF(t.type = 'sell' AND t.status ='final' AND date(t.transaction_date) >= '$date_at_90' AND date(t.transaction_date) < '$date_at_60', (SELECT SUM(IF(is_return = 1, -1*amount,amount)) FROM transaction_payments 
                                        WHERE transaction_payments.transaction_id=t.id), 0)) as paid_61_90"),

                        DB::raw("SUM(IF(t.type = 'sell' AND t.status ='final' AND date(t.transaction_date) >= '$date_at_120' AND date(t.transaction_date) < '$date_at_90', (SELECT SUM(IF(is_return = 1, -1*amount,amount)) FROM transaction_payments 
                                        WHERE transaction_payments.transaction_id=t.id), 0)) as paid_91_120"),

                        DB::raw("SUM(IF(t.type = 'sell' AND t.status ='final' AND date(t.transaction_date) < '$date_at_120', (SELECT SUM(IF(is_return = 1, -1*amount,amount)) FROM transaction_payments 
                                        WHERE transaction_payments.transaction_id=t.id), 0)) as paid_120_more"),

                        'contacts.supplier_business_name',
                        'contacts.name',
                        'contacts.id',
                        'contacts.contact_id',
                        'contacts.type as contact_type'                    
                    );
                }
               
            $permitted_locations = auth()->user()->permitted_locations();
            
            if ($permitted_locations != 'all') {
                $contacts->whereIn('t.location_id', $permitted_locations);
            }

            if (!empty($request->input('customer_group_id'))) {
                $contacts->where('contacts.customer_group_id', $request->input('customer_group_id'));
            }

            if (!empty($request->input('contact_type'))) {
                $contacts->whereIn('contacts.type', [$request->input('contact_type'), 'both']);
            }

            return Datatables::of($contacts)
                ->editColumn('name', function ($row) {
                    $name = $row->name;
                    if (!empty($row->supplier_business_name)) {
                        $name .= ', ' . $row->supplier_business_name;
                    }
                    return '<a href="' . action('ContactController@show', [$row->id]) . '" target="_blank" class="no-print">' .
                            $name .
                        '</a><span class="print_section">' . $name . '</span>';
                })
                ->addColumn('total_current_due', function ($row) {
                    $due = $row->total_current - $row->paid_current; 

                    return '<span class="total_current_due" 
                            data-orig-value="' . $due . '" data-currency_symbol = true>' 
                            . number_format($due,2,'.',',') . '</span>';
                })    
                ->addColumn('total_30_60_due', function ($row) {
                    $due = $row->total_30_60 - $row->paid_30_60; 

                    return '<span class="total_30_60_due" 
                            data-orig-value="' . $due . '" data-currency_symbol = true>' 
                            . number_format($due,2,'.',',') . '</span>';
                })   
                ->addColumn('total_61_90_due', function ($row) {
                    $due = $row->total_61_90 - $row->paid_61_90; 

                    return '<span class="total_61_90_due" 
                            data-orig-value="' . $due . '" data-currency_symbol = true>' 
                            . number_format($due,2,'.',',') . '</span>';
                })                           
                ->addColumn('total_91_120_due', function ($row) {
                    $due = $row->total_91_120 - $row->paid_91_120; 

                    return '<span class="total_91_120_due" 
                            data-orig-value="' . $due . '" data-currency_symbol = true>' 
                            . number_format($due,2,'.',',') . '</span>';
                })                           
                ->addColumn('total_120_more_due', function ($row) {
                    $due = $row->total_120_more - $row->paid_120_more; 

                    return '<span class="total_120_more_due" 
                            data-orig-value="' . $due . '" data-currency_symbol = true>' 
                            . number_format($due,2,'.',',') . '</span>';
                })                                          
                ->addColumn('total_due', function ($row) {
                    $due = $row->total - $row->paid_total; 

                    return '<span class="total_due" 
                            data-orig-value="' . $due . '" data-currency_symbol = true>' 
                            . number_format($due,2,'.',',') . '</span>';
                }) 
                ->removeColumn('supplier_business_name')                
                ->rawColumns(['name','total_current_due','total_30_60_due','total_61_90_due','total_91_120_due','total_120_more_due','total_due'])
                ->make(true);
        }

        $customer_group = CustomerGroup::forDropdown($business_id, false, true);
        $types = [            
            'customer' => __('report.customer'),
            'supplier' => __('report.supplier')
        ];

        return view('reports_custom.age_analiysis')
        ->with(compact('customer_group', 'types'));
    }
    
}
