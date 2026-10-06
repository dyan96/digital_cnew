@extends('layouts.app')
@section('title','Cost of Sale')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Cost Of Sales Report</h1>
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

                        {!! Form::label('product_sr_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'product_sr_date_filter', 'readonly']); !!}
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
                            id="cost_of_sales_report_table" style="width: 100%;">
                                <thead> 
                                    <tr>
                                        <th>@lang('product.sku')</th>
                                        <th>@lang('sale.product')</th>                                        
                                        {{-- <th>@lang('messages.date')</th> --}}
                                        <th>Current stock (MS + LCs)</th>
                                        <th>Current stock (LCs)</th>
                                        <th>@lang('report.total_unit_sold')</th>
                                        <th>Value @ Sale</th>
                                        <th>Value @ Cost</th>
                                        <th>Gross Profit</th>
                                        <th>GP (%)</th>

                                        
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr class="bg-gray font-17 footer-total text-center">
                                        <td colspan="4"><strong>@lang('sale.total'):</strong></td>
                                        <td id="footer_total_grouped_sold"></td>   
                                        <td><span class="display_currency" id="footer_subtotal" data-currency_symbol ="true"></span></td>
                                        <td><span class="display_currency" id="footer_total_cost_val" data-currency_symbol ="true"></span> </td>
                                        <td><span class="display_currency" id="footer_total_gp_val" data-currency_symbol ="true"></span> </td>
                                        <td><span class="" id="footer_total_gp_avg" ></span> </td>
                                    </tr>
                                </tfoot>
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

            if ($('#product_sr_date_filter').length == 1) {
                $('#product_sr_date_filter').daterangepicker(
                    dateRangeSettings, 
                    function(start, end) {
                        $('#product_sr_date_filter').val(
                            start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
                        );
                       // product_sell_report.ajax.reload();
                        product_sell_grouped_report.ajax.reload();
                        //product_sell_report_with_purchase_table.ajax.reload();
                    }
                );
                $('#product_sr_date_filter').on('cancel.daterangepicker', function(ev, picker) {
                    $('#product_sr_date_filter').val('');
                   // product_sell_report.ajax.reload();
                    product_sell_grouped_report.ajax.reload();
                   // product_sell_report_with_purchase_table.ajax.reload();
                });
            }

            product_sell_grouped_report = $('table#cost_of_sales_report_table').DataTable({
                    processing: true,
                    serverSide: true,
                    aaSorting: [[1, 'desc']],
                    ajax: {
                        url: '/reports/cost-of-sales/get-data',
                        data: function(d) {
                            var start = '';
                            var end = '';
                            if ($('#product_sr_date_filter').val()) {
                                start = $('input#product_sr_date_filter')
                                    .data('daterangepicker')
                                    .startDate.format('YYYY-MM-DD');
                                end = $('input#product_sr_date_filter')
                                    .data('daterangepicker')
                                    .endDate.format('YYYY-MM-DD');
                            }
                            d.start_date = start;
                            d.end_date = end;

                            d.variation_id = $('#variation_id').val();
                            d.customer_id = $('select#customer_id').val();
                            d.location_id = $('select#location_id').val();
                            d.category_id = $('select#category_id').val();
                        },
                    },
                    columns: [
                        { data: 'sub_sku', name: 'v.sub_sku' },
                        { data: 'product_name', name: 'p.name' },            
                        { data: 'current_stock_all', name: 'current_stock_all',searchable: false, orderable: false },
                        { data: 'current_stock', name: 'current_stock', searchable: false, orderable: false },           
                        { data: 'total_qty_sold', name: 'total_qty_sold', searchable: false },
                        { data: 'subtotal', name: 'subtotal', searchable: false },            
                        { data: 'total_cost_as_val', name: 'total_cost_as_val', searchable: false, orderable: true },            
                        { data: 'gross_profit', name: 'gross_profit', searchable: false, orderable: false },
                        { data: 'gross_profit_perc', name: 'gross_profit_perc', searchable: false, orderable: false },
                    ],
                    fnDrawCallback: function(oSettings) {
                        
                        $('#footer_subtotal').text(
                            sum_table_col($('#cost_of_sales_report_table'), 'row_subtotal')
                        );
                         
                        $('#footer_total_cost_val').text(
                            sum_table_col($('#cost_of_sales_report_table'), 'row_total_cost_val')
                        );
                        $('#footer_total_gp_val').text(
                            sum_table_col($('#cost_of_sales_report_table'), 'row_gross_profit')
                        );
                        $('#footer_total_grouped_sold').html(
                            __sum_stock($('#cost_of_sales_report_table'), 'sell_qty')
                        );
                        //(($row->subtotal - $row->total_cost_as_val)/$row->subtotal * 100.0)
                        var subtot =  sum_table_col($('#cost_of_sales_report_table'), 'row_subtotal');
                        var costtot =  sum_table_col($('#cost_of_sales_report_table'), 'row_total_cost_val');
                        var gp_avg = ((subtot-costtot)/subtot * 100.0).toFixed(2)
                        $('#footer_total_gp_avg').text(
                            gp_avg+'%'
                        );
                        
                        __currency_convert_recursively($('#cost_of_sales_report_table'));
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


});

    </script>
@endsection