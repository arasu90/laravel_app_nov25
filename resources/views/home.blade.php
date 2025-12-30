@extends('include.app_layout')
@section('content')
<div class="app-title">
  <div>
    <h1><i class="fa fa-dashboard"></i> Dashboard</h1>
  </div>
  <ul class="app-breadcrumb breadcrumb">
    <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
    <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
  </ul>
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
    <div class="widget-small {{ optional($nifty50_index)->value_p_change > 0 ? 'primary' : 'danger' }} coloured-icon"><i class="icon fa fa-users fa-3x"></i>
      <div class="info">
        <h4>{{ optional($nifty50_index)->index_symbol }}</h4>
        <p><b>{{ optional($nifty50_index)->value_last }}</b> <span class="{{ optional($nifty50_index)->value_p_change > 0 ? 'text-primary' : 'text-danger' }}">{{ optional($nifty50_index)->value_change }} ({{ optional($nifty50_index)->value_p_change }}%)</span> </p>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="widget-small {{ optional($index_vix)->value_p_change > 0 ? 'primary' : 'danger' }} coloured-icon"><i class="icon fa fa-users fa-3x"></i>
      <div class="info">
        <h4>{{ optional($index_vix)->index_symbol }}</h4>
        <p><b>{{ optional($index_vix)->value_last }}</b> <span class="{{ optional($index_vix)->value_p_change > 0 ? 'text-success' : 'text-danger' }}">{{ optional($index_vix)->value_change }} ({{ optional($index_vix)->value_p_change }}%)</span> </p>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
    <div class="tile">
      <h3 class="tile-title">Top Gainer %</h3>
      <table class="table table-striped">
        <tbody>
          @foreach($topGainerPer as $stockList)
          <tr>
            <td>
              <span class="float-right">
                <span class="text-success float-right">{{ $stockList->last_price }} </span>
                <br />
                <span class="badge badge-success">{{ $stockList->change }} ({{ $stockList->p_change }} %) </span>
              </span>
              {{ $stockList->company_name }}
              <br />
              <small class="text-muted">{{ $stockList->symbol }}</small>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-md-6">
    <div class="tile">
      <h3 class="tile-title">Top Looser %</h3>
      <table class="table table-striped">
        <tbody>
          @foreach($topLooserPer as $stockList)
          <tr>
            <td>
              <span class="float-right">
                <span class="text-danger float-right">{{ $stockList->last_price }} </span>
                <br />
                <span class="badge badge-danger">{{ $stockList->change }} ({{ $stockList->p_change }} %) </span>
              </span>
              {{ $stockList->company_name }}
              <br />
              <small class="text-muted">{{ $stockList->symbol }}</small>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-md-6">
    <div class="tile">
      <h3 class="tile-title">Top Gainer</h3>
      <table class="table table-striped">
        <tbody>
          @foreach($topGainerChange as $stockList)
          <tr>
            <td>
              <span class="float-right">
                <span class="text-success float-right">{{ $stockList->last_price }} </span>
                <br />
                <span class="badge badge-success">{{ $stockList->change }} ({{ $stockList->p_change }} %) </span>
              </span>
              {{ $stockList->company_name }}
              <br />
              <small class="text-muted">{{ $stockList->symbol }}</small>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-md-6">
    <div class="tile">
      <h3 class="tile-title">Top Looser</h3>
      <table class="table table-striped">
        <tbody>
          @foreach($topLooserChange as $stockList)
          <tr>
            <td>
              <span class="float-right">
                <span class="text-danger float-right">{{ $stockList->last_price }} </span>
                <br />
                <span class="badge badge-danger">{{ $stockList->change }} ({{ $stockList->p_change }} %) </span>
              </span>
              {{ $stockList->company_name }}
              <br />
              <small class="text-muted">{{ $stockList->symbol }}</small>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-md-6">
    <div class="tile">
      <h3 class="tile-title">52 Week High</h3>
      <table class="table table-striped">
        <tbody>
          @foreach($Week52High as $stockList)
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
              <small class="text-muted">{{ $stockList->symbol }}</small>
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
          @foreach($Week52Low as $stockList)
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
                <small class="text-muted">{{ $stockList->symbol }}</small>
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