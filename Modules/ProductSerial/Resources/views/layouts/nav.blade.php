<section class="no-print">
    <nav class="navbar navbar-default bg-white m-4">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{action('\Modules\ProductSerial\Http\Controllers\ProductSerialController@index')}}"><i class="fas fa-barcode"></i> &nbsp;Product Serial</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling --> 
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    {{-- <li @if(request()->segment(1) == 'productserial' && request()->segment(2) == ''))  class="active" @endif><a href="{{action('\Modules\ProductSerial\Http\Controllers\ProductSerialController@index')}}"> <i class="fa fas fa-tachometer-alt"></i> &nbsp;Dashboard</a></li> --}}
                    <li @if(request()->segment(1) == 'productserial' && request()->segment(2) == ''))  class="active" @endif><a href="{{action('\Modules\ProductSerial\Http\Controllers\ProductSerialController@index')}}"> <i class="fa fas fa-barcode"></i> &nbsp;Serials</a></li>
                    <li @if(request()->segment(1) == 'productserial' && request()->segment(2) == 'add'))  class="active" @endif><a href="{{action('\Modules\ProductSerial\Http\Controllers\ProductSerialController@addNew')}}"> <i class="fa fas fa-plus"></i> &nbsp;Add Serials</a></li>
                    <li @if(request()->segment(1) == 'productserial' && request()->segment(2) == 'transfers'))  class="active" @endif><a href="{{action('\Modules\ProductSerial\Http\Controllers\ProductSerialController@transfer')}}"> <i class="fa fas fa-truck"></i> &nbsp;Serial Transfers</a></li>
                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>