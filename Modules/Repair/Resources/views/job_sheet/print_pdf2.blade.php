<link rel="stylesheet" href="{{ asset('css/app.css?v='.$asset_v) }}">
<style type="text/css">
	.box {
		border: 1px solid;
	}
	.table-pdf {
		width: 100%;
	}

	.table-pdf td, .table-pdf th {
		padding: 6px;
		text-align: left;
	}
	.w-20 {
		width: 20%;
		float: left;
	}
	.checklist {
		padding: 5px 15px;
		width: 100%;
	}
	.checkbox {
		width: 20%; 
		float: left;
	}
	.checkbox-text {
		width: 80%;
		float: left;
	} 
	.content-div {
		padding: 4px;
	} 
	.table-slim{
		width: 100%;
	}

	.table-slim td, .table-slim th {
		padding: 1px !important;
		font-size: 12px;
	}
	.font-18 {
		font-size: 18px;
	}
    .font-20 {
		font-size: 25px;
	}
	.font-14 {
		font-size: 14px;
	}
	body {
		font-size: 14px;
	}
</style>
<style>
    .header-table {
        width: 100%;
        
    }
    .bottom-table {
        width: 100%;
        margin: 0px;
    }
    .body-content {
        height: 100px;
       
    }
    
    @page {
        size: 9.5in 5.5in;
        margin-top: 10px;
        margin-left: 50px;
        margin-right: 10px;
        margin-bottom: 1px;
    }

</style>
<table class="header-table">
    <tbody>
        <tr>
            <th colspan="2" align="left">
                <strong class="font-18">
                    {{$job_sheet->customer->business->name}}
                </strong>
            </th>
            <th colspan="2" align="right">
                <strong class="font-20">
                    JOB NOTE
                </strong>
            </th>
        </tr>
        <tr>
            <td colspan="2" align="left">
                <span class="font-14">
                    <!--{!!$job_sheet->customer->business->business_address!!}-->
                    NO 12, INGIRIYA RD,PADUKKA. TEL : 075 585 9341
                    
                </span>
            </td>
            <td colspan="2" align="right" >
               <b class="font-18"> {{$job_sheet->job_sheet_no}}</b>
            </td>
        </tr>    
        <tr>
            <td colspan="4"><hr style="margin: 0px;">   </td>
        </tr>  
        <tr>
            <td width="12%"><b>Customer :</b> </td>
            <td width="53%" align="left">{{$job_sheet->customer->name}} - {{$job_sheet->customer->mobile}}</td>
            <td width="15%"><b>Date :</b> </td>
            <td width="20%" align="right">{{@format_datetime($job_sheet->created_at)}}</td>
        </tr>
        <tr>
            <td width="12%"><b>Device :</b> </td>
            <td width="53%" align="left">{{optional($job_sheet->brand)->name}} - {{optional($job_sheet->deviceModel)->name}} - {{optional($job_sheet->device)->name}} - </td>
            <td width="15%"><b>Delivery Date :</b> </td>
            <td width="20%" align="right">{{@format_datetime($job_sheet->delivery_date)}}</td>
        </tr>
        <tr>
            <td width="10%"><b> IMEI/SR :</b> </td>
            <td width="55%" align="left">{{$job_sheet->serial_no}}</td>
            <td width="15%"><b>Status :</b> </td>
            <td width="20%" align="right">{{optional($job_sheet->status)->name}}</td>
        </tr>
        {{--<tr>
            <td colspan="4"><hr style="margin: 0px;">   </td>
        </tr>  --}}
    </tbody>
</table>


{{-- <div class="box mb-10"> 
<table class="table-pdf">
	<tr>
		<td style="vertical-align: top;">
			<table class="width-100">
				<tr>
					<th style="padding-left: 0;">@lang('role.customer'):</th>
				</tr>
				<tr>
					<td style="padding-left: 0;">
						<p>
							{{$job_sheet->customer->name}} <br>
							{!! $job_sheet->customer->contact_address !!}
							@if(!empty($contact->email))
								<br>@lang('business.email'):
								{{$job_sheet->customer->email}}
							@endif
							<br>@lang('contact.mobile'):
							{{$job_sheet->customer->mobile}}
							@if(!empty($contact->tax_number))
								<br>@lang('contact.tax_no'):
								{{$job_sheet->customer->tax_number}}
							@endif
						</p>
					</td>
				</tr>
			</table>
		</td>
		<td colspan="2" style="vertical-align: top;">
			<table class="width-100">
				<tr>
					<th>@lang('product.brand'):</th>
					<td>{{optional($job_sheet->brand)->name}}</td>
					<th>@lang('repair::lang.device'):</th>
					<td>{{optional($job_sheet->device)->name}}</td>
				</tr>
				<tr>
					<th>@lang('repair::lang.device_model'):</th>
					<td>{{optional($job_sheet->deviceModel)->name}}</td>
					<th>@lang('lang_v1.password'):</th>
					<td>{{$job_sheet->security_pwd}}</td>
				</tr>
				<tr>
					<th>@lang('repair::lang.serial_no'):</th>
					<td colspan="2">{{$job_sheet->serial_no}}</td>
				</tr>
				<tr>
					<th>@lang('repair::lang.security_pattern_code'):</th>
					<td colspan="2">{{$job_sheet->security_pattern}}</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<strong>@lang('sale.invoice_no'):</strong>
			@if($job_sheet->invoices->count() > 0)
				@foreach($job_sheet->invoices as $invoice)
					{{$invoice->invoice_no}}
					@if (!$loop->last)
				        {{', '}}
				    @endif
				@endforeach
			@endif
		</td>
		<td>
			<strong>@lang('repair::lang.estimated_cost'):</strong>
			<span class="display_currency" data-currency_symbol="true">
				@format_currency($job_sheet->estimated_cost)
			</span>
		</td>
		<td>
			<strong>
				@lang('sale.status'):
			</strong>
			{{optional($job_sheet->status)->name}}
		</td>
	</tr>
</table>
</div> --}}

