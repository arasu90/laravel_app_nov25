@extends('include.app_layout')
@section('content')
<div class="app-title">
  <div>
    <h1><i class="fa fa-th-list"></i> Stock Detail View</h1>
  </div>
</div>
<div class="row">

  <div class="col-md-12">
    <div class="tile">
      <div class="tile-body">
        <form class="row" action="{{ route('stockDetailView') }}" method="get">
          <div class="form-group col-md-4">
            <label for="" class="control-label">Stock List</label>
            <select class="form-control select2" name="stock_name">
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
      <div class="table-responsive table-hover table-striped">
        <table class="table table-striped table-bordered">
          <thead>
            <tr class="text-bold text-center">
              <th>Company Name</th>
              <th>Company Details</th>
              <th>Last 5 Days Chart</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <strong>{{ $stock_details->company_name ?? 'N/A' }}</strong>
                <br>
                <span class="badge badge-primary">{{ $stock_details->symbol ?? 'N/A' }}</span>
                <br>
                <small>Sector: {{ $stock_details->sector ?? 'N/A' }}</small>
                <br>
                <small>Industry: {{ $stock_details->industry ?? 'N/A' }}</small>
                <br>
                <span>Price:<i class="fa fa-arrows-h"></i></span>
                <br>
                <span>
                  <span>52WeekLow: <i class="fa fa-arrow-down"></i></span>
                  <span style="float: inline-end;">
                    52WeekHigh: <i class="fa fa-arrow-up"></i>
                  </span>
                </span>
                <br>
                <span>
                  <span>LowerCP: <i class="fa fa-arrow-circle-o-down"></i></span>
                  <span style="float: inline-end;">
                    UpperCP: <i class="fa fa-arrow-circle-o-up"></i>
                  </span>
                </span>
                <br>
                <span>
                  <span>IntraDayLow: <i class="fa fa-arrow-circle-down"></i></span>
                  <span style="float: inline-end;">
                    IntraDayHigh: <i class="fa fa-arrow-circle-up"></i>
                  </span>
                </span>
                <br>
                <span>
                  <span>CurrentDay52WeekLow: <i class="fa fa-bookmark-o"></i></span>
                  <span style="float: inline-end;">
                    CurrentDay52WeekHigh: <i class="fa fa-bookmark"></i>
                  </span>
                </span>
                <br>
                <span>
                  <span>Day52WeekLow: <i class="fa fa-toggle-down"></i></span>
                  <span style="float: inline-end;">
                    Day52WeekHigh: <i class="fa fa-toggle-up"></i>
                  </span>
                </span>
              </td>
              <td>
                Listing Status: {{ $stock_details->status ?? 'N/A' }} <br>
                Listing Date: {{ $stock_details->listing_date ?? 'N/A' }} <br>
                Trading Status: {{ $stock_details->trading_status ?? 'N/A' }} <br>
                Trading Segment: {{ $stock_details->trading_segment ?? 'N/A' }} <br>
                Face Value: {{ $stock_details->face_value ?? 'N/A' }} <br>
                <h4>52 Week Data</h4>
                52 Week Low: {{ $stock_details->week_high_low_min ?? 'N/A' }} <br>
                52 Week Low Date: {{ $stock_details->week_high_low_min_date ?? 'N/A' }} <br>
                52 Week High: {{ $stock_details->week_high_low_max ?? 'N/A' }} <br>
                52 Week High Date: {{ $stock_details->week_high_low_max_date ?? 'N/A' }} <br>
              </td>
              <td>
                <div class="embed-responsive embed-responsive-16by9">
                  <canvas class="embed-responsive-item" id="lineChartDemoDee"></canvas>
                </div>
                <span>Last 5 Days
                  <span class="badge" style="background-color:#e756cfff;color:white">Open Price</span>
                  <span class="badge" style="background-color:rgba(6, 62, 90, 1);color:white">Last Price</span>
                </span>
              </td>
            </tr>
          </tbody>
        </table>
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
                @if ($stock_daily_price_data->date == $stock_details->week_high_low_min_date )
                <i class="fa fa-bookmark-o"></i>
                @endif
                @if ($stock_daily_price_data->date == $stock_details->week_high_low_max_date )
                <i class="fa fa-bookmark"></i>
                @endif
                @if ($stock_daily_price_data->last_price == $stock_details->week_high_low_min )
                <i class="fa fa-arrow-down"></i>
                @endif
                @if ($stock_daily_price_data->last_price == $stock_details->week_high_low_max )
                <i class="fa fa-arrow-up"></i>
                @endif
                @if ($stock_daily_price_data->last_price == $stock_daily_price_data->lower_cp )
                <i class="fa fa-arrow-circle-o-down"></i>
                @endif
                @if ($stock_daily_price_data->last_price == $stock_daily_price_data->upper_cp )
                <i class="fa fa-arrow-circle-o-up"></i>
                @endif
                @if ($stock_daily_price_data->last_price == $stock_daily_price_data->intra_day_high_low_min )
                <i class="fa fa-arrow-circle-down"></i>
                @endif
                @if ($stock_daily_price_data->last_price == $stock_daily_price_data->intra_day_high_low_max )
                <i class="fa fa-arrow-circle-up"></i>
                @endif
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
                @if ($stock_daily_price_data->lower_cp == $stock_daily_price_data->last_price )
                <i class="fa fa-arrows-h"></i>
                @endif
                @if ($stock_daily_price_data->lower_cp == $stock_details->week_high_low_min )
                <i class="fa fa-arrow-down"></i>
                @endif
                @if ($stock_daily_price_data->lower_cp == $stock_details->week_high_low_max )
                <i class="fa fa-arrow-up"></i>
                @endif
                @if ($stock_daily_price_data->lower_cp == $stock_daily_price_data->upper_cp )
                <i class="fa fa-arrow-circle-o-up"></i>
                @endif
                @if ($stock_daily_price_data->lower_cp == $stock_daily_price_data->intra_day_high_low_min )
                <i class="fa fa-arrow-circle-down"></i>
                @endif
                @if ($stock_daily_price_data->lower_cp == $stock_daily_price_data->intra_day_high_low_max )
                <i class="fa fa-arrow-circle-up"></i>
                @endif
              </td>
              <td>
                {{ $stock_daily_price_data->upper_cp }}
                @if ($stock_daily_price_data->upper_cp == $stock_daily_price_data->last_price )
                <i class="fa fa-arrows-h"></i>
                @endif
                @if ($stock_daily_price_data->upper_cp == $stock_details->week_high_low_min )
                <i class="fa fa-arrow-down"></i>
                @endif
                @if ($stock_daily_price_data->upper_cp == $stock_details->week_high_low_max )
                <i class="fa fa-arrow-up"></i>
                @endif
                @if ($stock_daily_price_data->upper_cp == $stock_daily_price_data->lower_cp )
                <i class="fa fa-arrow-circle-o-down"></i>
                @endif
                @if ($stock_daily_price_data->upper_cp == $stock_daily_price_data->intra_day_high_low_min )
                <i class="fa fa-arrow-circle-down"></i>
                @endif
                @if ($stock_daily_price_data->upper_cp == $stock_daily_price_data->intra_day_high_low_max )
                <i class="fa fa-arrow-circle-up"></i>
                @endif
              </td>
              <td>
                {{ $stock_daily_price_data->intra_day_high_low_min }}
                @if ($stock_daily_price_data->intra_day_high_low_min == $stock_daily_price_data->last_price )
                <i class="fa fa-arrows-h"></i>
                @endif
                @if ($stock_daily_price_data->intra_day_high_low_min == $stock_details->week_high_low_min )
                <i class="fa fa-arrow-down"></i>
                @endif
                @if ($stock_daily_price_data->intra_day_high_low_min == $stock_details->week_high_low_max )
                <i class="fa fa-arrow-up"></i>
                @endif
                @if ($stock_daily_price_data->intra_day_high_low_min == $stock_daily_price_data->lower_cp )
                <i class="fa fa-arrow-circle-o-down"></i>
                @endif
                @if ($stock_daily_price_data->intra_day_high_low_min == $stock_daily_price_data->upper_cp )
                <i class="fa fa-arrow-circle-o-up"></i>
                @endif
                @if ($stock_daily_price_data->intra_day_high_low_min == $stock_daily_price_data->intra_day_high_low_max )
                <i class="fa fa-arrow-circle-up"></i>
                @endif
              </td>
              <td>
                {{ $stock_daily_price_data->intra_day_high_low_max }}
                @if ($stock_daily_price_data->intra_day_high_low_max == $stock_daily_price_data->last_price )
                <i class="fa fa-arrows-h"></i>
                @endif
                @if ($stock_daily_price_data->intra_day_high_low_max == $stock_details->week_high_low_min )
                <i class="fa fa-arrow-down"></i>
                @endif
                @if ($stock_daily_price_data->intra_day_high_low_max == $stock_details->week_high_low_max )
                <i class="fa fa-arrow-up"></i>
                @endif
                @if ($stock_daily_price_data->intra_day_high_low_max == $stock_daily_price_data->lower_cp )
                <i class="fa fa-arrow-circle-o-down"></i>
                @endif
                @if ($stock_daily_price_data->intra_day_high_low_max == $stock_daily_price_data->upper_cp )
                <i class="fa fa-arrow-circle-o-up"></i>
                @endif
                @if ($stock_daily_price_data->intra_day_high_low_max == $stock_daily_price_data->intra_day_high_low_min )
                <i class="fa fa-arrow-circle-down"></i>
                @endif
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
    var data = {
      labels: @json($chartData['line']['label']),
      datasets: [{
          fillColor: "rgba(151,187,205,0.2)",
          strokeColor: "rgba(27, 145, 204, 1)",
          pointColor: "rgba(18, 106, 150, 1)",
          pointStrokeColor: "#fff",
          pointHighlightFill: "#fff",
          pointHighlightStroke: "rgba(6, 62, 90, 1)",
          data: @json($chartData['line']['data']),
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
          data: @json($chartData['line']['data_1']),
        }
      ]
    };

    var ctxl = $("#lineChartDemoDee").get(0).getContext("2d");
    var lineChart = new Chart(ctxl).Line(data);
  });
</script>
@endsection
