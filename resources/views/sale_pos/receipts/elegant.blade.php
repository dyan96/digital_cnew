<table style="width:100%">
	<thead>
    {{--	<tr>
			<td>
				<p class="text-right">
					<small class="text-muted-imp">
						@if(!empty($receipt_details->invoice_no_prefix))
							{!! $receipt_details->invoice_no_prefix !!}
						@endif

						{{$receipt_details->invoice_no}}
					</small>
				</p>
			</td>
		</tr> --}}
<tr>
		    
    <td>
		        <!-- business information here -->
    <div class="row invoice-info mt-0 mb-0 padding-0">

		
    	<div class="col-md-8 invoice-col width-c70">
    		
    		<!-- Logo -->
    		@if(!empty($receipt_details->logo))
    			<img src="{{$receipt_details->logo}}" class="img center-block">
    			<br/>
    		@endif
    
    		<!-- Shop & Location Name  -->
    		@if(!empty($receipt_details->display_name))
    			<span style="color:black !important; font-weight: 600;">
    			    <span class="pull-left font-23" style="font-weight: 1000;">
    				{{$receipt_details->display_name}}
    				</span>
    				<br>
    				@if(!empty($receipt_details->address))
    					<br/> {!! $receipt_details->address !!} 
    				@endif
    
    				@if(!empty($receipt_details->contact))
    					<br/>{{ $receipt_details->contact }}
    				@endif
    
    				@if(!empty($receipt_details->website))
    					<br/>{{ $receipt_details->website }}
    				@endif
    
    				@if(!empty($receipt_details->tax_info1))
    					<br/>{{ $receipt_details->tax_label1 }} {{ $receipt_details->tax_info1 }}
    				@endif
    
    				@if(!empty($receipt_details->tax_info2))
    					<br/>{{ $receipt_details->tax_label2 }} {{ $receipt_details->tax_info2 }}
    				@endif
    
    				@if(!empty($receipt_details->location_custom_fields))
    					<br/>{{ $receipt_details->location_custom_fields }}
    				@endif
    			</span>
    		@endif
    				
    		<!-- Table information-->
            @if(!empty($receipt_details->table_label) || !empty($receipt_details->table))
            	<p>
    				@if(!empty($receipt_details->table_label))
    					{!! $receipt_details->table_label !!}
    				@endif
    				{{$receipt_details->table}}
    			</p>
            @endif
    
    		<!-- Waiter info -->
    		@if(!empty($receipt_details->service_staff_label) || !empty($receipt_details->service_staff))
            	<p>
    				@if(!empty($receipt_details->service_staff_label))
    					{!! $receipt_details->service_staff_label !!}
    				@endif
    				{{$receipt_details->service_staff}}
    			</p>
            @endif
    
    
    
            <div class="word-wrap">
    
    			<p class="text-right  ">
    			@if(!empty($receipt_details->serial_no_label) || !empty($receipt_details->repair_serial_no))
    				@if(!empty($receipt_details->serial_no_label))
    					<span class="pull-left">
    						<strong>{!! $receipt_details->serial_no_label !!}</strong>
    					</span>
    				@endif
    				{{$receipt_details->repair_serial_no}}<br>
    	        @endif
    			@if(!empty($receipt_details->repair_status_label) || !empty($receipt_details->repair_status))
    				@if(!empty($receipt_details->repair_status_label))
    					<span class="pull-left">
    						<strong>{!! $receipt_details->repair_status_label !!}</strong>
    					</span>
    				@endif
    				{{$receipt_details->repair_status}}<br>
    	        @endif
    	        
    	        @if(!empty($receipt_details->repair_warranty_label) || !empty($receipt_details->repair_warranty))
    				@if(!empty($receipt_details->repair_warranty_label))
    					<span class="pull-left">
    						<strong>{!! $receipt_details->repair_warranty_label !!}</strong>
    					</span>
    				@endif
    				{{$receipt_details->repair_warranty}}
    				<br>
    	        @endif
    	        </p>
			</div>
			
    	{{-- @if(!empty($receipt_details->defects_label) || !empty($receipt_details->repair_defects))
    		<div class="col-xs-12">
    			<p class=" ">
    				<br>
    				@if(!empty($receipt_details->defects_label))
    					<strong>{!! $receipt_details->defects_label !!}</strong> 
    				@endif
    				{{$receipt_details->repair_defects}}
    			</p>
    		</div>
        @endif --}}
        
        <div class="word-wrap" style="color:black !important; font-weight: 500;">
    			@if(!empty($receipt_details->customer_label))
    				<b>{{ $receipt_details->customer_label }}</b> <br/>
    			@endif    
				<!-- customer info -->
				
    			@if(!empty($receipt_details->customer_name))
    			 	{{ $receipt_details->customer_name }}
    			@endif
    			
    			@if(!empty($receipt_details->customer_info))
    			 -	{!! $receipt_details->customer_info !!}
				@endif
				
    			
    			@if(!empty($receipt_details->client_id_label))
    				<!--<br/>-->
    				<strong>{{ $receipt_details->client_id_label }}</strong> {{ $receipt_details->client_id }}
    			@endif
    			@if(!empty($receipt_details->customer_tax_label))
    				<br/>
    				<strong>{{ $receipt_details->customer_tax_label }}</strong> {{ $receipt_details->customer_tax_number }}
    			@endif
    			@if(!empty($receipt_details->customer_custom_fields))
    				/ {!! $receipt_details->customer_custom_fields !!}
    			@endif
    			
    			@if($receipt_details->additional_notes!=null)
    			    <br> 
    			    <b>Note :</b> {{$receipt_details->additional_notes}}
    			@endif
    			
    		{{--	@if(!empty($receipt_details->sales_person_label))
    				<br/>
    				<strong>{{ $receipt_details->sales_person_label }}</strong> {{ $receipt_details->commision_agent }}
    			@endif --}}
    
    			@if(!empty($receipt_details->customer_rp_label))
    				<br/>
    				<strong>{{ $receipt_details->customer_rp_label }}</strong> {{ $receipt_details->customer_total_rp }}
    			@endif
    
    			<!-- Display type of service details -->
    			@if(!empty($receipt_details->types_of_service))
    				<span class="pull-left text-left">
    					<strong>{!! $receipt_details->types_of_service_label !!}:</strong>
    					{{$receipt_details->types_of_service}}
    					<!-- Waiter info -->
    					@if(!empty($receipt_details->types_of_service_custom_fields))
    						<br>
    						@foreach($receipt_details->types_of_service_custom_fields as $key => $value)
    							<strong>{{$key}}: </strong> {{$value}}@if(!$loop->last), @endif
    						@endforeach
    					@endif
    				</span>
    			@endif
  		</div>
    
 </div>
    	
 	<div class="col-md-4 invoice-col width-c30">
    	    
    	    @if(!empty($receipt_details->invoice_heading))
    			<div class="text-right font-23" style="padding-top:0px; margin-top-0px;">
        	        <h1 style="font-weight: 1000; padding-top:0px; margin-top-0px;">{!! $receipt_details->invoice_heading !!}</h1>
        	    </div>
    		@endif
    
    		<div class="text-right font-14" style="padding-top:0px; margin-top-0px;">
    			@if(!empty($receipt_details->invoice_no_prefix))
    				<span class="pull-left">{!! $receipt_details->invoice_no_prefix !!}</span>
    			@endif
    
    			{{$receipt_details->invoice_no}}
    		</div>
	
				<!-- Date-->
			@if(!empty($receipt_details->date_label))
    			<div class="text-right font-14 ">
    				<span class="pull-left">
    					{{$receipt_details->date_label}}
    				</span>
    
    				{{$receipt_details->invoice_date}}
    			</div>
			@endif

			@if(!empty($receipt_details->sales_person_label))
            <div class="text-right font-14">
    				<span class="pull-left">
    					{{$receipt_details->sales_person_label}}
    				</span>
    
    				{{$receipt_details->commision_agent ?? ''}}
    		</div>
    		@endif
			

    		<!-- Total Due-->
    		@if(!empty($receipt_details->total_due))
    			<div class="text-right font-17 padding-0">
    				<span class="pull-left">
    					{!! $receipt_details->total_due_label !!}
    				</span>
    
    				{{$receipt_details->total_due}}
    			</div>
    		@endif
    
    		@if(!empty($receipt_details->all_due))
    			<div class="text-right font-17 padding-0">
    				<span class="pull-left">
    					{!! $receipt_details->all_bal_label !!}
    				</span>
    
    				{{$receipt_details->all_due}}
    			</div>
    		@endif
    		
    		<!-- Total Paid-->
    		@if(!empty($receipt_details->total_paid))
    			<div class="text-right font-17  ">
    				<span class="pull-left">{!! $receipt_details->total_paid_label !!}</span>
    				{{$receipt_details->total_paid}}
    			</div>
    		@endif
    		
    	    @if(!empty($receipt_details->due_date_label))
    			<div class="text-right font-14">
    				<span class="pull-left">
    					{{$receipt_details->due_date_label}}
    				</span>
    
    				{{$receipt_details->due_date ?? ''}}
    				<br>
    			</div>
    		@endif
            
        	
    		
    
   </div>    	
