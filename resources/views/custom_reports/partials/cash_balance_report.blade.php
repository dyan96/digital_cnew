@can('account.access')
    <li class="{{ $request->segment(1) == 'pos' && $request->segment(2) == null ? 'active' : '' }}" ><a href="{{action('Custom\SettlemetController@dailyStmlIndex')}}"><i class="fa fa-list"></i>Cash Balance Report</a></li>
@endcan