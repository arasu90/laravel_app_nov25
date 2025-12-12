@extends('include.app_layout')
@section('content')
  <div class="app-title">
    <div>
      <h1><i class="fa fa-th-list"></i> Basic Tables</h1>
      <p>Basic bootstrap tables</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
      <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
      <li class="breadcrumb-item">Tables</li>
      <li class="breadcrumb-item active"><a href="#">Simple Tables</a></li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="tile">
        <h3 class="tile-title">Stock List</h3>
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
                                <br />
                                <small>{{ $value_change }} ({{ $value_p_change }}%)</small>
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