</div>
<div class="row mt-0 mb-0" style="padding-bottom:0px; font-14">
	@includeIf('sale_pos.receipts.partial.common_repair_invoice')
</div>
</td>
		    
</tr>
	</thead>
	<tbody>
		<tr class="mt-0">
			<td class="mt-0">

<div class="row" style="padding-bottom:0px;">
	<div class="col-xs-12">
		<table  class="table table-bordered-nw table-sm print">
			<thead>
				<tr style="font-size: 15px !important; font-weight: 800; color:black;" class="text-center border-print " >
					<td style="width: 5% !important; padding-top: 0px;padding-bottom: 0px;">#</td>
					
					@php
						$p_width = 35;
					@endphp
					@if($receipt_details->show_cat_code != 1)
						@php
							$p_width = 45;
						@endphp
					@endif
					<td style="width: {{$p_width}}% !important; padding-top: 0px;padding-bottom: 0px;">
						{{$receipt_details->table_product_label}}
					</td>

				{{--	@if($receipt_details->show_cat_code == 1)
						<td style="width: 10% !important; ">{{$receipt_details->cat_code_label}}</td>
					@endif --}}
					
					<td style=" width: 15% !important; padding-top: 0px;padding-bottom: 0px;">
						{{$receipt_details->table_qty_label}}
					</td>
					<td style="width: 15% !important; padding-top: 0px;padding-bottom: 0px;">
						{{$receipt_details->table_unit_price_label}}
					</td>
					<td style="width: 15% !important; padding-top: 0px;padding-bottom: 0px;">
					    DISC
					</td>
					<td style="width: 15% !important; padding-top: 0px;padding-bottom: 0px;">
					    U.P-D
					</td>
					<td style="width: 20% !important; padding-top: 0px;padding-bottom: 0px;">
						{{$receipt_details->table_subtotal_label}}
					</td>
				</tr>
			</thead>
			<tbody style="padding-bottom:0px;">
				@foreach($receipt_details->lines as $line)
					<tr style="font-weight: 500; color:black;" class="m-0 padding-0">
						<td class="text-center padding-0" style="padding-top: 0px;padding-bottom: 0px;">
							{{$loop->iteration}}
						</td>
						<td style="word-break: break-all; padding-top: 0px;padding-bottom: 0px;">
							@if(!empty($line['image']))
								<img src="{{$line['image']}}" alt="Image" width="50" style="float: left; margin-right: 8px;">
							@endif
                            {{$line['name']}} {{$line['product_variation']}} {{$line['variation']}} 
                            @if(!empty($line['sub_sku'])), {{$line['sub_sku']}} @endif @if(!empty($line['brand'])), {{$line['brand']}} @endif
                            @if(!empty($line['product_custom_fields'])), {{$line['product_custom_fields']}} @endif
                            @if(!empty($line['sell_line_note']))({{$line['sell_line_note']}}) @endif
                            @if(!empty($line['lot_number']))<br> {{$line['lot_number_label']}}:  {{$line['lot_number']}} @endif 
                            @if(!empty($line['product_expiry'])), {{$line['product_expiry_label']}}:  {{$line['product_expiry']}} @endif 

                            @if(!empty($line['warranty_name'])) <br><small>{{$line['warranty_name']}} </small>@endif @if(!empty($line['warranty_exp_date'])) <small>- {{@format_date($line['warranty_exp_date'])}} </small>@endif
                            @if(!empty($line['warranty_description'])) <small> {{$line['warranty_description'] ?? ''}}</small>@endif
                        </td>

					{{--	@if($receipt_details->show_cat_code == 1)
	                        <td>
	                        	@if(!empty($line['cat_code']))
	                        		{{$line['cat_code']}}
	                        	@endif
	                        </td>
	                    @endif --}}

						<td class="text-right" style="padding-top: 0px;padding-bottom: 0px;">
							{{$line['quantity']}} {{$line['units']}}
						</td>
						<td class="text-right" style="padding-top: 0px;padding-bottom: 0px;">
							{{$line['unit_price_before_discount']}}
						</td>
						<td class="text-right" style="padding-top: 0px;padding-bottom: 0px;">
							{{$line['line_discount']}}
						</td>
						<td class="text-right" style="padding-top: 0px;padding-bottom: 0px;">
							{{$line['unit_price_inc_tax']}}
						</td>
						<td class="text-right" style="padding-top: 0px;padding-bottom: 0px;">
							{{$line['line_total']}}
						</td>
					</tr>
					@if(!empty($line['modifiers']))
						@foreach($line['modifiers'] as $modifier)
							<tr>
								<td class="text-center">
									&nbsp;
								</td>
								<td>
		                            {{$modifier['name']}} {{$modifier['variation']}} 
		                            @if(!empty($modifier['sub_sku'])), {{$modifier['sub_sku']}} @endif 
		                            @if(!empty($modifier['sell_line_note']))({{$modifier['sell_line_note']}}) @endif 
		                        </td>

								@if($receipt_details->show_cat_code == 1)
			                        <td>
			                        	@if(!empty($modifier['cat_code']))
			                        		{{$modifier['cat_code']}}
			                        	@endif
			                        </td>
			                    @endif

								<td class="text-right">
									{{$modifier['quantity']}} {{$modifier['units']}}
								</td>
								<td class="text-right">
									{{$modifier['unit_price_exc_tax']}}
								</td>
								<td class="text-right">
									{{$modifier['line_total']}}
								</td>
							</tr>
						@endforeach
					@endif
				
				
					
				@endforeach

				@php
					$lines = count($receipt_details->lines);
				@endphp
			</tbody>
		</table>
	</div>
