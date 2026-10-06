<?php

namespace App\Custom;

use App\Transaction;
use Illuminate\Database\Eloquent\Model;

class CustomeTransactionUtil extends Model
{
    //

    public function getPurchaseSupplier($business_id, $transaction_id)
    {
        $supplier = Transaction::join('contacts as ct','transactions.contact_id','=','ct.id')                            
                                ->where('transactions.business_id', $business_id)
                                ->where('transactions.id', $transaction_id)
                                ->where('transactions.type', 'purchase')
                                ->select('ct.supplier_business_name','ct.name','ct.contact_id','transactions.ref_no','transactions.transaction_date')
                                ->first();
        return $supplier;
    }
}
