@php
use App\Http\Controllers\HomeController;
@endphp
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
      <div class="tile-body">
        <form class="row" action="{{ url()->current() }}" method="get">
          <div class="form-group col-md-4">
            <label for="stock_name" class="control-label">Stock List</label>
            <select class="form-control select2" id="stock_name" name="stock_name" required>
              <option value="">Select Stock</option>
              @foreach($stock_list as $stock)
                <option
                  value="{{ $stock->symbol }}"
                  {{ $stock->symbol == $stock_name ? 'selected' : '' }}
                >
                    {{ $stock->symbol }} - {{ $stock->details->company_name ?? '' }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-md-4 align-self-end">
            <button class="btn btn-primary" type="submit">
              <i class="fa fa-fw fa-lg fa-check-circle"></i>
              Submit
            </button>
            <a href="{{ url()->current() }}" class="btn btn-secondary float-right">
                Reset
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="tile p-0">
      <ul class="nav flex-column nav-tabs user-tabs">
        @foreach($watchListList as $watchListName => $watchListItems)
          <li class="nav-item">
            <a
              class="nav-link {{ $loop->first ? 'active' : '' }}"
              href="#{{ $watchListName }}"
              data-toggle="tab">
              {{ $watchListItems['name'] }}
              <span class="badge badge-dark">{{ count($watchListItems['stock_list']) }}</span>
              <span class="badge badge-success">{{
                  $watchListItems['stock_list']->filter(function($item) {
                    return $item->p_change > 0;
                  })->count();
                }}
              </span>
              <span class="badge badge-danger">{{
                  $watchListItems['stock_list']->filter(function($item) {
                    return $item->p_change < 0;
                  })->count();
                }}
              </span>
              <span class="badge badge-info">{{
                  $watchListItems['stock_list']->filter(function($item) {
                    return $item->p_change == 0;
                  })->count();
                }}
              </span>
            </a>
          </li>
        @endforeach
      </ul>
    </div>
  </div>
  <div class="col-md-9">
    <div class="tab-content">
      @foreach($watchListList as $watchListName => $watchListItems)
        <div class="tab-pane {{ $loop->first ? 'active' : '' }}" id="{{ $watchListName }}">
          <div class="tile">
            <h3 class="tile-title">{{ $watchListItems['name'] }}</h3>
            <div class="table-responsive table-hover table-striped">
              <table class="table table-striped watchlistDataTable">
                <thead>
                  <tr>
                    <th>S.No</th>
                    <th>Stock</th>
                    <th>Last Price</th>
                    <th>Today Price</th>
                    <th>Intra Day</th>
                    <th>52 Week</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($watchListItems['stock_list'] as $watchListItem)
                    @php
                    $sameMonthYearHigh = HomeController::sameMonthYear($watchListItem->week_high_low_max_date);
                    $sameMonthYearLow = HomeController::sameMonthYear($watchListItem->week_high_low_min_date);
                    @endphp
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>
                        <p>{{ $watchListItem->company_name ?? '' }}</p>
                        <a target="_blank" href="stock-detail-view?stock_name={{ $watchListItem->symbol }}">{{ $watchListItem->symbol }}</a>
                      </td>
                      <td>
                        {{ $watchListItem->last_price }}
                      </td>
                      <td class="{{ $watchListItem->p_change > 0 ? 'text-success' : ($watchListItem->p_change < 0 ? 'text-danger' : 'text-info') }}">
                        {{ $watchListItem->change }} ({{ $watchListItem->p_change }} %)
                        <br>
                        P.Close: {{ $watchListItem->previous_close }}
                        <br>
                        Open: {{ $watchListItem->open }}
                        <br>
                        Close: {{ $watchListItem->close }}
                      </td>
                      <td>
                        @if ($watchListItem->lower_cp)
                        Lower CP: {{ $watchListItem->lower_cp }}
                        <br>
                        @endif
                        Low: {{ $watchListItem->intra_day_high_low_min }}
                        <br>
                        High: {{ $watchListItem->intra_day_high_low_max }}
                        @if ($watchListItem->upper_cp)
                        <br>
                        Upper CP: {{ $watchListItem->upper_cp }}
                        @endif
                      </td>
                      <td>
                        <br>
                        Low: {{ $watchListItem->week_high_low_min }}
                        <br>
                        @if ($watchListItem->week_high_low_min_date)
                        <span class="{{ $sameMonthYearLow ? 'text-danger' : '' }}"> Date: {{ $watchListItem->week_high_low_min_date }} </span>
                        <br>
                        @endif
                        High: {{ $watchListItem->week_high_low_max }}
                        @if ($watchListItem->week_high_low_max_date)
                        <br>
                        <span class="{{ $sameMonthYearHigh ? 'text-success' : '' }}"> Date: {{ $watchListItem->week_high_low_max_date }} </span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
  
@endsection
