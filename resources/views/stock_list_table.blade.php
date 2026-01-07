@extends('include.app_layout')
@section('content')
  <div class="app-title">
    <div>
      <h1><i class="fa fa-th-list"></i> Stock List</h1>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="tile">
        <div class="table-responsive table-hover table-striped">
        <table class="table table-bordered" id="sampleTable">
            <thead>
              <tr>
              <th>Symbol</th>
                @foreach ($dates as $date)
                    <th>{{ \Carbon\Carbon::parse($date)->format('d M') }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
                @foreach ($result as $row)
                    <tr>
                        <td>
                          <strong><a target="_blank" href="stock-detail-view?stock_name={{ $row['symbol'] }}">{{ $row['symbol'] }}</a></strong>
                          <br>
                          <small>{{ $row['company_name'] }}</small>
                        </td>

                        @foreach ($dates as $date)
                            @php
                                $last_price = $row[$date]['last_price'] ?? null;
                                $change = $row[$date]['change'] ?? null;
                                $p_change = $row[$date]['p_change'] ?? null;
                                $color = match (true) {
                                  $p_change === null => 'table-warning',
                                  $p_change > 0 => 'table-success',
                                  $p_change < 0 => 'table-danger',
                                  $p_change == 0 => 'table-info',
                              };
                            @endphp

                            <td class="{{$color}}">
                                <strong>{{ $last_price }}</strong><br/>
                                <small>{{ $change }}</small><br/>
                                <small>({{ $p_change }}%)</small>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
          </table>
        </div></div>
    </div>
  </div>
@endsection
