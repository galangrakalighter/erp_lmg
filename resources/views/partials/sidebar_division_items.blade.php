@if($isSuperAdmin)
    @include('partials.sidebar_idme')
@endif

@if($isSuperAdmin || in_array(1, $myDivisionIds, true))
    @include('partials.sidebar_digital_marketing')
@endif

@if($isSuperAdmin || in_array((int) $multimediaDivisionId, $myDivisionIds, true))
    @include('partials.sidebar_multimedia')
@endif

@if($isSuperAdmin || in_array(3, $myDivisionIds, true))
    @include('partials.sidebar_mcn')
@endif

@if($isSuperAdmin || in_array(5, $myDivisionIds, true))
    @include('partials.sidebar_tech')
@endif

@if($isSuperAdmin || in_array(7, $myDivisionIds, true))
    @include('partials.sidebar_commerce')
@endif