</div>

<hr style="border-top: 1px solid black">

<div class="row invoice-info" style="page-break-inside: avoid !important;">
	{{-- <div class="col-md-6 invoice-col width-50">
		<table class="table table-condensed">
			@if(!empty($receipt_details->payments))
				@foreach($receipt_details->payments as $payment)
					<tr>
						<td>{{$payment['method']}}</td>
						<td>{{$payment['amount']}}</td>
						<td>{{$payment['date']}}</td>
					</tr>
				@endforeach
			@endif
		</table>
		<b class="pull-left">@lang('lang_v1.authorized_signatory')</b>
	</div> --}}
	
	<div class="col-md-6 invoice-col width-50">
	    @if(!empty($receipt_details->footer_text))
    	<div class="row  ">
    		<div class="col-xs-12">
    			{!! $receipt_details->footer_text !!}
    		</div>
    	</div>
        @endif
        
      {{--  <div class="row  ">
        	<div class="col-xs-6">
        		<b>{{$receipt_details->additional_notes}}</b>
        	</div> 
        </div>--}}
        
    </div>

	<div class="col-md-6 invoice-col width-50">
		<table class="table-no-side-cell-border table-no-top-cell-border width-100">
			<tbody>
				<tr class=" ">
					<td style="width:50%">
						{!! $receipt_details->subtotal_label !!}
					</td>
					<td class="text-right">
						{{$receipt_details->subtotal}}
					</td>
				</tr>
				
				<!-- Shipping Charges -->
				@if(!empty($receipt_details->shipping_charges))
					<tr class=" ">
						<td style="width:50%">
							{!! $receipt_details->shipping_charges_label !!}
						</td>
						<td class="text-right">
							{{$receipt_details->shipping_charges}}
						</td>
					</tr>
				@endif

				<!-- Tax -->
				@if(!empty($receipt_details->taxes))
					@foreach($receipt_details->taxes as $k => $v)
						<tr class=" ">
							<td>{{$k}}</td>
							<td class="text-right">(+) {{$v}}</td>
						</tr>
					@endforeach
				@endif

				<!-- Discount -->
				@if( !empty($receipt_details->discount) )
					<tr class=" ">
						<td>
							{!! $receipt_details->discount_label !!}
						</td>

						<td class="text-right">
							(-) {{$receipt_details->discount}}
						</td>
					</tr>
				@endif

				@if( !empty($receipt_details->reward_point_label) )
					<tr class=" ">
						<td>
							{!! $receipt_details->reward_point_label !!}
						</td>

						<td class="text-right">
							(-) {{$receipt_details->reward_point_amount}}
						</td>
					</tr>
				@endif

				@if(!empty($receipt_details->group_tax_details))
					@foreach($receipt_details->group_tax_details as $key => $value)
						<tr class=" ">
							<td>
								{!! $key !!}
							</td>
							<td class="text-right">
								(+) {{$value}}
							</td>
						</tr>
					@endforeach
				@else
					@if( !empty($receipt_details->tax) )
						<tr class=" ">
							<td>
								{!! $receipt_details->tax_label !!}
							</td>
							<td class="text-right">
								(+) {{$receipt_details->tax}}
							</td>
						</tr>
					@endif
				@endif
				
				<!-- Total -->
				<tr>
					<th style="" class="font-232 padding-0">
						<b>{!! $receipt_details->total_label !!}</b>
					</th>
					<td class="text-right font-232 padding-0" style="">
						<b>{{$receipt_details->total}}</b>
					</td>
				</tr>
				@if(!empty($receipt_details->payments))
        			@foreach($receipt_details->payments as $payment)
        					<tr>
        						<td class="font-232 padding-0"><i>{{$payment['method']}} - {{$payment['date']}}</i></td>
        						<td class="font-232 padding-0 text-right"><i>{{$payment['amount']}}</i></td>
        					</tr>
        			@endforeach
        		@endif
			</tbody>
        </table>
	</div>
