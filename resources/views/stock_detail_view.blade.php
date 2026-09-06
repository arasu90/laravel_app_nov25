@extends('include.app_layout')
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="tile">
      <div class="tile-body">
        <form class="row" action="{{ route('stockDetailView') }}" method="get">
          <div class="form-group col-md-4">
            <label for="stock_name" class="control-label">Stock List</label>
            <select id="stock_name" class="form-control select2" name="stock_name">
              <option value="">Select Stock</option>
              @foreach($stock_list as $stock)
              <option
                value="{{ $stock->symbol }}"
                {{ $stock->symbol == $stock_name ? 'selected' : '' }}>
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
      <h3 class="tile-title">Stock Detail View for {{ $stock_name }}</h3>
      <div class="row">
        <div class="col-md-4">
          <div class="alert alert-primary">
          <strong>{{ $stock_details->company_name }}</strong>
          <br>
          <span class="badge badge-primary">{{ $stock_details->symbol }}</span>
          <br>
          <small>Sector: {{ $stock_details->sector }}</small>
          <br>
          <small>Industry: {{ $stock_details->industry }}</small>
          Listing Status: <strong>{{ $stock_details->status }} </strong><br>
          Listing Date: <strong>{{ $stock_details->listing_date }} </strong><br>
          Trading Status: <strong><span class="{{ $stock_details->trading_status == 'Suspended' ? 'badge badge-danger' : '' }}">{{ $stock_details->trading_status }} </span> </strong><br>
          Trading Segment: <strong>{{ $stock_details->trading_segment }}</strong><br>
          Market Type: <strong>{{ $stock_details->market_type }}</strong><br>
          Active Series: <strong>{{ $stock_details->series }}</strong><br>
          Face Value: <strong>{{ $stock_details->face_value }}</strong> <br>
          Surveillance: <strong>{{ $stock_details->surveillance_desc }}</strong>
          <h4>52 Week Data</h4>
          52 Week Low: <span class="badge badge-info">{{ $stock_details->week_high_low_min }}</span> <br>
          52 Week Low Date: <strong>{{ $stock_details->week_high_low_min_date }}</strong> <br>
          52 Week High: <span class="badge badge-info">{{ $stock_details->week_high_low_max }}</span> <br>
          52 Week High Date: <strong>{{ $stock_details->week_high_low_max_date }}</strong> <br>
        </div>
        </div>
        <div class="col-md-4">
          <div class="alert alert-secondary">
            <span class="badge badge-primary">Live Price: {{ $stock_details->stock_last_price }}</span>
            <div class="embed-responsive embed-responsive-16by9">
                  <canvas
                    class="embed-responsive-item"
                    id="lineChartDemoDee"
                    data-values_1='@json($chartData["line"]["data_1"])'
                    data-values='@json($chartData["line"]["data"])'
                    data-label='@json($chartData["line"]["label"])'
                  ></canvas>
                </div>
                <span>Last 5 Days
                  <span class="badge" style="background-color:#e756cfff;color:white">Open Price</span>
                  <span class="badge" style="background-color:rgba(6, 62, 90, 1);color:white">Last Price</span>
                </span>
            </div>
        </div>
        <div class="col-md-4">
          <div class="alert alert-info">
            <p>
              <strong>Note:</strong> The icons next to the values indicate their relationship to key price points:
            </p>
            <ul>
              <li><i class="fa fa-arrows-h"></i> - Indicates the value is equal to the current price.</li>
              <li><i class="fa fa-arrow-down"></i> - Indicates the value is a 52-week low.</li>
              <li><i class="fa fa-arrow-up"></i> - Indicates the value is a 52-week high.</li>
              <li><i class="fa fa-arrow-circle-o-down"></i> - Indicates the value is a lower circuit price.</li>
              <li><i class="fa fa-arrow-circle-o-up"></i> - Indicates the value is an upper circuit price.</li>
              <li><i class="fa fa-bookmark-o"></i> - Indicates the value is the current day's 52-week low.</li>
              <li><i class="fa fa-bookmark"></i> - Indicates the value is the current day's 52-week high.</li>
              <li><i class="fa fa-toggle-down"></i> - Indicates the value is a 52-week low for the day.</li>
              <li><i class="fa fa-toggle-up"></i> - Indicates the value is a 52-week high for the day.</li>
            </ul>
          </div>
        </div>
      </div>
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
            @foreach($stock_daily_price_data as $stock_daily_price_data)
            <tr
              class="{{ $stock_daily_price_data->p_change > 0
                ? 'text-success'
                : ($stock_daily_price_data->p_change < 0
                  ? 'text-danger'
                  : 'text-info')
                }}">
              @php
                $priceIndicators = [
                  'last_price' => ['value' => $stock_daily_price_data->last_price, 'icon' => 'fa-arrows-h'],
                  'week_high_low_min' => ['value' => optional($stock_details)->week_high_low_min, 'icon' => 'fa-arrow-down'],
                  'week_high_low_max' => ['value' => optional($stock_details)->week_high_low_max, 'icon' => 'fa-arrow-up'],
                  'lower_cp' => ['value' => $stock_daily_price_data->lower_cp, 'icon' => 'fa-arrow-circle-o-down'],
                  'upper_cp' => ['value' => $stock_daily_price_data->upper_cp, 'icon' => 'fa-arrow-circle-o-up'],
                  'intra_day_high_low_min' => ['value' => $stock_daily_price_data->intra_day_high_low_min, 'icon' => 'fa-arrow-circle-down'],
                  'intra_day_high_low_max' => ['value' => $stock_daily_price_data->intra_day_high_low_max, 'icon' => 'fa-arrow-circle-up'],
                ];
              @endphp
              <td>
                {{ $stock_daily_price_data->date }}
                @if ($stock_daily_price_data->is_52_week_low)
                <i class="fa fa-toggle-down"></i>
                @endif
                @if ($stock_daily_price_data->is_52_week_high)
                <i class="fa fa-toggle-up"></i>
                @endif
              </td>
              <td>
                {{ $stock_daily_price_data->last_price }}
                @if ($stock_daily_price_data->date == optional($stock_details)->week_high_low_min_date )
                <i class="fa fa-bookmark-o"></i>
                @endif
                @if ($stock_daily_price_data->date == optional($stock_details)->week_high_low_max_date )
                <i class="fa fa-bookmark"></i>
                @endif
                @include('components.price-indicators', ['key' => 'last_price', 'value' => $stock_daily_price_data->last_price, 'indicators' => $priceIndicators])
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
                @include('components.price-indicators', ['key' => 'lower_cp', 'value' => $stock_daily_price_data->lower_cp, 'indicators' => $priceIndicators])
              </td>
              <td>
                {{ $stock_daily_price_data->upper_cp }}
                @include('components.price-indicators', ['key' => 'upper_cp', 'value' => $stock_daily_price_data->upper_cp, 'indicators' => $priceIndicators])
              </td>
              <td>
                {{ $stock_daily_price_data->intra_day_high_low_min }}
                @include('components.price-indicators', ['key' => 'intra_day_high_low_min', 'value' => $stock_daily_price_data->intra_day_high_low_min, 'indicators' => $priceIndicators])
              </td>
              <td>
                {{ $stock_daily_price_data->intra_day_high_low_max }}
                @include('components.price-indicators', ['key' => 'intra_day_high_low_max', 'value' => $stock_daily_price_data->intra_day_high_low_max, 'indicators' => $priceIndicators])
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const el = document.getElementById('lineChartDemoDee');
    var data = {
      labels: JSON.parse(el.dataset.label),
      datasets: [{
          fillColor: "rgba(151,187,205,0.2)",
          strokeColor: "rgba(27, 145, 204, 1)",
          pointColor: "rgba(18, 106, 150, 1)",
          pointStrokeColor: "#fff",
          pointHighlightFill: "#fff",
          pointHighlightStroke: "rgba(6, 62, 90, 1)",
          data: JSON.parse(el.dataset.values),
        },
        {
          label: "Last Price",
          fillColor: "rgba(215, 150, 218, 0.2)",
          strokeColor: "rgba(229, 132, 233, 1)",
          pointColor: "rgba(238, 180, 228, 1)",
          pointStrokeColor: "#ee7ddbff",
          pointHighlightFill: "#fff",
          pointHighlightStroke: "rgba(225, 166, 229, 1)",
          label: "Previous Close",
          data: JSON.parse(el.dataset.values_1),
        }
      ]
    };

    var ctx = $("#lineChartDemoDee").get(0).getContext("2d");
    var lineChart = new Chart(ctx).Line(data);
  });
</script>
@endsection
