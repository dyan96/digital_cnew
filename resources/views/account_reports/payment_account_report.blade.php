@extends('layouts.app')
@section('title', __('account.payment_account_report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{ __('account.payment_account_report')}}</h1>
</section>

<!-- Main content -->
<section class="content no-print">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary" id="accordion">
              <div class="box-header with-border">
                <h3 class="box-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapseFilter">
                    <i class="fa fa-filter" aria-hidden="true"></i> @lang('report.filters')
                  </a>
                </h3>
              </div>
              <div id="collapseFilter" class="panel-collapse active collapse in" aria-expanded="true">
                <div class="box-body">
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('account_id', __('account.account') . ':') !!}
                            {!! Form::select('account_id', $accounts, null, ['class' => 'form-control select2']); !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('date_filter', __('report.date_range') . ':') !!}
                            {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'date_filter', 'readonly']); !!}
                        </div>
                    </div>
                </div>
              </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="payment_account_report">
                        <thead>
                            <tr>
                                <th><input type="checkbox"  id="select-all-row"></th>
                                <th>@lang('messages.date')</th>
                                <th>@lang('account.payment_ref_no')</th>
                                <th>@lang('account.invoice_ref_no')</th>
                                <th>@lang('lang_v1.payment_type')</th>
                                <th>@lang('account.account')</th>
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td colspan="7">
                                    <div style="display: flex; width: 100%;">
                                        <button type="button" class="btn btn-xs btn-success bulk_link_pmt_account" data-type="add">Link Account Selected</button>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->
<div class="modal fade" id="bulk_link_account_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
        
            {!! Form::open(['url' => action('AccountReportsController@postLinkAccountBulk'), 'method' => 'post', 'id' => 'link_account_form_bulk' ]) !!}
        
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang( 'account.link_account' ) - Selected all Payments</h4>
            </div>
        
            <div class="modal-body">
                <div class="form-group">
                    {!! Form::hidden('selected_transaction_payment',''); !!}
                    {!! Form::label('account_id_lb', __( 'account.account' ) .":") !!}
                    {!! Form::select('account_id_bulk', $accounts_list, [], ['class' => 'form-control', 'required','id'=>'account_id_bulk']); !!}
                </div>
            </div>
        
            <div class="modal-footer">
                <button type="submit" id="bulk_submit_btn" class="btn btn-primary">@lang( 'messages.save' )</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
            </div>
        
            {!! Form::close() !!}
        
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
</div>

@endsection

@section('javascript')
    
    <script type="text/javascript">
        $(document).ready(function(){
            if($('#date_filter').length == 1){
                $('#date_filter').daterangepicker(
                    dateRangeSettings,
                    function (start, end) {
                        $('#date_filter').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                        payment_account_report.ajax.reload();
                    }
                );

                $('#date_filter').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    payment_account_report.ajax.reload();
                });
            }

            payment_account_report = $('#payment_account_report').DataTable({
                            processing: true,
                            serverSide: true,
                            "ajax": {
                                "url": "{{action('AccountReportsController@paymentAccountReport')}}",
                                "data": function ( d ) {
                                    d.account_id = $('#account_id').val();
                                    var start_date = '';
                                    var endDate = '';
                                    if($('#date_filter').val()){
                                        var start_date = $('#date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                                        var endDate = $('#date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
                                    }
                                    d.start_date = start_date;
                                    d.end_date = endDate;
                                }
                            },
                            columnDefs:[{
                                "targets": 5,
                                "orderable": false,
                                "searchable": false
                            }],
                            columns: [
                                {data: 'select_pmt', orderable:false},
                                {data: 'paid_on', name: 'paid_on'},
                                {data: 'payment_ref_no', name: 'payment_ref_no'},
                                {data: 'transaction_number', name: 'transaction_number'},
                                {data: 'type', name: 'T.type'},
                                {data: 'account', name: 'account'},
                                {data: 'action', name: 'action'}
                            ],
                            "fnDrawCallback": function (oSettings) {
                                __currency_convert_recursively($('#payment_account_report'));
                            }
                        });
            
            $('select#account_id, #date_filter').change( function(){
                payment_account_report.ajax.reload();
            });
        })

        $(document).on('submit', 'form#link_account_form', function(e){
            e.preventDefault();
            var data = $(this).serialize();

            $.ajax({
                method: $(this).attr("method"),
                url: $(this).attr("action"),
                dataType: "json",
                data: data,
                success: function(result){
                    if(result.success === true){
                        $('div.view_modal').modal('hide');
                        toastr.success(result.msg);
                        payment_account_report.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });


        $(document).on('submit', 'form#link_account_form_bulk', function(e){
            e.preventDefault();
           // var data = $(this).serialize();
           var selected_rows = getSelectedRows();
           var account_id = $('select#account_id_bulk').val();

           // console.log(account_id);

            $.ajax({
                method: $(this).attr("method"),
                url: $(this).attr("action"),
                dataType: "json",
                data: {account_id : account_id,
                        payment_id_list : selected_rows },
                success: function(result){
                     console.log(result);
                    if(result.success === true){
                        $('div#bulk_link_account_modal').modal('hide');
                        // $('#bulk_submit_btn').
                        $( "#bulk_submit_btn" ).prop( "disabled", false );
                        toastr.success(result.msg);
                        payment_account_report.ajax.reload();

                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });
        

        $(document).on('click', '.bulk_link_pmt_account', function(e){
            e.preventDefault();
            var selected_rows = getSelectedRows();
            
            if(selected_rows.length > 0){
                // $('input#selected_transaction_payment').val(selected_rows);
               // var type = $(this).data('type');
                var modal = $('#bulk_link_account_modal');
                //console.log(selected_rows);
                modal.modal('show');
                 
            } else{
                $('input#selected_transaction_payment').val('');
                swal('@lang("lang_v1.no_row_selected")');
            }    
        });

        function getSelectedRows() {
            var selected_rows = [];
            var i = 0;
            $('.row-select:checked').each(function () {
                selected_rows[i++] = $(this).val();
            });

            return selected_rows; 
        }
    </script>
@endsection