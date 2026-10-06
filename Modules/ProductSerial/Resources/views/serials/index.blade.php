@extends('layouts.app')
@section('title', 'Product Serial')
 
@section('content')
@include('productserial::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Product Serials</h1>
</section>
 
<!-- Main content -->
<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        @include('productserial::serials.partials.filters')
    @endcomponent

    @component('components.widget', ['class' => 'box-solid','title'=>'Serial List'])
       
        @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary" href="{{action('\Modules\ProductSerial\Http\Controllers\ProductSerialController@index')}}">
                    <i class="fa fa-plus"></i>Add New</a>
            </div>            
        @endslot
       
        <div class="table-responsive">
            <table class="table table-bordered table-sm" id="serial_table">
                <thead>
                    <tr>
                        <th width="5%">Action</th>
                        <th width="10%">Location</th>
                        <th width="15%">Product Name</th>
                        <th width="20%">Serila/ IMEI</th>
                        <th width="5%">Issued TRN</th>
                        <th width="10%">Issued Date</th>
                        <th width="10%">Added Date</th>
                        <th width="5%">Status</th>
                    </tr>
                </thead>                
            </table>
        </div>
    @endcomponent
</section>
<!-- /.content -->

@stop
@section('javascript')
@include('productserial::layouts.partials.common_script')

<script type="text/javascript">
$(document).ready( function () {
$('#filter_date_range').daterangepicker( 
     dateRangeSettings,
     function (start, end) {
         $('#filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
         serials_table.ajax.reload();
     }
 );
        
 $('#filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
     $('#filter_date_range').val('');
     serials_table.ajax.reload();
 });



        serials_table = $('#serial_table').DataTable({
	        processing: true,
	        serverSide: true,
	        ajax: {
                url: '{{action("\Modules\ProductSerial\Http\Controllers\ProductSerialController@getData")}}',
                method: 'GET',
                data: function(d) {
                    var start = '';
                    var end = '';
                    if ($('#filter_date_range').val()) {
                                start = $('input#filter_date_range')
                                    .data('daterangepicker')
                                    .startDate.format('YYYY-MM-DD');
                                end = $('input#filter_date_range')
                                    .data('daterangepicker')
                                    .endDate.format('YYYY-MM-DD');
                    }

                    d.start_date = start;
                    d.end_date = end;                     
                    d.status = $('select#status').val();    
                    d.location_id = $('select#location_id').val();  
                    d.product_id = $('select#product_id').val();  
                    d.ref_no = $('#ref_no').val();  

                },
            },
	         
	        "order": [[ 2, "desc" ]],
            "columnDefs": [
                // { className: "text-right", "targets": [4,5,6] },
                { className: "text-center", "targets": [0,1,4,5] },
                ] ,  
            "createdRow": function( row, data, dataIndex){
               // console.log(data.is_canceled)
                if( data.status ==  1){
                    $(row).addClass('bg-success');
                }
            }, 
	        columns: [	 
                { data: 'action', name: 'action', orderable:false,searchable:false },                  	 
	            { data: 'location_name', name: 'BL.name' },
	            { data: 'product_name', name: 'P.name' },
	            { data: 'serial_no', name: 'serial_no' },
	            { data: 'invoice_no', name: 'TS.invoice_no' },	            
	            { data: 'transaction_date', name: 'TS.transaction_date' },	            
	            { data: 'created_at', name: 'created_at' },	            
	            { data: 'status', name: 'status', searchable:false },	            
	            
	        ],
	        fnDrawCallback: function(oSettings) {
	            __currency_convert_recursively($('#serial_table'));
	        },
		});


       
        

        $(document).on('change', '#location_id', function() {
            serials_table.ajax.reload();
		});

       
        $(document).on('change', '#status', function() {
            serials_table.ajax.reload();
		});
        
        $(document).on('change', '#product_id', function() {
            serials_table.ajax.reload();
		});

        // $(document).on('change', '#document_type', function() {
        //     transaction_table.ajax.reload();
		// });
});

function resetProductSelected(){
            $('#product_id').val(null).trigger('change');  
            serials_table.ajax.reload();   
            initSelect2($(this).find('#product_id'), $('#product_select'));	    
}

function restoreSerial(id,serial){
    swal( {
        title: "Restore Serial No",
        text: "Are you sure, want to restore this serial ? please verify serial no to restore",
        icon: "warning",
        buttons: true,
        content: "input",
        }
    )
    .then((value) => {
        console.log(value)
        
        if(value==serial){
            $.ajax({
                method: 'PUT',
                url: '/productserial/restore',				
                data: { id: id},
                success: function(result) {	

                    if (result=='DONE') {
                        toastr.success('Transaction Successfully');  
                        serials_table.ajax.reload();
                    }else{                        
                        toastr.error('Something went wrong');
                        $('#serial_no').select()
                        //toastr.success('Transaction Successfully');
                    }

                    },
			});
        }else if((value != null) || (value == '')){
            toastr.error('Serial Number verification does not match. Please try again');
        }
    });

    
}
function deleteSerial(id){
    swal({
                  title: LANG.sure,
                  icon: "warning",
                  buttons: true,
                  dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                        method: 'DELETE',
                        url: '/productserial/destroy',				
                        data: { id: id},
                        success: function(result) {	
                            if (result=='DONE') {
                                toastr.success('Transaction Successfully');  
                                serials_table.ajax.reload();
                            }else{                        
                                toastr.error('Something went wrong');
                                $('#serial_no').select()
                                //toastr.success('Transaction Successfully');
                            }
                            },
                        });
                    }
                });
    
}
</script>
@endsection
