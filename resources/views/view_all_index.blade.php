@extends('include.app_layout')
@section('content')
  <div class="app-title">
    <div>
      <h1><i class="fa fa-th-list"></i> View ALL Index</h1>
      <p>View all index data</p>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="tile">
        <div class="table-responsive table-hover table-striped">
        <table class="table table-bordered" id="indexTable">
            <thead>
              <tr>
              <th>Symbol</th>
                @foreach ($dates as $date)
                    <th>{{ \Carbon\Carbon::parse($date)->format('d M') }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
                @foreach ($indexData as $row)
                    <tr>
                        <td>
                          <strong>{{ $row['index_symbol'] }}</small>
                        </td>

                        @foreach ($dates as $date)
                            @php
                                $value_last = $row[$date]['value_last'] ?? null;
                                $value_change = $row[$date]['value_change'] ?? null;
                                $value_p_change = $row[$date]['value_p_change'] ?? null;
                                $color = match (true) {
                                  $value_p_change === null => 'table-warning',
                                  $value_p_change > 0 => 'table-success',
                                  $value_p_change < 0 => 'table-danger',
                                  $value_p_change == 0 => 'table-info',
                              };
                            @endphp

                            <td class="{{$color}}">
                                <strong>{{ $value_last }}</strong>
                                <small>{{ $value_change }}</small><br/>
                                <small>({{ $value_p_change }}%)</small>
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
