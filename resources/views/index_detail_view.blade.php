@extends('include.app_layout')
@section('content')
<div class="app-title">
  <div>
    <h1><i class="fa fa-th-list"></i> Index Detail View</h1>
  </div>
</div>
<div class="row">

  <div class="col-md-12">
    <div class="tile">
      <div class="tile-body">
        <form class="row" action="{{ route('stockDetailView') }}" method="get">
          <div class="form-group col-md-4">
            <label for="" class="control-label">Index List</label>
            <select class="form-control select2" name="index_name">
              <option value="">Select Index</option>
              @foreach($stock_list as $stock)
              <option
                value="{{ $stock->symbol }}"
                {{ $stock->symbol == $index_name ? 'selected' : '' }}>
                {{ $stock->symbol }} - {{ $stock->details->company_name ?? 'N/A' }}
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
      <h3 class="tile-title">Index Detail View for {{ $index_name }}</h3>
      <div class="table-responsive table-hover table-striped">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Date</th>
              <th>Last Price</th>
              <th>Change</th>
              <th>Change %</th>
              <th>Previous Close</th>
              <th>Open</th>
              <th>Day Low</th>
              <th>Day High</th>
              <th>Year Low</th>
              <th>Year High</th>
              <th>Advanced</th>
              <th>Declines</th>
              <th>Unchanged</th>
            </tr>
          </thead>
          <tbody>
            @foreach($nseIndexDataRecords as $nseIndexData)
            <tr
              class="{{ $nseIndexData->value_p_change > 0
                ? 'text-success'
                : ($nseIndexData->value_p_change < 0
                  ? 'text-danger'
                  : 'text-info')
                }}">
              <td>
                {{ $nseIndexData->trade_date }}
              </td>
              <td>
                {{ $nseIndexData->value_last }}
              </td>
              <td>{{ $nseIndexData->value_change }}</td>
              <td
                class="{{ $nseIndexData->value_p_change > 0
                  ? 'table-success'
                  : ($nseIndexData->value_p_change < 0
                    ? 'table-danger'
                    : 'table-info')
                  }}">
                {{ $nseIndexData->value_p_change }} %
              </td>
              <td>{{ $nseIndexData->previous_close }}</td>
              <td>{{ $nseIndexData->value_open }}</td>
              <td>{{ $nseIndexData->day_low }}</td>
              <td>{{ $nseIndexData->day_high }}</td>
              <td>{{ $nseIndexData->year_low }}</td>
              <td>{{ $nseIndexData->year_high }}</td>
              <td>{{ $nseIndexData->advances }}</td>
              <td>{{ $nseIndexData->declines }}</td>
              <td>{{ $nseIndexData->unchanged }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-12">
    <div class="tile">
      <h3 class="tile-title">Index Stock View for {{ $index_name }}</h3>
      <div class="table-responsive table-hover table-striped">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Date</th>
              <th>Last Price</th>
              <th>Change</th>
              <th>Change %</th>
              <th>Previous Close</th>
              <th>Open</th>
              <th>Close</th>
              <th>Lower CP</th>
              <th>Upper CP</th>
              <th>Intra Day Low</th>
              <th>Intra Day High</th>
            </tr>
          </thead>
          <tbody>
            @foreach($stockDailyPriceRecords as $stock_daily_price_data)
            <tr
              class="{{ $stock_daily_price_data->p_change > 0
                ? 'text-success'
                : ($stock_daily_price_data->p_change < 0
                  ? 'text-danger'
                  : 'text-info')
                }}">
              <td>
                {{ $stock_daily_price_data->date }}
                <p>{{ $stock_daily_price_data->symbol }}</p>
              </td>
              <td>
                {{ $stock_daily_price_data->last_price }}
              </td>
              <td>{{ $stock_daily_price_data->change }}</td>
              <td
                class="{{ $stock_daily_price_data->p_change > 0
                  ? 'table-success'
                  : ($stock_daily_price_data->p_change < 0
                    ? 'table-danger'
                    : 'table-info')
                  }}">
                {{ $stock_daily_price_data->p_change }} %
              </td>
              <td>{{ $stock_daily_price_data->previous_close }}</td>
              <td>{{ $stock_daily_price_data->open }}</td>
              <td>{{ $stock_daily_price_data->close }}</td>
              <td>
                {{ $stock_daily_price_data->lower_cp }}
              </td>
              <td>
                {{ $stock_daily_price_data->upper_cp }}
              </td>
              <td>
                {{ $stock_daily_price_data->intra_day_high_low_min }}
              </td>
              <td>
                {{ $stock_daily_price_data->intra_day_high_low_max }}
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
