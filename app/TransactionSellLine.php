<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class TransactionSellLine extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    
    public function transaction()
    {
        return $this->belongsTo(\App\Transaction::class);
    }

    public function product() 
    {
        return $this->belongsTo(\App\Product::class, 'product_id');
    }

    public function variations()
    {
        return $this->belongsTo(\App\Variation::class, 'variation_id');
    }

    public function modifiers()
    {
        return $this->hasMany(\App\TransactionSellLine::class, 'parent_sell_line_id')
            ->where('children_type', 'modifier');
    }

    /**
     * Get the quantity column.
     *
     * @param  string  $value
     * @return float $value
     */
    public function getQuantityAttribute($value)
    {
        return (float)$value;
    }

    public function lot_details()
    {
        return $this->belongsTo(\App\PurchaseLine::class, 'lot_no_line_id');
    }

    public function get_discount_amount()
    {
        $discount_amount = 0;
        if (!empty($this->line_discount_type) && !empty($this->line_discount_amount)) {
            if ($this->line_discount_type == 'fixed') {
                $discount_amount = $this->line_discount_amount;
            } elseif ($this->line_discount_type == 'percentage') {
                $discount_amount = ($this->unit_price_before_discount * $this->line_discount_amount) / 100;
            }
        }
        return $discount_amount;
    }

    // public function get_cost_val()
    // {
    //     $cost_val = 0.0;
    //     $tr_data =  TransactionSellLinesPurchaseLines::join('purchase_lines as PL','PL.id','=','transaction_sell_lines_purchase_lines.purchase_line_id')                                                    
    //                                                  ->where('transaction_sell_lines_purchase_lines.sell_line_id', $this->id)                                                
    //                                                  ->select('transaction_sell_lines_purchase_lines.id','transaction_sell_lines_purchase_lines.sell_line_id','PL.id',
    //                                                             'transaction_sell_lines_purchase_lines.quantity','transaction_sell_lines_purchase_lines.qty_returned','PL.purchase_price_inc_tax')
    //                                                  ->get();

    //     // $tr_data  =  $this->join('transaction_sell_lines_purchase_lines as TSPL','TSPL.sell_line_id','=','transaction_sell_lines.id') 
    //     //                   ->join('purchase_lines as PL','PL.id','=','transaction_sell_lines_purchase_lines.purchase_line_id')                                                      
    //     //                                             //  ->where('transaction_sell_lines_purchase_lines.sell_line_id', $this->id)                                                
    //     //                                              ->select('TSPL.id','TSPL.sell_line_id','PL.id','transaction_sell_lines.id as tsl_id',
    //     //                                                         'TSPL.quantity','TSPL.qty_returned','PL.purchase_price_inc_tax')
    //     //                                              ->get();

    //                                                   // DB::raw('(SELECT SUM((TSPL.quantity - TSPL.qty_returned) * PL.purchase_price_inc_tax) FROM transaction_sell_lines_purchase_lines as TSPL 
    //                     //          INNER JOIN purchase_lines as PL ON PL.id = TSPL.purchase_line_id WHERE TSPL.sell_line_id = (transaction_sell_lines.id)) as total_cost_as_val')
                
    //     // Log::emergency($tr_data);
    //         foreach($tr_data as $data){
    //             $cost_val = $cost_val + ($data->quantity - $data->qty_returned) * $data->purchase_price_inc_tax;
    //         }
        
    //     return $cost_val;
    //     // DB::raw('(SELECT SUM((TSPL.quantity - TSPL.qty_returned)) FROM transaction_sell_lines_purchase_lines AS TSPL 
    //     //                 WHERE TSPL.purchase_line_id = transaction_sell_lines.id) as total_cost_val');
    // }

    /**
     * Get the unit associated with the purchase line.
     */
    public function sub_unit()
    {
        return $this->belongsTo(\App\Unit::class, 'sub_unit_id');
    }

    public function order_statuses()
    {
        $statuses = [
            'received',
            'cooked',
            'served'
        ];
    }

    public function service_staff()
    {
        return $this->belongsTo(\App\User::class, 'res_service_staff_id');
    }

    /**
     * The warranties that belong to the sell lines.
     */
    public function warranties()
    {
        return $this->belongsToMany('App\Warranty', 'sell_line_warranties', 'sell_line_id', 'warranty_id');
    }

    public function line_tax()
    {
        return $this->belongsTo(\App\TaxRate::class, 'tax_id');
    }
}