<div class="box mb-10 body-content">
    <div class="width-100  content-div">
        <!--<div class="width-100">-->
            
        <!--</div>-->
        <strong>@lang('repair::lang.pre_repair_checklist'):</strong>
        @php
            $checklists = [];
            if (!empty($job_sheet->deviceModel) && !empty($job_sheet->deviceModel->repair_checklist)) {
                $checklists = explode('|', $job_sheet->deviceModel->repair_checklist);
            }
        @endphp
        @if(!empty($job_sheet->checklist))
            <!--<div class="width-100">-->
            @foreach($checklists as $check)
                 
                
                    @if($job_sheet->checklist[$check] == 'yes')
                         {{$check}},
                    @endif
                    
                 
                
            @endforeach
            <!--</div>-->
        @endif
    </div>
    <div class="width-100 content-div">
        <strong>@lang('repair::lang.product_configuration'):</strong>
        @php
            $product_configuration = json_decode($job_sheet->product_configuration, true);
        @endphp
        @if(!empty($product_configuration))
            @foreach($product_configuration as $product_conf)
                {{$product_conf['value']}}
                @if(!$loop->last)
                    {{','}}
                @endif
            @endforeach
        @endif
    </div>
    <div class="width-100 content-div">
        <strong>@lang('repair::lang.condition_of_product'):</strong>
        @php
            $product_condition = json_decode($job_sheet->product_condition, true);
        @endphp
        @if(!empty($product_condition))
            @foreach($product_condition as $product_cond)
                {{$product_cond['value']}}
                @if(!$loop->last)
                    {{','}}
                @endif
            @endforeach
        @endif
    </div>
    <div class="width-100 content-div">
        <strong>@lang('repair::lang.problem_reported_by_customer'):</strong>
        @php
            $defects = json_decode($job_sheet->defects, true);
        @endphp
        @if(!empty($defects))
            @foreach($defects as $product_defect)
                {{$product_defect['value']}}
                @if(!$loop->last)
                    {{','}}
                @endif
            @endforeach
        @endif
    </div>
    <div class="width-100 content-div">
        @if(!empty($job_sheet->custom_field_1))
        <div class="width-50 f-left mb-5">
            <strong>{{$repair_settings['job_sheet_custom_field_1'] ?? __('lang_v1.custom_field', ['number' => 1])}}:</strong> 
        {{$job_sheet->custom_field_1}}
        </div>
        @endif
        @if(!empty($job_sheet->custom_field_2))
        <div class="width-50 f-left mb-5">
                <strong>{{$repair_settings['job_sheet_custom_field_2'] ?? __('lang_v1.custom_field', ['number' => 2])}}:</strong> 
                {{$job_sheet->custom_field_2}}
        </div>
        @endif
        @if(!empty($job_sheet->custom_field_3))
        <div class="width-50 f-left">
            <strong>{{$repair_settings['job_sheet_custom_field_3'] ?? __('lang_v1.custom_field', ['number' => 3])}}:</strong> 
            {{$job_sheet->custom_field_3}}
        </div>
        @endif
        @if(!empty($job_sheet->custom_field_4))
        <div class="width-50 f-left mb-5">
            <strong>{{$repair_settings['job_sheet_custom_field_4'] ?? __('lang_v1.custom_field', ['number' => 4])}}:</strong> 
            {{$job_sheet->custom_field_4}}
        </div>
        @endif
        @if(!empty($job_sheet->custom_field_5))
        <div class="width-50 f-left mb-5">
            <strong>{{$repair_settings['job_sheet_custom_field_5'] ?? __('lang_v1.custom_field', ['number' => 5])}}:</strong> 
            {{$job_sheet->custom_field_5}}
        </div>
        @endif
    </div>
</div>
<table class="bottom-table">
    <tbody>
        <tr style="margin: 0px;">
            <th align="left"><strong>සැළකිය යුතුයි.</strong></th>
            <th align="right">Estimated Amount </b></th>
            <td align="right">@format_currency($job_sheet->estimated_cost)</td>
        </tr>
    </tbody>
</table>

<div style="margin:0px;" class="width-100 content-div">
	@if(!empty($repair_settings['repair_tc_condition']))
		{!!$repair_settings['repair_tc_condition']!!}
	@endif
</div>
<table style="margin:0px; padding:0px;" class="table-pdf">
	<tr>
		<th>
			@lang('repair::lang.customer_signature'):
		</th>
		<th>@lang('repair::lang.authorized_signature'):</th>
		<td><strong>@lang('repair::lang.technician'):</strong> {{optional($job_sheet->technician)->user_full_name}}</td>
	</tr>
</table>
 