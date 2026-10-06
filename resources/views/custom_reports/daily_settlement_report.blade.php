@extends('layouts.app')
@section('title', 'Cash Balance Report')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    {{-- <h1>{{ __('report.stock_report')}}</h1> --}}
    <h1>Cash Balance Report</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row no-print">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => null, 'method' => 'get', 'id' => 'stock_report_filter_form' ]) !!}
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('location_id',  __('purchase.business_location') . ':') !!}
                        {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                    </div>
                </div>     
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('report_date', 'Report Date' . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('report_date', date("Y/m/d") , ['class' => 'form-control', 'readonly', 'required']); !!}
                        </div>
                    </div>
                </div>
                {{-- <div class="col-sm-3">
                    SS
                    <div class="form-group">
                        <button type="button" id="submit_purchase_form" class="btn btn-primary pull-right btn-flat">@lang('messages.save')</button>
                    </div>
                </div> --}}
                
                {!! Form::close() !!}
            @endcomponent
        </div>
        
    </div>
    <div  class="row">
        <div class="col-sm-12">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="col-sm-12">
                    <h3>DAILY INCOME & EXPENSES REPORT - <span id="location_name"></span></h3>
                    <P>Date : <span id="report_date_print"></span></P>
                </div>
               <div class="col-xs-6 col-sm-6">
                <table class="table table-sm table-no-pd">
                    <thead>
                        <tr>
                            <th colspan="2"><h4><b>INCOME</b></h4></th>
                        </tr>                        
                    </thead>
                    <tbody>
                        <tr>
                            <th colspan="2"><h4 class="text-bold">SALES DETAIL (+)<h4/></th>                              
                        </tr>
                        <tr class="border">
                            <td class="text-left text-bold">Net Sale</td>
                            <td id="net_sale" class="text-right text-bold">0.00</td>                       
                        </tr>
                        <tr>
                            <td class="text-left">Cash Sale</td>
                            <td id="cash_sale" class="text-right">0.00</td>                       
                        </tr>
                        <tr>
                            <td class="text-left">Card Sale</td>
                            <td id="card_sale" class="text-right">0.00</td>                       
                        </tr>
                        <tr>
                            <td class="text-left">Cheque Sale</td>
                            <td id="cheque_sale" class="text-right">0.00</td>                       
                        </tr>
                        <tr>
                            <td class="text-left">Bank Transfer Sale</td>
                            <td id="bank_trnf_sale" class="text-right">0.00</td>                       
                        </tr>
                        <tr class="border">
                            <td class="text-left"><b>Total Paid Sale</b></td>
                            <td class="text-right"><b id="total_paid_sale">0.00</b></td>                       
                        </tr>
                        <tr class="border">
                            <td class="text-left"><b>Sales Due</b> <i>(Credit Sale)</i></td>
                            <td class="text-right"><b id="total_credit_sale">0.00</b></td>                       
                        </tr>



                        <tr>
                            <th colspan="2"><h4 class="text-bold">COLLECTION DETAIL (+)<h4/></th>                              
                        </tr>
                        <tr>
                            <td class="text-left">Cash Collection</td>
                            <td id="cash_collection" class="text-right">0.00</td>                       
                        </tr>
                        <tr>
                            <td class="text-left">Card Collection</td>
                            <td id="card_collection" class="text-right">0.00</td>                       
                        </tr>
                        <tr>
                            <td class="text-left">Cheque Collection</td>
                            <td id="cheque_collection" class="text-right">0.00</td>                       
                        </tr>
                        <tr>
                            <td class="text-left">Bank Transfer Collection</td>
                            <td id="bank_trnf_collection" class="text-right brder-bottom">0.00</td>                       
                        </tr>                        
                        <tr class="border">
                            <td class="text-left text-bold">Net Collection</td>
                            <td id="net_collection" class="text-right text-bold">0.00</td>                       
                        </tr>

                        <tr>
                            <th><h5 class="text-bold">TOTAL INCOME <h5/> (Total Paid Sale + Net Collection) </th>
                            <th class="text-right"><h5 id="total_income" class="text-bold">0.00</h5></th>                
                        </tr>     
                    </tbody>
                </table>
               </div>
               <div class="col-xs-6 col-sm-6">
                <table class="table table-sm table-no-pd">
                    <thead>
                        <tr>
                            <th colspan="2"><h4><b>EXPENSES</b></h4></th>
                        </tr>                        
                    </thead>
                    <tbody>
                        
                        
                        <tr>
                            <th colspan="2"><h4 class="text-bold">PURCHASES DETAIL (-)<h4/></th>                              
                        </tr>
                        <tr class="border">
                            <td class="text-left text-bold">Total Purchases <i>(Today)</i></td>
                            <td id="total_purchase" class="text-right text-bold">0.00</td>                       
                        </tr>
                        <tr>
                            <td class="text-left text-bold">Total Purchase Due <i>(Today)</i></td>
                            <td id="total_purchase_due" class="text-right text-bold">0.00</td>                       
                        </tr>
                        <tr>
                            <td class="text-left">Cash <i>( Today PMT : <span id="cash_pp_today">0.00</span> + Old PMT : <span id="cash_pp_old">0.00</span> )</td>
                            <td id="cash_pp" class="text-right">0.00</td>                       
                        </tr>                        
                        <tr>
                            <td class="text-left">Cheque <i>( Today PMT : <span id="cheque_pp_today">0.00</span> + Old PMT : <span id="cheque_pp_old">0.00</span> )</td>
                            <td id="cheque_pp" class="text-right">0.00</td>                       
                        </tr>
                        <tr>
                            <td class="text-left">Bank Transfer <i>( Today PMT : <span id="bank_trnf_pp_today">0.00</span> + Old PMT : <span id="bank_trnf_pp_old">0.00</span> )</td>
                            <td id="bank_trnf_pp" class="text-right brder-bottom">0.00</td>                       
                        </tr>                        
                        <tr class="border">
                            <td class="text-left text-bold">Total Purchases Payment (Today + Old)</td>
                            <td id="total_pp_paid" class="text-right text-bold">0.00</td>                       
                        </tr>
                        

                        <tr>
                            <th colspan="2"><h4 class="text-bold">EXPENSES DETAIL (-)<h4/></th>                              
                        </tr>
                        <tr class="border">
                            <td class="text-left text-bold">Total Expenses <i>(Today)</i></td>
                            <td id="total_exp" class="text-right text-bold">0.00</td>                       
                        </tr>
                        <tr>
                            <td class="text-left text-bold">Total Due <i>(Today)</i></td>
                            <td id="exp_due" class="text-right text-bold">0.00</td>                       
                        </tr>
                        <tr>
                            <td class="text-left">Cash <i>( Today PMT : <span id="cash_exp_today">0.00</span> + Old PMT : <span id="cash_exp_old">0.00</span> )</i></td>
                            <td id="cash_exp" class="text-right">0.00</td>                       
                        </tr>                        
                        <tr>
                            <td class="text-left">Cheque <i>( Today PMT : <span id="cheque_exp_today">0.00</span> + Old PMT : <span id="cheque_exp_old">0.00</span> )</i></td>
                            <td id="cheque_exp" class="text-right">0.00</td>                       
                        </tr>
                        <tr>
                            <td class="text-left">Bank Transfer <i>( Today PMT : <span id="bank_trnf_exp_today">0.00</span> + Old PMT : <span id="bank_trnf_exp_old">0.00</span> )</i></td>
                            <td id="bank_trnf_exp" class="text-right brder-bottom">0.00</td>                       
                        </tr>                        
                                                                        
                        <tr class="border">
                            <td class="text-left text-bold">Expenses Total Payment (Today + Old)</td>
                            <td id="total_exp_paid" class="text-right text-bold">0.00</td>                       
                        </tr>

                        <tr>
                            <th><h5 class="text-bold">TOTAL EXPENSES <h5/> (Total Purchases Payment + Total Expenses Payment) </th>
                            <th class="text-right"><h5 id="total_expenses" class="text-bold">0.00</h5></th>                
                        </tr>
                        
                    </tbody>
                </table>
               </div>
               <div class="col-sm-12" style="pa">
                    <table class="table table-sm table-no-pd">
                        <thead>
                            <tr class="border">
                                <td colspan="2" class="text-center"><span style="font-size:16px" class="text-bold">CASH ACCOUNT BALANCE DETAIL ( AC NO : <span id="cash_account_no"></span> ) </span></td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-left">OPENING BALANCE </td>
                                <td id="cash_ac_ob" class="text-right">0.00</td>                       
                            </tr>
                            <tr>
                                <td class="text-left">DEPOSIT </td>
                                <td id="cash_ac_deposit" class="text-right">0.00</td>                       
                            </tr>
                            <tr>
                                <td class="text-left">TRANSFER IN </td>
                                <td id="cash_ac_trnf_in" class="text-right">0.00</td>                       
                            </tr>
                            <tr>
                                <td class="text-left">TOTAL CASH INCOME </td>
                                <td id="cash_ac_total_income" class="text-right">0.00</td>                       
                            </tr>
                            <tr>
                                <td class="text-left"> TOTAL EXPENSES  <i class="text-bold">( " Purchases + Expenses" Cash Account Payment Only )</i></td>
                                <td id="cash_ac_total_expences" class="text-right brder-bottom">(0.00)</td>                       
                            </tr>                        
                            <tr class="border">
                                <td class="text-left text-bold">BALANCE </td>
                                <td id="cash_ac_balance_without_trnf_out" class="text-right text-bold">0.00</td>                       
                            </tr>                            
                            <tr class="">
                                <td class="text-left">TRANSFER OUT </td>
                                <td id="cash_ac_trnf_out" class="text-right">(0.00)</td>                       
                            </tr> 
                            <tr class="border">
                                <td class="text-left text-bold">CLOSING BALANCE </td>
                                <td id="cash_ac_closing_balance" class="text-right text-bold">0.00</td>
                            </tr>   
                            <tr>
                                <td colspan="2"></td>
                            </tr>                          
                            <tr>
                                <td id="deposit_data_table" colspan="2">                                   
                                </td>
                            </tr>
                            <tr>
                                <td id="transfer_data_table" colspan="2">                                   
                                </td>
                            </tr>

                        </tbody>
                    </table>
               </div>
            
            @endcomponent
        </div>
    </div>
    <div class="row no-print">
        <div class="col-sm-12">
            <button type="button" class="btn btn-primary pull-right" 
            aria-label="Print" onclick="window.print();"
            ><i class="fa fa-print"></i> @lang( 'messages.print' )</button>
        </div>
    </div>
</section>
<!-- /.content -->

@endsection

@section('javascript')

<script>
$(document).ready(function() {

   // let net_sale =$('#net_sale');

    $('#report_date').datepicker({
        format: 'yyyy/mm/dd',
        ignoreReadonly: true,
    });

    $('#report_date').change(function(e) {
        e.preventDefault();
               $.ajaxSetup({
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                  }
              });
               jQuery.ajax({
                  url: "{{ url('/custom-reports/daily-settlement') }}",
                  method: 'post',
                  data: {
                     location: jQuery('#location_id').val(),
                     date: jQuery('#report_date').val(),
                      
                  },
                  success: function(result){
                     console.log(result);
                     $('#location_name').html(result.location_name);
                     $('#report_date_print').html(result.report_date);
                    //sale data
                     $('#net_sale').html(result.sales.net_sale);
                     $('#cash_sale').html(result.sales.cash_amount);
                     $('#card_sale').html(result.sales.card_amount);
                     $('#cheque_sale').html(result.sales.cheque_amount);
                     $('#bank_trnf_sale').html(result.sales.bank_transfer_amount);
                     $('#total_paid_sale').html(result.sales.total_paid);
                     $('#total_credit_sale').html(result.sales.credit_sale);

                     //Collection                     
                     $('#net_collection').html(result.collection.net_collection);
                     $('#cash_collection').html(result.collection.cash_amount);
                     $('#card_collection').html(result.collection.card_amount);
                     $('#cheque_collection').html(result.collection.cheque_amount);
                     $('#bank_trnf_collection').html(result.collection.bank_transfer_amount);

                    //Total Income
                     $('#total_income').html((parseFloat(result.sales.total_paid)+parseFloat(result.collection.net_collection)).toFixed(2));
                     
                    //Purchase Data
                    $('#total_purchase').html(result.purchase.total_purchase);
                    $('#total_purchase_due').html(result.purchase.total_due);
                    
                    $('#cash_pp_today').html(result.purchase.cash_amount);
                    $('#cash_pp_old').html(result.purchase_old_pmt.cash_amount);
                    $('#cash_pp').html((parseFloat(result.purchase.cash_amount)+parseFloat(result.purchase_old_pmt.cash_amount)).toFixed(2));

                    $('#cheque_pp_today').html(result.purchase.cheque_amount);
                    $('#cheque_pp_old').html(result.purchase_old_pmt.cheque_amount);                    
                    $('#cheque_pp').html((parseFloat(result.purchase.cheque_amount) + parseFloat(result.purchase_old_pmt.cheque_amount)).toFixed(2));

                    $('#bank_trnf_today').html(result.purchase.bank_transfer_amount);
                    $('#bank_trnf_old').html(result.purchase_old_pmt.bank_transfer_amount);                   
                    $('#bank_trnf_pp').html((parseFloat(result.purchase.bank_transfer_amount)+parseFloat(result.purchase_old_pmt.bank_transfer_amount)).toFixed(2));

                    $('#total_pp_paid').html((parseFloat(result.purchase.total_paid_purchase)+parseFloat(result.purchase_old_pmt.total_paid)).toFixed(2));

                    
                    
                    //Expenses Data
                    $('#total_exp').html(result.expense.total_expense);
                    $('#exp_due').html(result.expense.total_due);
                    
                    $('#cash_exp_today').html(result.expense.cash_amount);
                    $('#cash_exp_old').html(result.expense_old_pmt.cash_amount);
                    $('#cash_exp').html((parseFloat(result.expense.cash_amount)+parseFloat(result.expense_old_pmt.cash_amount)).toFixed(2));

                    $('#cheque_exp_today').html(result.expense.cheque_amount);
                    $('#cheque_exp_old').html(result.expense_old_pmt.cheque_amount);                    
                    $('#cheque_exp').html((parseFloat(result.expense.cheque_amount) + parseFloat(result.expense_old_pmt.cheque_amount)).toFixed(2));

                    $('#bank_trnf_exp_today').html(result.expense.bank_transfer_amount);
                    $('#bank_trnf_exp_old').html(result.expense_old_pmt.bank_transfer_amount);                   
                    $('#bank_trnf_exp').html((parseFloat(result.expense.bank_transfer_amount)+parseFloat(result.expense_old_pmt.bank_transfer_amount)).toFixed(2));

                    $('#total_exp_paid').html((parseFloat(result.expense.total_paid_expense)+parseFloat(result.expense_old_pmt.total_paid)).toFixed(2));

                    //Total Expenses
                    $('#total_expenses').html((
                        parseFloat(result.expense.total_paid_expense)
                      + parseFloat(result.expense_old_pmt.total_paid)
                      + parseFloat(result.purchase.total_paid_purchase)
                      + parseFloat(result.purchase_old_pmt.total_paid)).toFixed(2));
                 


                  //Cash Account Balance Details
                  
                  var cash_ac_ob = 0;
                  var cash_ac_closing_balance = 0;
                  var total_paid_in_cash_ac = 0;
                  var cash_ac_deposit = 0;
                  var cash_ac_transfer_in = 0;
                  var cash_ac_transfer_out = 0;

                  var total_cash_income = 0;

                    if(result.cash_account.ob.balance!=null){
                        cash_ac_ob = parseFloat(result.cash_account.ob.balance);
                    }
                  
                  
                  cash_ac_deposit = (parseFloat(result.cash_account.ac_dep.deposit_amount));
                  cash_ac_transfer_in = (parseFloat(result.cash_account.ac_trf.transfer_in_amount));
                  cash_ac_transfer_out = (parseFloat(result.cash_account.ac_trf.transfer_out_amount));

                  total_paid_exp_from_cash_ac = ( parseFloat(result.expense.total_pmt_in_cash_ac)
                                           + parseFloat(result.expense_old_pmt.total_pmt_in_cash_ac)
                                           + parseFloat(result.purchase.total_pmt_in_cash_ac)
                                           + parseFloat(result.purchase_old_pmt.total_pmt_in_cash_ac));
                    
                  total_cash_income = (parseFloat(result.sales.cash_amount) + parseFloat(result.collection.cash_amount));


                  var cash_ac_balance_cal_without_trnf_out = (cash_ac_ob + cash_ac_deposit+cash_ac_transfer_in+total_cash_income) - (total_paid_exp_from_cash_ac);
                  var cash_ac_closing_balance = (cash_ac_balance_cal_without_trnf_out - cash_ac_transfer_out);

                  $('#cash_account_no').html(result.cash_account.ob.account_number);

                  $('#cash_ac_ob').html((cash_ac_ob).toFixed(2));

                  $('#cash_ac_deposit').html((cash_ac_deposit).toFixed(2));
                  $('#cash_ac_trnf_in').html((cash_ac_transfer_in).toFixed(2));
                  $('#cash_ac_total_income').html((total_cash_income).toFixed(2));                   

                  $('#cash_ac_total_expences').html((total_paid_exp_from_cash_ac).toFixed(2));

                  $('#cash_ac_balance_without_trnf_out').html((cash_ac_balance_cal_without_trnf_out).toFixed(2));

                  $('#cash_ac_trnf_out').html((cash_ac_transfer_out).toFixed(2));
                  
                  $('#cash_ac_closing_balance').html((cash_ac_closing_balance).toFixed(2)); 

                  $('#deposit_data_table').html(result.cash_account.ac_dep.deposit_details)

                  $('#transfer_data_table').html(result.cash_account.ac_trf.transfer_details)

                }});


    });



});
</script>

<style>
.table-no-pd tbody td, .table-no-pd tbody th {
    margin-top: 0px !important;
    margin-bottom: 0px !important;
    padding-top: 0px !important;
    padding-bottom: 0px !important; 
}
.border td {
    border-bottom: 1px solid black !important;
    border-top: 1px solid black !important;
}
</style>
@endsection