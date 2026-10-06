@extends('layouts.app')
@section('title','Stock Bin Card Report')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Stock Bin Card</h1>
</section>
 
<!-- Main content -->
<section class="content">
   
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])              
                <div class="col-md-12">                    
                    <div id="product_select" class="form-group">
                        {!! Form::label('product_id',  'PRODUCT :') !!}
                        {!! Form::select('product_id', [], null, ['id'=>'product_id','class' => 'form-control select2', 'style' => 'width:100%']); !!}        
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('location_id', __('purchase.business_location').':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-map-marker"></i>
                            </span>
                            {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('category_id', __('product.category') . ':') !!}
                        {!! Form::select('category_id', $categories, null, ['id'=>'category_id','class' => 'form-control select2', 'style' => 'width:100%','placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('product_sr_date_filter', 'Date :') !!}
                        {!! Form::text('date_of_report', @format_date('now'), ['class' => 'form-control', 'id'=> 'date_of_report', 'readonly', 'required']); !!}         
                    </div>
                </div>
                {{-- {!! Form::close() !!} --}}
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" 
                            id="stock_detail_report_table" style="width: 100%;">
                                <thead> 
                                    <tr>
                                        <th>@lang('product.sku')</th>
                                        <th>@lang('sale.product')</th>                                                                                
                                        <th>Location</th>
                                        <th>Transaction</th>
                                        <th>Ref No</th> 
                                        <th>Trn.Date</th>                                                                                
                                        <th>IN</th>
                                        <th>OUT</th>
                                        <th>BALANCE</th>                                                                               
                                    </tr>
                                </thead>                              
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->
<div class="modal fade view_register" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

@endsection

@section('javascript')

    <script type="text/javascript">
        $(document).ready(function() {

            
            initSelect2($(this).find('#product_id'), $('#product_select'));	

            if ($('#date_of_report').length == 1) { 
                          
                $('#date_of_report').daterangepicker( 
                    dateRangeSettings,
                    function (start, end) {
                        $('#date_of_report').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                       // product_sell_grouped_report.ajax.reload();
                    }
                );
            }
            product_sell_grouped_report = $('table#stock_detail_report_table').DataTable(
                {
                    processing: true,
                    serverSide: true,
                    aaSorting: [[0, 'ASC']],
                    ajax: {
                        url: '/reports/stock-bin-card/get-data',
                        data: function(d) {  
                            var product_id = $('#product_id').val();                         
                            var start = '';
                            var end = '';
                            if ($('#date_of_report').val()) {
                                        start = $('input#date_of_report')
                                            .data('daterangepicker')
                                            .startDate.format('YYYY-MM-DD');
                                        end = $('input#date_of_report')
                                            .data('daterangepicker')
                                            .endDate.format('YYYY-MM-DD');
                            }
                            d.product_id = product_id;
                            d.start_date = start;
                            d.end_date = end;                                                                       
                            d.location_id = $('select#location_id').val();
                            d.category_id = $('select#category_id').val();
                        },
                    },
                    columnDefs: [                                        
                        { targets: [6,7,8],className: 'text-right' },                    
                    ],
                    columns: [
                        { data: 'sku', name: 'sku' },
                        { data: 'product_name', name: 'product_name' },            
                        { data: 'location_name', name: 'location_name',searchable: true, orderable: false },
                        { data: 'type', name: 'type', searchable: false },           
                        { data: 'ref_no', name: 'ref_no', searchable: false },
                        { data: 'transaction_date', name: 'transaction_date', searchable: false },                                    
                        { data: 'in_qty', name: 'in_qty', searchable: false, orderable: false },                                    
                        { data: 'out_qty', name: 'out_qty', searchable: false,orderable: false },                                    
                        { data: 'balance', name: 'balance', searchable: false, orderable: false},                                                                                              
                    ],
                    fnDrawCallback: function(oSettings) {                        
                    },
                });


                $('#location_id ').change(function() {
                  //  product_sell_report.ajax.reload();
                    product_sell_grouped_report.ajax.reload();
                   // product_sell_report_with_purchase_table.ajax.reload();
                });

                $('#category_id').change(function() {
                  //  product_sell_report.ajax.reload();
                    product_sell_grouped_report.ajax.reload();
                   // product_sell_report_with_purchase_table.ajax.reload();
                });

                $('#date_of_report').change(function() {
                  //  product_sell_report.ajax.reload();
                    product_sell_grouped_report.ajax.reload();
                   // product_sell_report_with_purchase_table.ajax.reload();
                });

                $(document).on('change', '#product_id', function() {
                    product_sell_grouped_report.ajax.reload();
                });


});

function initSelect2(element, dropdownParent = $('body')) {
        element.select2({
            ajax: {
                url: '/products/list',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term, // search term
                    };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function (value, key) {
                            var name = value.type == 'variable' ? value.name + ' - ' + value.variation : value.name;
                            name += ' (' + value.sub_sku + ')';
                            return {
                                id: value.variation_id,
                                text: name
                            }
                        })
                    };
                },
            },
            minimumInputLength: 1,
            escapeMarkup: function(markup) {
                return markup;
            },
            dropdownParent: dropdownParent
        });
    }

    </script>
   
@endsection