@extends('layouts.app')
@section('title', 'Product Serial')
 
@section('content')
@include('productserial::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Serial Transfers</h1>
</section>
 
<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-solid','title'=>'Add New Transfer'])
    <div class="row">
        <div class="col-md-12">                 
            <table class="custom_table">
                <thead>
                    <tr>
                        <td class="col-sm-2">
                            <strong class="">LOCATION (From) :*</strong>                            
                        </td>
                        <td class="col-sm-4">
                            {!! Form::select('form_location_id', $locations, isset($transaction) ? $transaction->location_id : null, ['id' => 'from_location_id' ,'class' => 'form-control select2']); !!}                           
                        </td>  

                        <td class="col-sm-2">
                            <strong class="">LOCATION (To) :*</strong>                            
                        </td>
                        <td class="col-sm-4">
                            {!! Form::select('to_location_id', $locations, isset($transaction) ? $transaction->location_id : null, ['id' => 'to_location_id' ,'class' => 'form-control select2']); !!}                           
                        </td>  
                    </tr>
                    <tr>                     
                        <td class="col-sm-2 text-left">
                            <strong class="">PRODUCT NAME :</strong>
                        </td>  
                        <td colspan="3" id="serial_product_td" class="col-sm-10">
                            {{-- {!! Form::select('variation_id', [],  ['id' => 'variation_id' ,'class' => 'form-control select2', 'required']); !!} --}}
                            {!! Form::select('variation_id', [], null, ['class' => 'form-control', 'id' => 'variation_id', 'placeholder' => __('messages.please_select'), 'required', 'style' => 'width: 100%;']); !!}                          

                        </td>                                              
                    </tr>
                    <tr>                     
                        <td class="col-sm-2 text-left">
                            <strong class="">SERIAL/IMEI NO :</strong>
                        </td>  
                        <td colspan="2" class="col-sm-8">
                            {!! Form::text('serial_no', isset($transaction) ? $transaction->cheque_date : null, ['class' => 'form-control', 'id'=> 'serial_no', 'required']); !!}                      
                          
                        </td>
                        <td colspan="2" class="col-sm-2 text-left">
                            <button type="button" onclick="addSerialToList()" style="margin-left: 10px"  class="btn btn-success pull-left">Add to List</button>                     
                        </td>
                                                 
                    </tr>
                    <tr>
                        <td>-</td>
                        <td colspan="3">
                            <span id="serial_no_error" class="text-danger"></span>
                        </td>
                    </tr>
                </thead>                 
            </table>
            <table class="table table-bordered dp-table">
                <thead>
                    <tr class="bg-danger">
                        <th class="text-center" width="5%">#</th>   
                        <th width="40%">Product Name</th>                      
                        <th class="text-center" width="40%">Serial/IMEI NO</th>
                        <th class="text-center" width="5%">Action</th>
                    </tr>
                </thead>
                <tbody id="serial_no_tbody">
                    
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right">
                            <button type="button" style="margin-left: 10px" onclick="storeSerials()" class="btn btn-primary">UPDATE</button>                     
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
                
    @endcomponent
</section>
<!-- /.content -->

@stop
@section('javascript')
    @include('productserial::layouts.partials.common_script')


<script>

$('#serial_no').keyup(function(e){
    if(e.keyCode == 13)
    {
        addSerialToList();
    }
});

    async function  addSerialToList(){
        var serial_no = $('#serial_no').val();
        var product_id = $('#variation_id').val();
        var from_location_id = $('#from_location_id').val();
        var to_location_id = $('#to_location_id').val();

        if(!product_id){
            toastr.error('Please select Product First'); 
        }else if(!from_location_id){
            toastr.error('Please select from Location'); 
        }else if(!to_location_id){
            toastr.error('Please select to Location'); 
        }else{
            var hasEntry = hasDuplicatedEntry(serial_no);
            if(hasEntry==false){
                $('#serial_no_error').html('');
            checkHasSerialDB(product_id,serial_no,from_location_id);
            
            }else{
                $('#serial_no_error').html('Duplicate Serial/IMEI Found. Please Enter new Serial No');
                $('#serial_no').select()
            }
        }

        

    }

$("#serial_no_tbody").on("click", ".delete-btn", function() {
   $(this).closest("tr").remove();
   
   if($("#serial_no_tbody tr").length>0){
    $("#from_location_id").prop("disabled", true );   
    $("#to_location_id").prop("disabled", true ); 
   }else{
    $("#from_location_id").prop("disabled", false );   
    $("#to_location_id").prop("disabled", false );  
   }
});

    function hasDuplicatedEntry(serial_no){
        var hasEntry =false;
        $('#serial_no_tbody tr').each(function() {
            var serial_no_tb = $(this).find(".serial_no").val(); 
            if(serial_no_tb==serial_no){
                hasEntry = true;
            }
        });

        return hasEntry;
    }

     function checkHasSerialDB(product_id,serial_no,from_location) {             
            
			$.ajax({
				method: 'POST',
				url: '/productserial/check',				
				data: { product_id: product_id, serial_no: serial_no, type:'TRN',location: from_location},
				success: function(result) {
					 console.log(result)
					if (result.status=='OK') {
                        $("#from_location_id").prop( "disabled", true );   
                        $("#to_location_id").prop( "disabled", true ); 

                        $('#serial_no_tbody').append(result.html);
                        $('#serial_no').val('')
					}else if(result.status=='ISSUED'){
                        $('#serial_no_error').html('This serial no is already Issued in the system.');
                        $('#serial_no').select()
                    }else if(result.status=='NF'){
                        $('#serial_no_error').html('This serial no is not found in the system.');
                        $('#serial_no').select()
                    }else if(result.status=='IVL'){
                        $('#serial_no_error').html('This serial no is not available in this location.');
                        $('#serial_no').select()
                    }else{                        
                        toastr.error('Something went wrong');
                        $('#serial_no').select()
                        //toastr.success('Transaction Successfully');
                    }

				},
			});
        }

        function storeSerials() {   

            
            var from_location_id = $('#from_location_id').val();
            var to_location_id = $('#to_location_id').val();


            var serials =[];
            $('#serial_no_tbody tr').each(function() {
                var serial_id = $(this).find(".serial_id").val();                 
                serials.push(serial_id);                
            });    
            
			$.ajax({
				method: 'POST',
				url: '/productserial/transfer-store',				
				data: {                    
                    from_location_id: from_location_id,
                    to_location_id:to_location_id,
                    serials:serials},
				success: function(result) {
					 console.log(result)
					if (result=='DONE') {
                        $('#serial_no_tbody').html(null);
                        $('#serial_no').val('');
                        $("#from_location_id").prop("disabled", false );   
                        $("#to_location_id").prop("disabled", false );
                        
                        toastr.success('Transaction Successfully');
					}else if(result=='error'){
                        $('#serial_no_error').html(null);
                        toastr.error('Something went wrong');
                    }else{                        
                        toastr.error('Something went wrong');
                         
                    }

				},
			});
        }
</script>

<style>
    .custom_table {
      font-family: Arial, Helvetica, sans-serif;
      border-collapse: collapse;
      width: 100%;
    }
    
    .custom_table td, .custom_table th {
      border: 1px solid #ddd;
      padding: 2px;
    }
    .custom_table strong {
     padding-left: 5px;
    }
    
    .custom_table tr {background-color: #f2f2f2;}
    
    
    /* #customers tr:hover {background-color: #ddd;} */
    
    .custom_table th {
      padding-top: 12px;
      padding-bottom: 12px;
      text-align: center;
      background-color: #6b6b6b;
      color: white;
    }

    
.dp-table tbody td {
    padding-top: 1px !important; 
    padding-bottom: 1px !important;
    border: 1px solid #aaaaaa !important;
    vertical-align: middle !important;
    font-weight: 500;
}
.dp-table tbody th {
    padding-top: 3px;
    padding-bottom: 3px;
    border: 1px solid #aaaaaa;    
    vertical-align: middle !important;
}

.dp-table thead th {
    padding-top: 3px;
    padding-bottom: 3px;
    border: 1px solid #aaaaaa !important;  
    vertical-align: middle !important;
}

.dp-table tbody .balance th {
    padding: 8px;    
    
}
.dp-table tbody .bold-font {
    font-weight: bold;   
}

.dp-table .acc-type {
    background-color: rgb(232, 232, 232);
}

.dp-table .acc-category {
}
</style>
@endsection
