@extends('layouts.app')
@section('title', 'Product Serial')
  
@section('content')
@include('productserial::layouts.nav')
<!-- Content Header (Page header) -->
{{-- <section class="content-header">
    <h1>Dashboard</h1>
</section> --}}
<section class="content-header content-header-custom">
    <h1>
        Product Serial Dashboard
    </h1>
</section>

<!-- Main content -->
<section class="content content-custom no-print">
 <br>  
<div class="row">
    <div class="col-md-4 col-xs-12">
      
    </div>
		<div class="col-md-8 col-xs-12">
			<div class="btn-group pull-right" data-toggle="buttons">
				<label class="btn btn-info active">
    				<input type="radio" name="date-filter"
    				data-start="{{ date('Y-m-d') }}" 
    				data-end="{{ date('Y-m-d') }}"
    				checked> {{ __('home.today') }}
  				</label>
            </div>
        </div>
</div>
    <br>
    
<div class="row row-custom">
    	<div class="col-md-3 col-sm-6 col-xs-12 col-custom">
	      <div class="info-box info-box-new-style">
	        <span class="info-box-icon bg-aqua"><i class="ion ion-cash"></i></span>

	        <div class="info-box-content">
	          <span class="info-box-text">Total</span>
	          <span class="info-box-number total_income"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
	        </div>
	        <!-- /.info-box-content -->
	      </div>
	      <!-- /.info-box -->
	    </div>
	    <!-- /.col -->
	    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
	      <div class="info-box info-box-new-style">
			<span class="info-box-icon bg-red"><i class="ion ion-ios-calculator"></i></span>
			 

	        <div class="info-box-content">
	          <span class="info-box-text">Total </span>
	          <span class="info-box-number total_expenses"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
	        </div>
	        <!-- /.info-box-content -->
	      </div>
	      <!-- /.info-box -->
	    </div>
	    <!-- /.col -->
	    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
	      <div class="info-box info-box-new-style">
	        <span class="info-box-icon bg-green">	        	 
				<i class="fa fa-heart"></i>
	        </span>

	        <div class="info-box-content">
	          <span class="info-box-text">Total </span>
	          <span class="info-box-number total_profit"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
	        </div>
			<!-- /.info-box-content -->
			 
	      </div>
	      <!-- /.info-box -->
	    </div>
	    
</div>

  <div class="row">
	<div class="col-sm-12">
        @component('components.widget', ['class' => 'box-primary','title'=>'RECENTS'])        
        <table class="table table-striped">            
             
        </table>
        @endcomponent
	  </div>
  </div>

  <div class="row">
	<div class="col-sm-12">
        @component('components.widget', ['class' => 'box-primary','title'=>'RECENT TRANSACTIONS'])        
        <table class="table table-striped">            
             
        </table>
        @endcomponent
	  </div>
  </div>





</section>
<!-- /.content -->

@stop
@section('javascript')
    {{-- @include('manufacturing::layouts.partials.common_script') --}}
<script>
	$(document).ready(function() {

	var start = $('input[name="date-filter"]:checked').data('start');
    var end = $('input[name="date-filter"]:checked').data('end');
    update_statistics(start, end);
    $(document).on('change', 'input[name="date-filter"], #dashboard_location', function() {
        var start = $('input[name="date-filter"]:checked').data('start');
        var end = $('input[name="date-filter"]:checked').data('end');
      //  update_statistics(start, end);
    });


	});

	function update_statistics(start, end) {
     
    var data = { start_date: start, end_date: end};
    //get purchase details
    var loader = '<i class="fas fa-sync fa-spin fa-fw margin-bottom"></i>';
    $('.total_income').html(loader);
    $('.total_expenses').html(loader);
    $('.total_profit').html(loader);
    
    $.ajax({
        method: 'POST',
        url: '/accounting/dashboard/getdata',
        dataType: 'json',
        data: data,
        success: function(data) {
            //income details
            $('.total_income').html(__currency_trans_from_en(data.toal_income, true));
            
			//Expenses details
            $('.total_expenses').html(__currency_trans_from_en(data.total_expenses, true));

            //Profit details
            $('.total_profit').html(__currency_trans_from_en(data.total_profit, true));
            
        },
    });
}
</script>
<style>
	.short-cut{
		background: #3595f6
	}
</style>
@endsection
