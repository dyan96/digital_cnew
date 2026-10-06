<?php

namespace Modules\ProductSerial\Http\Controllers;

use Exception;
use App\BusinessLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\ProductSerial\Entities\ProductSerial;
use Yajra\DataTables\Facades\DataTables; 

class ProductSerialController extends Controller
{
     
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id, true);
        return view('productserial::serials.index',compact('business_locations'));
    }

   
    public function addNew()
    {   
        $business_id = request()->session()->get('user.business_id');  

        $locations = BusinessLocation::where('business_id',$business_id)->pluck('name','id');
        $locations = $locations->prepend('None', '');

        return view('productserial::serials.add_serial',compact('locations'));
    }

    public function transfer()
    {   
        $business_id = request()->session()->get('user.business_id');  

        $locations = BusinessLocation::where('business_id',$business_id)->pluck('name','id');
        $locations = $locations->prepend('None', '');

        return view('productserial::serials.transfer_serial',compact('locations'));
    }

    

    public function getData(Request $request)
    {
       // $business_id = $request->session()->get('user.business_id');
         
        // if($request->ajax()){

            //join('double_entry_account_types as AT','AT.id','=','double_entry_account_categories.account_type_id')            
        
            $productSerials =  ProductSerial::join('products as P','P.id','=','product_serials.product_id')
                                                ->join('business_locations as BL','BL.id','=','product_serials.location_id')
                                                ->leftJoin('transactions as TS','TS.id','=','product_serials.issued_transaction_id')
                                                ->select('product_serials.*','P.name as product_name','BL.name as location_name','TS.invoice_no','TS.transaction_date');
            
                if ($request->has('location_id')) {
               $location_id = request()->get('location_id');
               if (!empty($location_id)) {
                   $productSerials->where('product_serials.location_id', $location_id);
               }
             }
             
             if ($request->has('product_id')) {
                $product_id = request()->get('product_id');
                if (!empty($product_id)) {
                   $productSerials->where('product_serials.product_id', $product_id);
                }
              }

              if ($request->has('start_date') && $request->has('end_date')) {  
                $status = request()->get('status');
                
                if($status==1){
                    $productSerials->whereDate('TS.transaction_date', '>=',$request->get('start_date'))
                               ->whereDate('TS.transaction_date', '<=',$request->get('end_date'));
                }             
                
              }
             
              if ($request->has('status')) {
                $status = request()->get('status');
                if (!empty($status)) {
                    if($status=='all'){
                        $productSerials->whereIn('product_serials.status', [0,1]);
                    }else{
                        $productSerials->where('product_serials.status', $status);
                    }
                    
                }
              }

              

            return Datatables::of($productSerials)
                            ->addColumn('action', function($row){
                                $html = '<div class="btn-group">
                                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                                data-toggle="dropdown" aria-expanded="false">' .
                                                __("messages.actions") .
                                                '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                                </span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-left" role="menu">' ;
                                if($row->status==1){
                                    $html .= '<li><a class="" href="#" onclick="restoreSerial('.$row->id.','.$row->serial_no.')" class="" ><i class="fas fa-undo" aria-hidden="true"></i> ' . 'Restore Serial' . '</a></li>';
                                }else{
                                    $html .= '<li><a class="" href="#" onclick="deleteSerial('.$row->id.')"><i class="fas fa-trash" aria-hidden="true"></i> ' . 'Delete' . '</a></li>';
                                }                                
                                
                                 $html .= '</ul></div>';

                                 return $html;
                            }) 
                            ->editColumn('status', function ($row) {                                            
                                return $row->status == 0 ? '<span class="label bg-green">NOT ISSUED</span>' : '<span class="label bg-yellow">ISSUED</span>';;
                            }) 
                            ->addColumn('issued_trn', function ($row) {                                            
                                return $row->invoice_no;
                            }) 
                            // ->filterColumn('category_name', function ($query, $keyword) {
                            //     $query->whereRaw("CONCAT(double_entry_account_categories.category_code, ' - ', double_entry_account_categories.category_name, ' - ') like ?", ["%{$keyword}%"]);
                            // })                            
                            ->rawColumns(['action','issued_trn','status'])
                            ->make(true);

        // }
        //return null;
    }

    public function checkSerialIsAvailable(Request $request)
    {   
        //return $request->all();
        $product_id = $request->product_id;
        $serial_no = $request->serial_no;
        $location_id = isset($request->location) ? $request->location : null;
        
        if(isset($product_id)){
            if(isset($serial_no)){
                $productSerial = ProductSerial::checkSerial($product_id,$serial_no);
                if($request->type =='AD'){
                    if(isset($productSerial)==false){
                        return [
                            'status'=>'OK',
                            'data'=>'',
                            'html'=>'
                            <tr>
                                <td class="text-center">-</td>                        
                                <td ><input type="text" class="serial_no" value="'.$serial_no.'" style="width: 100%"  disabled> </th>
                                <td class="text-center">
                                    <button type="button" style="margin-left: 10px"  class="btn btn-danger btn-sm delete-btn">Remove</button>                     
                                </td>
                            </tr>
                            ',
                        ];
                    }else{
                        return [
                            'status'=>'Exist',
                            'data'=>$productSerial,
                            'html'=>''
                        ];
                    }
                }else if($request->type =='TRN'){
                    if(isset($productSerial)){
                        
                        
                        if($productSerial->status==0){
                            
                            if($productSerial->location_id!=$location_id){
                                return [
                                    'status'=>'IVL',
                                    'data'=>'',
                                    'html'=>''
                                ]; 
                            }

                            return [
                                'status'=>'OK',
                                'data'=>'',
                                'html'=>'
                                <tr>
                                    <td class="text-center">-</td>    
                                    <td class="text-center">
                                        <input type="hidden" class="serial_id" value="'.$productSerial->id.'">
                                        '.$productSerial->product->name.'
                                    </td>                                                            
                                    <td ><input type="text" class="serial_no" value="'.$serial_no.'" style="width: 100%"  disabled> </th>
                                    <td class="text-center">
                                        <button type="button" style="margin-left: 10px"  class="btn btn-danger btn-sm delete-btn">Remove</button>                     
                                    </td>
                                </tr>
                                ',
                            ];
                        }else{
                            return [
                                'status'=>'ISSUED',
                                'data'=>'',
                                'html'=>''
                            ];
                        }
                       

                    }else{
                        return [
                            'status'=>'NF',
                            'data'=>$productSerial,
                            'html'=>''
                        ];
                    }
                }
               

            }else{
                return [
                    'status'=>'error',
                    'data'=>'Serial No cannot be empty',
                    'html'=>'Serial No cannot be empty'
                ];
            }
        }else{
            return [
                'status'=>'error',
                'data'=>'Product cannot be empty',
                'html'=>'Product cannot be empty'
            ];
        }
    }
    
   
    public function store(Request $request)
    {
        $serialList = $request->serials;
       // return $serialList;
        try{

            DB::beginTransaction();

            if(count($serialList)>0){
            
                foreach($serialList as $serial){
                    ProductSerial::create($serial);
                }
            }

            DB::commit();

            return   'DONE';

        }catch(Exception $ex){
            \Log::error($ex);
            DB::rollback();
            return 'error';
        }
       
    }

    public function transferStore(Request $request)
    {
        $serialList = $request->serials;
        $from_location_id = $request->from_location_id;
        $to_location_id = $request->to_location_id;
        // return $request->all();
         try{
 
             DB::beginTransaction();
 
            
             
            ProductSerial::whereIn('id',$serialList)->update(['location_id'=>$to_location_id]);
            
 
             DB::commit();
 
             return 'DONE';
 
         }catch(Exception $ex){
             \Log::error($ex);
             DB::rollback();
             return 'error';
         }
    }

    public function restoreSerial(Request $request)
    {   

        $serial =  ProductSerial::find($request->id);

        if(isset($serial)){
            ProductSerial::restore([$serial->serial_no]);
        }

        return 'DONE';
          
    }

    public function destroy(Request $request)
    {   

        $serial =  ProductSerial::find($request->id);

        if(isset($serial)){
            $serial->delete();
            return 'DONE';
        }else{
            return 'ERROR';
        }

       
    }

 
}
