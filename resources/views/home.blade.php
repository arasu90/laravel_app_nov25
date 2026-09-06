@extends('include.app_layout')
@section('content')
<div class="app-title">
  <div>
    <h1><i class="fa fa-dashboard"></i> Dashboard</h1>
  </div>
</div>
<div class="row">
  <div class="col-md-6 col-lg-3">
    <div class="widget-small info coloured-icon"><i class="icon fa fa-users fa-3x"></i>
      <div class="info">
        <h4>Total Stocks</h4>
        <p><b>{{ $totalStocks }}</b></p>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="widget-small {{ optional($nifty50Index)->value_p_change > 0 ? 'primary' : 'danger' }} coloured-icon"><i class="icon fa fa-users fa-3x"></i>
      <div class="info">
        <h4>{{ optional($nifty50Index)->index_symbol }}</h4>
        <p><b>{{ optional($nifty50Index)->value_last }}</b> <span class="{{ optional($nifty50Index)->value_p_change > 0 ? 'text-primary' : 'text-danger' }}">{{ optional($nifty50Index)->value_change }} ({{ optional($nifty50Index)->value_p_change }}%)</span> </p>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="widget-small {{ optional($indexVix)->value_p_change > 0 ? 'primary' : 'danger' }} coloured-icon"><i class="icon fa fa-users fa-3x"></i>
      <div class="info">
        <h4>{{ optional($indexVix)->index_symbol }}</h4>
        <p><b>{{ optional($indexVix)->value_last }}</b> <span class="{{ optional($indexVix)->value_p_change > 0 ? 'text-success' : 'text-danger' }}">{{ optional($indexVix)->value_change }} ({{ optional($indexVix)->value_p_change }}%)</span> </p>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <x-stock-panel
    title="Top Gainer %"
    collapseId="topGainerPer"
    :stocks="$topGainerPer"
    type="success"
  />
  
  <x-stock-panel
    title="Top Looser %"
    collapseId="topLooserPer"
    :stocks="$topLooserPer"
    type="danger"
  />

  <x-stock-panel
    title="Top Gainer"
    collapseId="topGainer"
    :stocks="$topGainerChange"
    type="success"
  />
  
  <x-stock-panel
    title="Top Looser"
    collapseId="topLooser"
    :stocks="$topLooserChange"
    type="danger"
  />

  <div class="col-md-6">
    <div class="tile">
      <h3 class="tile-title">52 Week High</h3>
      <table class="table table-striped">
        <tbody>
          @foreach($week52High as $stockList)
          @php
            $color_1 = match (true) {
                $stockList->p_change === null => 'text-warning',
                $stockList->p_change > 0 => 'text-success',
                $stockList->p_change < 0 => 'text-danger',
                $stockList->p_change == 0 => 'text-info',
            };

            $color_2 = match (true) {
                $stockList->p_change === null => 'badge-warning',
                $stockList->p_change > 0 => 'badge-success',
                $stockList->p_change < 0 => 'badge-danger',
                $stockList->p_change == 0 => 'badge-info',
            };

          @endphp
          <tr>
            <td>
              <span class="float-right">
                <span class="{{ $color_1 }} float-right">{{ $stockList->week_high_low_max }} </span>
                <br />
                <span class="badge {{ $color_2 }}">
                  {{ $stockList->week_high_low_max_date }} </span>
              </span>
              {{ $stockList->company_name }}
              <br />
              <small class="text-muted"><a target="_blank" href="stock-detail-view?stock_name={{ $stockList->symbol }}">{{ $stockList->symbol }}</a></small>
              <br />
              <span class="float-left">
                <span class="{{ $color_1 }} float-left">{{ $stockList->last_price }} </span>
                <br />
                <span class="badge {{ $color_2 }}">{{ $stockList->change }} ({{ $stockList->p_change }} %) </span>
              </span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-md-6">
    <div class="tile">
      <h3 class="tile-title">52 Week Low</h3>
      <table class="table table-striped">
        <tbody>
          @foreach($week52Low as $stockList)
            @php
              $color_1 = match (true) {
                  $stockList->p_change === null => 'text-warning',
                  $stockList->p_change > 0 => 'text-success',
                  $stockList->p_change < 0 => 'text-danger',
                  $stockList->p_change == 0 => 'text-info',
              };

              $color_2 = match (true) {
                  $stockList->p_change === null => 'badge-warning',
                  $stockList->p_change > 0 => 'badge-success',
                  $stockList->p_change < 0 => 'badge-danger',
                  $stockList->p_change == 0 => 'badge-info',
              };

            @endphp
            <tr>
              <td>
                <span class="float-right">
                  <span class="{{ $color_1 }} float-right">{{ $stockList->week_high_low_min }} </span>
                  <br />
                  <span class="badge {{ $color_2 }}">
                    {{ $stockList->week_high_low_min_date }} </span>
                </span>
                {{ $stockList->company_name }}
                <br />
                <small class="text-muted"><a target="_blank" href="stock-detail-view?stock_name={{ $stockList->symbol }}">{{ $stockList->symbol }}</a></small>
                <br />
                <span class="float-left">
                  <span class="{{ $color_1 }} float-left">{{ $stockList->last_price }} </span>
                  <br />
                  <span class="badge {{ $color_2 }}">{{ $stockList->change }} ({{ $stockList->p_change }} %) </span>
                </span>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
.collapse-icon { font-size: 1rem; text-decoration: none; }
</style>
@endpush

@push('scripts')
<script>
$(function(){
    // collapse panels by default on phones, expand on larger screens
    function adjustPanels(){
      console.log('adjusting panels');
      
        if (window.matchMedia('(max-width: 768px)').matches) {
            $('.tile .collapse').collapse('hide');
        } else {
            $('.tile .collapse').collapse('show');
        }
    }
    adjustPanels();
    $(window).on('resize', adjustPanels);

    // flip chevron in the right‑hand icon
    $('.tile .collapse').on('shown.bs.collapse', function(){
        $(this).prev('.tile').find('.collapse-icon i')
            .removeClass('fa-chevron-down').addClass('fa-chevron-up');
    });
    $('.tile .collapse').on('hidden.bs.collapse', function(){
        $(this).prev('.tile').find('.collapse-icon i')
            .removeClass('fa-chevron-up').addClass('fa-chevron-down');
    });
});
</script>
@endpush
