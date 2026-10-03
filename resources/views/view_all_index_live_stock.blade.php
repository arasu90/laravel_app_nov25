@extends('include.app_layout')
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="tile">
      <div class="tile-body">
        <form class="row" action="{{ route('viewAllIndexLive') }}" method="get">
          <div class="form-group col-md-4">
            <label for="nse_index" class="control-label">Live Index List</label>
            <select id="nse_index" class="form-control select2" name="nse_index">
              <option value="">Select NSE Index</option>
              @foreach($indexList as $key => $nseIndexList)
              <option
                value="{{ $key }}"
                {{ $key == $nseIndex ? 'selected' : '' }}>
                {{ $nseIndexList }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-md-4 align-self-end">
            <button class="btn btn-primary" type="submit">
              <i class="fa fa-fw fa-lg fa-check-circle"></i>
              Submit
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-12">
    <div class="tile">
      <h3 class="tile-title">Stock Detail View for {{ $nseIndex }}</h3>
      <div class="row">
        <div class="col-md-4">
          <div class="alert alert-primary">
            <strong>{{ $indexDetails['data']['identifier'] }}</strong>
            <br>
            <span class="badge badge-primary">{{ $indexDetails['data']['symbol'] }}</span>
            <br>
            <span>Last Price: <strong>{{$indexDetails['data']['lastPrice'] }}</strong></span>
            <br>
            <span>Change : <strong>{{ $indexDetails['data']['change'] }} <span class="badge badge-danger"> ( {{ $indexDetails['data']['pChange'] }} % )</span></strong></span><br>
            <h4>Day Changes </h4>
            Day Low: <strong>{{ $indexDetails['data']['dayLow'] }}</strong><br>
            Day High: <strong>{{ $indexDetails['data']['dayHigh'] }}</strong><br>
            52 Week Low: <strong>{{ $indexDetails['data']['yearLow'] }}</strong><br>
            52 Week High: <strong>{{ $indexDetails['data']['yearHigh'] }}</strong><br>
          </div>
        </div>
      </div>
      <div class="table-responsive table-hover table-striped">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Stock</th>
              <th>Last Price</th>
              <th>Change</th>
              <th>Change %</th>
              <th>Previous Close</th>
              <th>Open</th>
              <th>Intra Day Low</th>
              <th>Intra Day High</th>
              <th>52Week Low</th>
              <th>52Week High</th>
            </tr>
          </thead>
          <tbody>
            @foreach($indexStockList as $stock_daily_price_data)
            <tr
              class="{{ $stock_daily_price_data['pChange'] > 0
                ? 'text-success'
                : ($stock_daily_price_data['pChange'] < 0
                  ? 'text-danger'
                  : 'text-info')
                }}">
              <td>
                {{ $stock_daily_price_data['identifier'] }}
                <p><a href="{{ route('stockDetailView', ['stock_name' => $stock_daily_price_data['symbol']]) }}" target="_blank">{{ $stock_daily_price_data['symbol'] }}</a></p>
              </td>
              <td>
                {{ $stock_daily_price_data['lastPrice'] }}
              </td>
              <td>{{ $stock_daily_price_data['change'] }}</td>
              <td
                class="{{ $stock_daily_price_data['pChange'] > 0
                  ? 'table-success'
                  : ($stock_daily_price_data['pChange'] < 0
                    ? 'table-danger'
                    : 'table-info')
                  }}">
                {{ $stock_daily_price_data['pChange'] }} %
              </td>
              <td>{{ $stock_daily_price_data['previousClose'] }}</td>
              <td>{{ $stock_daily_price_data['open'] }}</td>
              <td>
                {{ $stock_daily_price_data['dayLow'] }}
              </td>
              <td>
                {{ $stock_daily_price_data['dayHigh'] }}
              </td>
              <td>
                {{ $stock_daily_price_data['yearLow'] }}
              </td>
              <td>
                {{ $stock_daily_price_data['yearHigh'] }}
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
