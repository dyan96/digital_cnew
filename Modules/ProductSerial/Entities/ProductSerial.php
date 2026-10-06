<?php

namespace Modules\ProductSerial\Entities;

use App\Product;
use Illuminate\Database\Eloquent\Model;

class ProductSerial extends Model
{
    protected $fillable = ['location_id','product_id','issued_transaction_id','serial_no','status'];


    public static function checkSerial($product_id,$serial_no, $location=null)
    {
        if($location){
            $serial = Self::where('product_id',$product_id)->where('location_id',$location)->where('serial_no',$serial_no)->with('product')->first();
        }else{
            $serial = Self::where('product_id',$product_id)->where('serial_no',$serial_no)->with('product')->first();
        }

        return $serial;
    }

    public static function checkSerialWithoutProduct($serial_no, $location=null)
    {
        if($location){
            $serial = Self::where('location_id',$location)->where('serial_no',$serial_no)->with('product')->first();
        }else{
            $serial = Self::where('serial_no',$serial_no)->with('product')->first();
        }

        return $serial;
    }
    
    public static function restore($serials, $transaction_id=null)
    {   
        if(isset($transaction_id)){
            $serial = Self::where('serial_no',$serials)->where('issued_transaction_id',$transaction_id)->update(['status'=>0, 'issued_transaction_id'=>null]);   
        }else{
            $serial = Self::whereIn('serial_no',$serials)->update(['status'=>0, 'issued_transaction_id'=>null]);   
        }
         
        return $serial;
    }

    

    public static function issueSerial($transaction_id, $serials)
    {
        $serial = Self::whereIn('serial_no',$serials)->update(['status'=>1, 'issued_transaction_id'=>$transaction_id]); 
        return $serial;

    }

    public static function transferSerial($form_location_id, $to_location_id, $serialIds)
    {
        $serial = Self::whereIn('id',$serialIds)
                    ->where('location_id',$form_location_id)
                    ->where('status',0)
                    ->update(['location_id'=>$to_location_id]);
        return $serial;
    }

    /**
     * Get the product that owns the ProductSerial
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