</div>

<!--<hr style="border-top: 1px solid black">-->

<div class="col-md-12 padding-0">
    <table class="table-no-side-cell-border table-no-top-cell-border width-100">
        <tbody>
            <tr class="padding-0">
                <td class="text-center">-----------------------</td>
                <td class="text-center">-----------------------</td>
                <td class="text-center">-----------------------</td>
            </tr>
             <tr class="padding-0">
                <td class="text-center">Prepared By</td>
                <td class="text-center">Checked BY</td>
                <td class="text-center">Customer Signature</td>
			</tr>
			<tr>
				<td colspan="3" class="text-left" style="font-size: 10px;">Software by XCELEN IT @ 070 335 3000</td>
			</tr>
        </tbody>
    </table>
</div>

{{-- Barcode --}}
@if($receipt_details->show_barcode)
<!--<br>-->
<!--<div class="row">-->
<!--		<div class="col-xs-12">-->
<!--			<img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2,30,array(39, 48, 54), true)}}">-->
<!--		</div>-->
<!--</div>-->
@endif



			</td>
		</tr>
	</tbody>
</table>

<style>
/*    table.table-bordered-nw .print > thead > tr > th{*/
/*  border:2px solid black !important;*/
/*}*/

/*@page {*/
/*   size: 218mm 127mm;*/
/*   margin: 27mm 16mm 27mm 16mm;*/
/*}*/


.font-14{
    font-size: 14px !important;
}

.width-c70 {
	width: 70% !important;
}

.width-c30 {
	width: 30% !important;
}

.border-print td {
    border-top:1px solid black !important;
    border-bottom:1px solid black !important;
}
</style>


