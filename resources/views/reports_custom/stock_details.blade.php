@extends('layouts.app')
@section('title','Stock Details Report')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Stock Details Report</h1>
</section>
 
<!-- Main content -->
<section class="content">
    {{-- <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('ir_supplier_id', __('purchase.supplier') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </span>
                        {!! Form::select('ir_supplier_id', $suppliers, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('ir_purchase_date_filter', __('purchase.purchase_date') . ':') !!}
                    {!! Form::text('ir_purchase_date_filter', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('ir_customer_id', __('contact.customer') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </span>
                        {!! Form::select('ir_customer_id', $customers, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('ir_sale_date_filter', __('lang_v1.sell_date') . ':') !!}
                    {!! Form::text('ir_sale_date_filter', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('ir_location_id', __('purchase.business_location').':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-map-marker"></i>
                        </span>
                        {!! Form::select('ir_location_id', $business_locations, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
                    </div>
                </div>
            </div>
            @if(Module::has('Manufacturing'))
                <div class="col-md-3">
                    <div class="form-group">
                        <br>
                        <div class="checkbox">
                            <label>
                              {!! Form::checkbox('only_mfg', 1, false, 
                              [ 'class' => 'input-icheck', 'id' => 'only_mfg_products']); !!} {{ __('manufacturing::lang.only_mfg_products') }}
                            </label>
                        </div>
                    </div>
                </div>
            @endif
            @endcomponent
        </div>
    </div> --}}
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])              
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
                                        <th>Opening Stock</th>
                                        <th>Purchase</th>
                                        <th>Transfer IN</th>
                                        <th>Transfer OUT</th>
                                        <th>Purchase Return</th>
                                        <th>Sold</th>
                                        <th>Return</th>
                                        <th>Stock Adjustment</th>                                        
                                        <th>Stock In Hand</th>                                        
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
    <script>
        $(document).ready(function() {

            if ($('#date_of_report').length == 1) {               
                $('#date_of_report').datepicker({
                    autoclose: true,
                    format: datepicker_date_format
                });               
            }
            product_sell_grouped_report = $('table#stock_detail_report_table').DataTable({
                    processing: true,
                    serverSide: true,
                    aaSorting: [[0, 'ASC']],
                    ajax: {
                        url: '/reports/stock-detail-report/get-data',
                        data: function(d) {                           
                            d.date_of_report = $('#date_of_report').val();                                                                           
                            d.location_id = $('select#location_id').val();
                            d.category_id = $('select#category_id').val();
                        },
                    },
                    columnDefs: [                                        
                        { targets: [3,4,5,6,7,8,9,10,11],className: 'text-right' },                    
                    ],
                    columns: [
                        { data: 'sub_sku', name: 'sub_sku' },
                        { data: 'product_name', name: 'product_name' },            
                        { data: 'location_name', name: 'location_name',searchable: true, orderable: false },
                        { data: 'opening_stock', name: 'opening_stock', searchable: false },           
                        { data: 'purchase', name: 'purchase', searchable: false },
                        { data: 'transfer_in', name: 'transfer_in', searchable: false },                                    
                        { data: 'transfer_out', name: 'transfer_out', searchable: false },                                    
                        { data: 'purchase_return', name: 'purchase_return', searchable: false },                                    
                        { data: 'sold', name: 'sold', searchable: false },                                    
                        { data: 'sell_return', name: 'sell_return', searchable: false },                                    
                        { data: 'stock_adjustment', name: 'stock_adjustment', searchable: false },                                    
                        { data: 'balance', name: 'balance', searchable: false },                                    
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


});

    </script>
@endsection