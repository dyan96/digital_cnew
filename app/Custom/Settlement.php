<?php

namespace App\Custom;

use DateTime;
use App\Account;
use Carbon\Carbon;
use App\Transaction;
use App\BusinessLocation;
use App\Custom\ConfigData;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    //



    public  function getSettlementByLocation($location_id, $date)
    {   
    

        $date = new DateTime($date);
        $date = $date->format('Y-m-d');

        $location_name ='';

        $locationData = BusinessLocation::find($location_id);
        $default_accounts = json_decode($locationData->default_payment_accounts,true);

        $cash_Account_id = $default_accounts['cash']['account'];
        $petty_cash_Account_id = ConfigData::getPetticashAccountByLocationId($location_id);
        
        $location_name = $locationData->name;

        //difination 
        $date_f =  date_create($date); 

        $Cash_Account_ob=[];
        $Cash_Account_balance=[];
        $Cash_Account_transfer = [];
        $Cash_Account_deposit = [];


        $Petty_cash_Account_ob=[];
        $Petty_cash_Account_balance=[];
        $Petty_cash_Account_transfer = [];
        $Petty_cash_Account_deposit = [];


        //CASH Account DATA        
        $Cash_Account_ob = $this->getAccountBalance($cash_Account_id, date_sub($date_f, date_interval_create_from_date_string('1 days')));
        $Cash_Account_balance = $this->getAccountBalance($cash_Account_id, $date);
        $Cash_Account_transfer = $this->getAccountTransfers($cash_Account_id,$date);
        $Cash_Account_deposit = $this->getAccountDeposit($cash_Account_id,$date);


        //Petty CASH Account DATA       
        $Petty_cash_Account_ob = $this->getAccountBalance($petty_cash_Account_id, date_sub($date_f, date_interval_create_from_date_string('1 days')));
        $Petty_cash_Account_balance = $this->getAccountBalance($petty_cash_Account_id,$date);
        $Petty_cash_Account_transfer = $this->getAccountTransfers($petty_cash_Account_id,$date);
        $Petty_cash_Account_deposit = $this->getAccountDeposit($petty_cash_Account_id,$date);


        //Sales Details
        $salesDetails = $this->getSalesDetails($location_id,$date);
        //Collection
        $collectionDetails = $this->getCollectionDetails($location_id,$date);
        //Expencess Details 
        $expenseDetails = $this->getExpencesDetails($location_id,$date, $cash_Account_id, $petty_cash_Account_id);
        $expenseOldPmtDetails = $this->getExpencessOldPaymentDetails($location_id,$date, $cash_Account_id, $petty_cash_Account_id);
        
        
         //Purchase Details 
         $purchaseDetails = $this->getPurchaseDetails($location_id,$date, $cash_Account_id, $petty_cash_Account_id);
         $purchaseOldPmtDetails = $this->getPurchaseOldPaymentDetails($location_id,$date, $cash_Account_id, $petty_cash_Account_id);
        
        return [
            'cash_account' => ['ob'=>$Cash_Account_ob, 'curr_bl' => $Cash_Account_balance, 'ac_trf'=> $Cash_Account_transfer, 'ac_dep' => $Cash_Account_deposit],
            'petty_cash_account' => [ 'ob'=> $Petty_cash_Account_ob, 'curr_bl' => $Petty_cash_Account_balance, 'ac_trf'=> $Petty_cash_Account_transfer, 'ac_dep' => $Petty_cash_Account_deposit],
            'sales' => $salesDetails,
            'collection' => $collectionDetails,
            'expense' =>$expenseDetails,
            'expense_old_pmt' =>$expenseOldPmtDetails,
            'purchase' =>$purchaseDetails,
            'purchase_old_pmt' =>$purchaseOldPmtDetails,
            'location_name'=>$location_name,
            'report_date'=> $date,

        ];
    }


    public  function getAccountBalance($id,$date)
    {
        // if (!auth()->user()->can('account.access')) {
        //     abort(403, 'Unauthorized action.');
        // }

        $business_id = session()->get('user.business_id');
        $account = Account::leftjoin(
            'account_transactions as AT',
            'AT.account_id',
            '=',
            'accounts.id'
        )
            ->whereNull('AT.deleted_at')
            ->where('accounts.business_id', $business_id)
            ->where('accounts.id', $id);

            if($date!=null){
                $account->select('accounts.*', DB::raw("SUM( IF(AT.type='credit', amount, -1 * amount) ) as balance"))
                ->where(DB::raw('date(AT.created_at)'),'<=', $date);
                    
            }else{
                $account->select('accounts.*', DB::raw("SUM( IF(AT.type='credit', amount, -1 * amount) ) as balance"));
            }
            

            $accountData = $account->first();

        return $accountData;
    }

    public  function getAccountTransfers($id, $date)
    {   
       

        // if (!auth()->user()->can('account.access')) {
        //     abort(403, 'Unauthorized action.');
        // }
        $transfer_amount_out =0;
        $transfer_amount_in=0;

        $business_id = session()->get('user.business_id');
        $account = Account::leftjoin(
            'account_transactions as AT',
            'AT.account_id',
            '=',
            'accounts.id'
        )
            ->whereNull('AT.deleted_at')
            ->where('accounts.business_id', $business_id)
            ->where('accounts.id', $id)
            ->where('sub_type','fund_transfer');

            if($date!=null){
                $account->where(DB::raw('date(AT.created_at)'),'=', $date);
            }
            
            $account->whereNull('AT.deleted_at');

            $account->select(DB::raw("SUM( IF(AT.type='credit', amount,0) ) as transfer_in_amount"),DB::raw("SUM( IF(AT.type='debit', amount, 0) ) as transfer_out_amount"));
            $transferData = $account->first();

            $transfer_amount_in= !empty($transferData->transfer_in_amount) ? (float)$transferData->transfer_in_amount : 0;
            $transfer_amount_out= !empty($transferData->transfer_out_amount) ? (float)$transferData->transfer_out_amount : 0;


            //Get Details 
            $transferDetailsData = Account::leftjoin(
                'account_transactions as AT',
                'AT.account_id',
                '=',
                'accounts.id'
            )
            
            ->join('account_transactions as TT','TT.id','=','AT.transfer_transaction_id')
            ->join('accounts as TAC','TAC.id','=','TT.account_id')
            ->join('users','users.id','=','AT.created_by')

            ->whereNull('AT.deleted_at')
            ->where('accounts.business_id', $business_id)
            ->where('accounts.id', $id)
            ->where('AT.sub_type','fund_transfer');
            
            if($date!=null){
                $transferDetailsData->where(DB::raw('date(AT.created_at)'),'=', $date);
            }
            
            $transferDetailsData->whereNull('AT.deleted_at');
            
            $transferDetailsData->select('AT.*','TT.account_id as TT_ID','TAC.name','TAC.account_number','users.surname','users.first_name','users.last_name');
            
            $transferDetails = $transferDetailsData->get();

            $transferDetails_html ='';
             if(isset($transferDetails)){

            $table_head ='<table class="table table-sm table-no-pd">                                        
                <thead>
                    <tr>
                        <th colspan="8">TRANSFER TRANSACTION DETAILS</th>
                    </tr>
                    <tr class="border">
                        <th>#</th>
                        <th class="text-center">OP Date</th>
                        <th class="text-center" >Created At</th>                                                
                        <th class="text-center">Reff</th>
                        <th class="text-center">Added By</th>
                        <th class="text-center">Note</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Amount</th>                                                                                                
                    </tr>
                </thead>
                <tbody>';
                
                $table_body_cnt='';
                $index=1;
                foreach($transferDetails as $trDetails){

                    $trf_account_data ='';
                    if($trDetails->type=='debit'){
                        $trf_account_data ='Transfer To : '; 
                    }else{
                        $trf_account_data ='Transfer From :'; 
                    }

                    $table_body_cnt.='
                        <tr>
                            <td class="text-center">'.$index.'</td>
                            <td class="text-center">'.$trDetails->operation_date.'</td>                            
                            <td class="text-center">'.$trDetails->created_at.'</td>
                            <td class="text-left">'.$trf_account_data.$trDetails->name.' AC/NO : '.$trDetails->account_number.'</td>
                            <td class="text-center">'.$trDetails->surname.'.'.$trDetails->first_name.' '.$trDetails->last_name.'</td>
                            <td class="text-center">'.$trDetails->note.'</td>
                            <td class="text-center">'.$trDetails->type.'</td>
                            <td class="text-right">'.number_format($trDetails->amount,2,'.','').'</td>
                        </tr>';

                        $index++;
                }

                $table_footer='</tbody>
                </table>';

                $transferDetails_html =  $table_head.$table_body_cnt.$transferDetails_html;

            }
            


        return ['transfer_in_amount'=>$transfer_amount_in,'transfer_out_amount'=>$transfer_amount_out, 'transfer_details' => $transferDetails_html];
    }

    public  function getAccountDeposit($id, $date)
    {   
       

        // if (!auth()->user()->can('account.access')) {
        //     abort(403, 'Unauthorized action.');
        // }
        $deposit_amount= 0;

        $business_id = session()->get('user.business_id');
        $account = Account::leftjoin(
            'account_transactions as AT',
            'AT.account_id',
            '=',
            'accounts.id'
        )
            ->whereNull('AT.deleted_at')
            ->where('accounts.business_id', $business_id)
            ->where('accounts.id', $id)
            ->where('sub_type','deposit');

            if($date!=null){
                $account->where(DB::raw('date(AT.created_at)'),'=', $date);
            }
            
            $account->whereNull('AT.deleted_at');

            $account->select(DB::raw("SUM( IF(AT.type='credit', amount,0) ) as deposit_amount"));
            $depositData = $account->first();

            $deposit_amount= !empty($depositData->deposit_amount) ? (float)$depositData->deposit_amount : 0;;


            //Get Details 
            $depositDetailsData = Account::leftjoin(
                'account_transactions as AT',
                'AT.account_id',
                '=',
                'accounts.id'
            )
            
            ->leftjoin('account_transactions as TT','TT.id','=','AT.transfer_transaction_id')
            ->leftjoin('accounts as TAC','TAC.id','=','TT.account_id')
            ->join('users','users.id','=','AT.created_by')

            ->whereNull('AT.deleted_at')
            ->where('accounts.business_id', $business_id)
            ->where('accounts.id', $id)
            ->where('AT.sub_type','deposit');
            
            if($date!=null){
                $depositDetailsData->where(DB::raw('date(AT.created_at)'),'=', $date);
            }
            $depositDetailsData->whereNull('AT.deleted_at');
            
            $depositDetailsData->select('AT.*','TT.account_id as TT_ID','TAC.name','TAC.account_number','users.surname','users.first_name','users.last_name');
            
            $depositDetails = $depositDetailsData->get();
            
            $depositDetails_html ='';
            if(isset($depositDetails)){

            $table_head ='<table class="table table-sm table-no-pd">                                        
                <thead>
                    <tr>
                        <th colspan="6">DEPOSIT TRANSACTION DETAILS</th>
                    </tr>
                    <tr class="border">
                        <th>#</th>
                        <th class="text-center">OP Date</th>
                        <th class="text-center" >Created At</th>                                                
                        <th class="text-center">Added By</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Amount</th>                                                                                                
                    </tr>
                </thead>
                <tbody>';
                
                $table_body_cnt='';
                $index=1;
                foreach($depositDetails as $trDetails){
                    $table_body_cnt.='
                        <tr>
                            <td class="text-center">'.$index.'</td>
                            <td class="text-center">'.$trDetails->operation_date.'</td>
                            <td class="text-center">'.$trDetails->created_at.'</td>
                            <td class="text-center">'.$trDetails->surname.'.'.$trDetails->first_name.' '.$trDetails->last_name.'</td>
                            <td class="text-center">'.$trDetails->type.'</td>
                            <td class="text-right">'.number_format($trDetails->amount,2,'.','').'</td>
                        </tr>';

                        $index++;
                }

                $table_footer='</tbody>
                </table>';

                $depositDetails_html =  $table_head.$table_body_cnt.$table_footer;
            }


        return ['deposit_amount' => number_format($deposit_amount,2,'.',''), 'deposit_details' => $depositDetails_html];
    }

    public function getSalesDetails($location_id, $date)
    {
         $business_id = request()->session()->get('user.business_id');
         $sells = Transaction::
                        // with(['payment_lines'=>function($query) use($date){
                        //     $query->where(DB::raw('date(transaction_payments.paid_on)'),$date);
                        // }])
              where('transactions.business_id', $business_id)
            ->where('transactions.location_id', $location_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->whereBetween(DB::raw('date(transactions.transaction_date)'),[$date,$date])
            ->select('transactions.*',
                DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id) as total_paid'),
                DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.method="cash") as cash_payment'),
                DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.method="custom_pay_1") as card_payment'),
                DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.method="bank_transfer") as bank_transfer_payment'),
                DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.method="cheque") as cheque_payment'))
            ->get();
            
            //sales
            $net_sale =0;
            $credit_sale=0;
           

            //payment 
            $cash_amount =0;
            $card_amount =0;
            $cheque_amount =0;
            $bank_transfer_amount =0;
            
            //Paid
            $total_paid =0;
            
                            
            foreach($sells as $sale){

                
                //net Sale
                $net_sale +=  !empty($sale->final_total) ? (float)$sale->final_total : 0;

                //pmt
                $cash_amount +=  !empty($sale->cash_payment) ? (float)$sale->cash_payment : 0;
                $card_amount +=  !empty($sale->card_payment) ? (float)$sale->card_payment : 0;
                $cheque_amount +=  !empty($sale->cheque_payment) ? (float)$sale->cheque_payment : 0;
                $bank_transfer_amount +=  !empty($sale->bank_transfer_payment) ? (float)$sale->bank_transfer_payment : 0;

                $total_paid +=  !empty($sale->total_paid) ? (float)$sale->total_paid : 0;

            }

            $data=[
                'net_sale' => number_format($net_sale,2,'.',''),
                'total_paid' => number_format($total_paid,2,'.',''),
                'credit_sale' => number_format((float)($net_sale - $total_paid),2,'.',''),
                'cash_amount' => number_format($cash_amount,2,'.',''),
                'card_amount' => number_format($card_amount,2,'.',''),
                'cheque_amount' => number_format($cheque_amount,2,'.',''),
                'bank_transfer_amount' => number_format($bank_transfer_amount,2,'.',''),
            ];

            return $data;

        //  return $sells; 
       
       
    }

    public function getCollectionDetails($location_id, $date)
    {   
        $business_id = request()->session()->get('user.business_id');
            
            $cash_amount =0;
            $card_amount =0;
            $cheque_amount =0;
            $bank_transfer_amount =0;

            for($i=1; $i<=4;$i++){

                $sellsPayment = Transaction::join('transaction_payments as TP','TP.transaction_id','=','transactions.id')
                ->where('transactions.business_id', $business_id)
                ->where('transactions.location_id', $location_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                // ->whereBetween(DB::raw('date(TP.created_at)'),[$date,$date])
                ->where(DB::raw('date(transactions.transaction_date)'),'!=',$date)
                ->where(DB::raw('date(TP.created_at)'),$date)
                ->select('TP.*');
                
                switch ($i) {
                    case 1 :
                        $cash_amount = $sellsPayment->where('TP.method','cash')->sum('TP.amount');
                        break;
                    case 2 :
                        $card_amount = $sellsPayment->where('TP.method','custom_pay_1')->sum('TP.amount');
                        break;
                    case 3 :
                        $cheque_amount = $sellsPayment->where('TP.method','cheque')->sum('TP.amount');
                        break;    
                    case 4 :
                        $bank_transfer_amount = $sellsPayment->where('TP.method','bank_transfer')->sum('TP.amount');
                        break;   
                }
            }
           

            $data=[
                'net_collection' => number_format((float)($cash_amount+$card_amount+$cheque_amount+$bank_transfer_amount),2,'.',''),                                
                'cash_amount' => number_format((float)$cash_amount,2,'.',''),
                'card_amount' => number_format((float)$card_amount,2,'.',''),
                'cheque_amount' => number_format((float)$cheque_amount,2,'.',''),
                'bank_transfer_amount' => number_format((float)$bank_transfer_amount,2,'.',''),
            ];

            return $data;
    }

    public function getExpencesDetails($location_id, $date, $cash_account_id, $petty_cash_account_id)
    {
         $business_id = request()->session()->get('user.business_id');
         $sells = Transaction::
                        // with(['payment_lines'=>function($query) use($date){
                        //     $query->where(DB::raw('date(transaction_payments.paid_on)'),$date);
                        // }])
              where('transactions.business_id', $business_id)
            ->where('transactions.location_id', $location_id)
            ->where('transactions.type', 'expense')
            // ->where('transactions.status', 'final')
            ->whereBetween(DB::raw('date(transactions.transaction_date)'),[$date,$date])
            ->select('transactions.*',
                DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id) as total_paid'),

                //cash Account pmt
                DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.method="cash"
                AND transaction_payments.account_id = "'.$cash_account_id.'" ) as total_pmt_in_cash_ac'),

                 //Petty Cash Account pmt
                 DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                 FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.method="cash"
                 AND transaction_payments.account_id = "'.$petty_cash_account_id.'" ) as total_pmt_in_petty_cash_ac'),

                DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.method="cash") as cash_payment'),
                DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.method="custom_pay_1") as card_payment'),
                DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.method="bank_transfer") as bank_transfer_payment'),
                DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.method="cheque") as cheque_payment'))
            ->get();
            
            //sales
            $net_sale =0;
            $credit_sale=0;
           

            //payment 
            $cash_amount =0;
            $card_amount =0;
            $cheque_amount =0;
            $bank_transfer_amount =0;
            
            //Paid
            $total_paid =0;

            $total_pmt_in_cash_ac =0;
            $total_pmt_in_petty_cash_ac =0;

            
                            
            foreach($sells as $sale){

                
                //net Sale
                $net_sale +=  !empty($sale->final_total) ? (float)$sale->final_total : 0;

                //pmt
                $cash_amount +=  !empty($sale->cash_payment) ? (float)$sale->cash_payment : 0;
                $card_amount +=  !empty($sale->card_payment) ? (float)$sale->card_payment : 0;
                $cheque_amount +=  !empty($sale->cheque_payment) ? (float)$sale->cheque_payment : 0;
                $bank_transfer_amount +=  !empty($sale->bank_transfer_payment) ? (float)$sale->bank_transfer_payment : 0;

                $total_pmt_in_cash_ac +=  !empty($sale->total_pmt_in_cash_ac) ? (float)$sale->total_pmt_in_cash_ac : 0;;
                $total_pmt_in_petty_cash_ac +=  !empty($sale->total_pmt_in_petty_cash_ac) ? (float)$sale->total_pmt_in_petty_cash_ac : 0;

                $total_paid +=  !empty($sale->total_paid) ? (float)$sale->total_paid : 0;

            }

            $data=[
                'total_expense' => number_format($net_sale,2,'.',''),
                'total_paid_expense' => number_format($total_paid,2,'.',''),
                'total_due' => number_format((float)($net_sale - $total_paid),2,'.',''),
                'cash_amount' => number_format($cash_amount,2,'.',''),
                'card_amount' => number_format($card_amount,2,'.',''),
                'cheque_amount' => number_format($cheque_amount,2,'.',''),
                'bank_transfer_amount' => number_format($bank_transfer_amount,2,'.',''),
                'total_pmt_in_cash_ac' => number_format($total_pmt_in_cash_ac,2,'.',''),
                'total_pmt_in_petty_cash_ac' => number_format($total_pmt_in_petty_cash_ac,2,'.',''),
            ];

            return $data;

        //  return $sells; 
       
       
    }

    public function getPurchaseDetails($location_id, $date, $cash_account_id,$petty_cash_account_id)
    {
         $business_id = request()->session()->get('user.business_id');
         $sells = Transaction::
                        // with(['payment_lines'=>function($query) use($date){
                        //     $query->where(DB::raw('date(transaction_payments.paid_on)'),$date);
                        // }])
              where('transactions.business_id', $business_id)
            ->where('transactions.location_id', $location_id)
            ->where('transactions.type', 'purchase')
            // ->where('transactions.status', 'final')
            ->whereBetween(DB::raw('date(transactions.transaction_date)'),[$date,$date])
            ->select('transactions.*',
                DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id) as total_paid'),

                //cash Account pmt
                DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.method="cash"
                AND transaction_payments.account_id = "'.$cash_account_id.'" ) as total_pmt_in_cash_ac'),

                 //Petty Cash Account pmt
                 DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                 FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.method="cash"
                 AND transaction_payments.account_id = "'.$petty_cash_account_id.'" ) as total_pmt_in_petty_cash_ac'),

                DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.method="cash") as cash_payment'),
                DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.method="custom_pay_1") as card_payment'),
                DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.method="bank_transfer") as bank_transfer_payment'),
                DB::raw('(SELECT SUM(IF(date(transaction_payments.paid_on)="'.$date.'",transaction_payments.amount,0)) 
                FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.method="cheque") as cheque_payment'))
            ->get();
            
            //sales
            $net_sale =0;
            $credit_sale=0;
           

            //payment 
            $cash_amount =0;
            $card_amount =0;
            $cheque_amount =0;
            $bank_transfer_amount =0;
            
            //Paid
            $total_paid =0;

            
            $total_pmt_in_cash_ac =0;
            $total_pmt_in_petty_cash_ac =0;
            
                            
            foreach($sells as $sale){

                
                //net Sale
                $net_sale +=  !empty($sale->final_total) ? (float)$sale->final_total : 0;

                //pmt
                $cash_amount +=  !empty($sale->cash_payment) ? (float)$sale->cash_payment : 0;
                $card_amount +=  !empty($sale->card_payment) ? (float)$sale->card_payment : 0;
                $cheque_amount +=  !empty($sale->cheque_payment) ? (float)$sale->cheque_payment : 0;
                $bank_transfer_amount +=  !empty($sale->bank_transfer_payment) ? (float)$sale->bank_transfer_payment : 0;

                $total_pmt_in_cash_ac +=  !empty($sale->total_pmt_in_cash_ac) ? (float)$sale->total_pmt_in_cash_ac : 0;;
                $total_pmt_in_petty_cash_ac +=  !empty($sale->total_pmt_in_petty_cash_ac) ? (float)$sale->total_pmt_in_petty_cash_ac : 0;

                $total_paid +=  !empty($sale->total_paid) ? (float)$sale->total_paid : 0;

            }

            $data=[
                'total_purchase' => number_format($net_sale,2,'.',''),
                'total_paid_purchase' => number_format($total_paid,2,'.',''),
                'total_due' => number_format((float)($net_sale - $total_paid),2,'.',''),
                'cash_amount' => number_format($cash_amount,2,'.',''),
                'card_amount' => number_format($card_amount,2,'.',''),
                'cheque_amount' => number_format($cheque_amount,2,'.',''),
                'bank_transfer_amount' => number_format($bank_transfer_amount,2,'.',''),
                'total_pmt_in_cash_ac' => number_format($total_pmt_in_cash_ac,2,'.',''),
                'total_pmt_in_petty_cash_ac' => number_format($total_pmt_in_petty_cash_ac,2,'.',''),
            ];

            return $data;

        //  return $sells; 
       
       
    }

    public function getExpencessOldPaymentDetails($location_id, $date, $cash_account_id, $petty_cash_account_id)
    {   
        $business_id = request()->session()->get('user.business_id');
            
            $cash_amount =0;
            $card_amount =0;
            $cheque_amount =0;
            $bank_transfer_amount =0;

            $total_pmt_in_cash_ac =0;
            $total_pmt_in_petty_cash_ac =0;

            for($i=1; $i<=5;$i++){

                $sellsPayment = Transaction::join('transaction_payments as TP','TP.transaction_id','=','transactions.id')
                ->where('transactions.business_id', $business_id)
                ->where('transactions.location_id', $location_id)
                ->where('transactions.type', 'expense')
                // ->where('transactions.status', 'final')
                // ->whereBetween(DB::raw('date(TP.created_at)'),[$date,$date])
                ->where(DB::raw('date(transactions.transaction_date)'),'!=',$date)
                ->where(DB::raw('date(TP.created_at)'),$date)
                ->select('TP.*');
                
                switch ($i) {
                    case 1 :
                        $cash_amount = $sellsPayment->where('TP.method','cash')->sum('TP.amount');
                        break;
                    // case 2 :
                    //     $card_amount = $sellsPayment->where('TP.method','custom_pay_1')->sum('TP.amount');
                    //     break;
                    case 2 :
                        $cheque_amount = $sellsPayment->where('TP.method','cheque')->sum('TP.amount');
                        break;    
                    case 3 :
                        $bank_transfer_amount = $sellsPayment->where('TP.method','bank_transfer')->sum('TP.amount');
                        break;   
                    case 4 :
                        $total_pmt_in_cash_ac = $sellsPayment->where('TP.method','cash')->where('TP.account_id',$cash_account_id)->sum('TP.amount');
                        break;   
                    case 5 :
                        $total_pmt_in_petty_cash_ac = $sellsPayment->where('TP.method','cash')->where('TP.account_id',$petty_cash_account_id)->sum('TP.amount');
                        break;   
                }
            }
           

            $data=[
                'total_paid' => number_format((float)($cash_amount+$card_amount+$cheque_amount+$bank_transfer_amount), 2,'.',''),                               
                'cash_amount' => number_format((float)$cash_amount,2,'.',''),
                // 'card_amount' => number_format((float)$card_amount,2,'.',''),
                'cheque_amount' => number_format((float)$cheque_amount,2,'.',''),
                'bank_transfer_amount' => number_format((float)$bank_transfer_amount,2,'.',''),
                'total_pmt_in_cash_ac' => number_format($total_pmt_in_cash_ac,2,'.',''),
                'total_pmt_in_petty_cash_ac' => number_format($total_pmt_in_petty_cash_ac,2,'.',''),
            ];

            return $data;
    }
    
    public function getPurchaseOldPaymentDetails($location_id, $date, $cash_account_id,$petty_cash_account_id)
    {   
        $business_id = request()->session()->get('user.business_id');
            
            $cash_amount =0;
            $card_amount =0;
            $cheque_amount =0;
            $bank_transfer_amount =0;

            $total_pmt_in_cash_ac =0;
            $total_pmt_in_petty_cash_ac =0;

            for($i=1; $i<=5;$i++){

                $sellsPayment = Transaction::join('transaction_payments as TP','TP.transaction_id','=','transactions.id')
                ->where('transactions.business_id', $business_id)
                ->where('transactions.location_id', $location_id)
                ->where('transactions.type', 'purchase')
                // ->where('transactions.status', 'final')
                // ->whereBetween(DB::raw('date(TP.created_at)'),[$date,$date])
                ->where(DB::raw('date(transactions.transaction_date)'),'!=',$date)
                ->where(DB::raw('date(TP.created_at)'),$date)
                ->select('TP.*');
                
                switch ($i) {
                    case 1 :
                        $cash_amount = $sellsPayment->where('TP.method','cash')->sum('TP.amount');
                        break;
                    // case 2 :
                    //     $card_amount = $sellsPayment->where('TP.method','custom_pay_1')->sum('TP.amount');
                    //     break;
                    case 2 :
                        $cheque_amount = $sellsPayment->where('TP.method','cheque')->sum('TP.amount');
                        break;    
                    case 3 :
                        $bank_transfer_amount = $sellsPayment->where('TP.method','bank_transfer')->sum('TP.amount');
                        break;   
                    case 4 :
                        $total_pmt_in_cash_ac = $sellsPayment->where('TP.method','cash')->where('TP.account_id',$cash_account_id)->sum('TP.amount');
                        break;   
                    case 5 :
                        $total_pmt_in_petty_cash_ac = $sellsPayment->where('TP.method','cash')->where('TP.account_id',$petty_cash_account_id)->sum('TP.amount');
                        break;   
                }
            }
           

            $data=[
                'total_paid' => number_format((float)($cash_amount+$card_amount+$cheque_amount+$bank_transfer_amount), 2,'.',''),                               
                'cash_amount' => number_format((float)$cash_amount,2,'.',''),
                // 'card_amount' => number_format((float)$card_amount,2,'.',''),
                'cheque_amount' => number_format((float)$cheque_amount,2,'.',''),
                'bank_transfer_amount' => number_format((float)$bank_transfer_amount,2,'.',''),
                'total_pmt_in_cash_ac' => number_format($total_pmt_in_cash_ac,2,'.',''),
                'total_pmt_in_petty_cash_ac' => number_format($total_pmt_in_petty_cash_ac,2,'.',''),
            ];

            return $data;
    }
}
