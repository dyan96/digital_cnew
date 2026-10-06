<?php

namespace App\Http\Controllers\Custom;

use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class StockDetailController extends Controller
{

    
    // public function __construct(TransactionUtil $transactionUtil, ProductUtil $productUtil, ModuleUtil $moduleUtil)
    // {
    //     //$this->transactionUtil = $transactionUtil;
    //     $this->productUtil = $productUtil;
    //     $this->moduleUtil = $moduleUtil;
    // }
    
    //
    public function find(Request $request)
    {   
        

        $barcode_id = $request->product_sku; 

        if(isset($barcode_id)){

        
        $business_id = $request->user()->business_id;
        
        $product_details = Product::leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                        ->join('units', 'products.unit_id', '=', 'units.id')
                        ->leftJoin('categories as c1', 'products.category_id', '=', 'c1.id')
                        ->leftJoin('categories as c2', 'products.sub_category_id', '=', 'c2.id')
                        ->leftJoin('tax_rates', 'products.tax', '=', 'tax_rates.id')
                        ->join('variations as v', 'v.product_id', '=', 'products.id')                          
                        ->leftJoin('variation_location_details as vld', 'vld.variation_id', '=', 'v.id')                      
                        ->where('products.business_id', $business_id)
                        ->where('products.type', '!=', 'modifier')
                        ->where('products.sku',$barcode_id)->select(                            
                            'products.id',
                            'products.name as product',
                            'products.type',
                            'c1.name as category',
                            'c2.name as sub_category',
                            'units.actual_name as unit',
                            'brands.name as brand',
                            'tax_rates.name as tax',
                            'products.sku',
                            'products.image',
                            'products.enable_stock',
                            'products.is_inactive',
                            'products.not_for_selling',
                            'products.alert_quantity',
                            'products.product_description',
                            // 'products.product_custom_field3',
                            // 'products.product_custom_field4',
                            DB::raw('SUM(vld.qty_available) as current_stock'),
                            DB::raw('MAX(v.sell_price_inc_tax) as max_price'),
                            DB::raw('MIN(v.sell_price_inc_tax) as min_price'),
                            DB::raw('MAX(v.dpp_inc_tax) as item_cost')
                            // DB::raw('MIN(v.dpp_inc_tax) as min_purchase_price')

                        )->first();

        if($product_details->product==null){
            return  response()->json(['message' => 'No matching product found!'], 200);            
        }

        $query = Product::leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->join('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('categories as c1', 'products.category_id', '=', 'c1.id')
                ->leftJoin('categories as c2', 'products.sub_category_id', '=', 'c2.id')
                ->leftJoin('tax_rates', 'products.tax', '=', 'tax_rates.id')
                ->join('variations as v', 'v.product_id', '=', 'products.id')
                ->leftJoin('variation_location_details as vld', 'vld.variation_id', '=', 'v.id')
                ->leftJoin('business_locations as bl','bl.id','=','vld.location_id')
                ->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier')
                ->where('products.sku',$barcode_id);

        // $query->with(['product_locations' => function($qry){
        //     $qry->join('variation_location_details as vld', 'vld.variation_id', '=', 'v.id');
        //     $qry->select('business_locations.id','business_locations.name');
        // }]);
        $query->select('bl.name','vld.qty_available');
        $product_availability =  $query->get();
        

        return json_encode(['product_details'=>$product_details, 'availability'=>$product_availability]);

        }else{
            return  response()->json(['message' => 'SKU is Null'], 200);
        }

    }
}
