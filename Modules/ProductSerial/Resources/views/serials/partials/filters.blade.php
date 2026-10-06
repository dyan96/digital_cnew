 

 
<div class="col-md-8">
    <div id="product_select" class="form-group">
        {!! Form::label('product_id',  'PRODUCT :') !!}
        {!! Form::select('product_id', [], null, ['id'=>'product_id','class' => 'form-control select2', 'style' => 'width:100%']); !!}        
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        {!! Form::label('location',  'Location') !!}
        {!! Form::select('location_id', $business_locations, null, ['id'=>'location_id','class' => 'form-control select2', 'style' => 'width:100%']); !!}
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('filter_date_range', 'ISSEUD DATE RANGE' . ':') !!}
        {!! Form::text('filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('status', 'Status'. ':') !!}
        {!! Form::select('status', ['0'=>'NOT ISSUED','1' => 'ISSUED'], null, ['id'=>'status','class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
    </div>
</div>


<div class="col-md-3">
    <div class="form-group" style="padding-top: 24px;">        
        <button class="btn btn-default" onclick="resetProductSelected()" >All Product</button>
    </div>
</div>

{{-- 
@if(empty($only) || in_array('only_subscriptions', $only))
<div class="col-md-3">
    <div class="form-group">
        <div class="checkbox">
            <label>
                <br>
              {!! Form::checkbox('only_subscriptions', 1, false, 
              [ 'class' => 'input-icheck', 'id' => 'only_subscriptions']); !!} {{ __('lang_v1.subscriptions') }}
            </label>
        </div>
    </div>
</div>
@endif --}}