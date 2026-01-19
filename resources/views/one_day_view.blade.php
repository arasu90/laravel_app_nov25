@extends('include.app_layout')
<style>
  .info-price {
    -webkit-box-flex: 1;
    -ms-flex: 1;
    flex: 1;
    padding: 0 5px;
    -ms-flex-item-align: center;
    align-self: center;
  }
</style>
@section('content')
@php
$request = request();
$sort_by = $request->input('sort_by');
$stock_name = $request->input('stock_name');
@endphp
<div class="row">
  <div class="col-md-12">
    <div class="tile">
      <div class="tile-body">
        <form class="row" action="{{ route('oneDayView') }}" method="get">
          <div class="form-group col-md-3">
            <label for="" class="control-label">Stock Name</label>
            <input
              class="form-control"
              type="text"
              placeholder="Enter Stock Name"
              name="stock_name"
              value="{{ $stock_name }}">
          </div>
          <div class="form-group col-md-3">
            <label for="" class="control-label">Sort by</label>
            <select class="form-control" name="sort_by">
              <option
                {{ $sort_by == 'name_az' ? 'selected' : '' }}
                value="name_az">
                Name A-Z
              </option>
              <option
                {{ $sort_by == 'name_za' ? 'selected' : '' }}
                value="name_za">
                Name Z-A
              </option>
              <option
                {{ $sort_by == 'low_price' ? 'selected' : '' }}
                value="low_price">
                Price Low to High
              </option>
              <option
                {{ $sort_by == 'high_price' ? 'selected' : '' }}
                value="high_price">
                Price High to Low
              </option>
              <option
                {{ $sort_by == 'p_change_asc' ? 'selected' : '' }}
                value="p_change_asc">
                Percentage Low to High
              </option>
              <option
                {{ $sort_by == 'p_change_desc' ? 'selected' : '' }}
                value="p_change_desc">
                Percentage High to Low
              </option>
              <option
                {{ $sort_by == 'low_price_zero' ? 'selected' : '' }}
                value="low_price_zero">
                Price Low to High(Expect 0)
              </option>
              <option
                {{ $sort_by == 'high_price_zero' ? 'selected' : '' }}
                value="high_price_zero">
                Price High to Low<(Expect 0)
              </option>
              <option
                {{ $sort_by == 'p_change_asc_gt_zero' ? 'selected' : '' }}
                value="p_change_asc_gt_zero">
                Percentage Low to High(only > 0)
              </option>
              <option
                {{ $sort_by == 'p_change_desc_gt_zero' ? 'selected' : '' }}
                value="p_change_desc_gt_zero">
                Percentage High to Low(only > 0)
              </option>
              <option
                {{ $sort_by == 'p_change_asc_lt_zero' ? 'selected' : '' }}
                value="p_change_asc_lt_zero">
                Percentage Low to High(only < 0)
              </option>
              <option
                {{ $sort_by == 'p_change_desc_lt_zero' ? 'selected' : '' }}
                value="p_change_desc_lt_zero">
                Percentage High to Low(only < 0)
              </option>
              <option
                {{ $sort_by == 'low_price_price' ? 'selected' : '' }}
                value="low_price_price">
                Price Change Low to High
              </option>
              <option
                {{ $sort_by == 'high_price_price' ? 'selected' : '' }}
                value="high_price_price">
                Price Change High to Low
              </option>
              <option
                {{ $sort_by == 'p_change_price_asc_gt_zero' ? 'selected' : '' }}
                value="p_change_price_asc_gt_zero">
                Price Change Low to High(only > 0)
              </option>
              <option
                {{ $sort_by == 'p_change_price_desc_gt_zero' ? 'selected' : '' }}
                value="p_change_price_desc_gt_zero">
                Price Change High to Low(only > 0)
              </option>
              <option
                {{ $sort_by == 'p_change_price_asc_lt_zero' ? 'selected' : '' }}
                value="p_change_price_asc_lt_zero">
                Price Change Low to High(only < 0)
              </option>
              <option
                {{ $sort_by == 'p_change_price_desc_lt_zero' ? 'selected' : '' }}
                value="p_change_price_desc_lt_zero">
                Price Change High to Low(only < 0)
              </option>
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
    <h4 class="badge badge-dark">
      {{ $day_records->count() }} out of {{ $stockCount }} Stocks found for {{ $record_date ?? 'today' }}
    </h4>
  </div>
  @foreach($day_records as $record)
  <div class="col-6 col-sm-4 col-md-3 col-lg-2">
    <div class="widget-small
      {{$record->p_change > 0
        ? 'primary'
        : ($record->p_change < 0 ? 'danger' : 'info') }}">
      <div class="info-price">
        <h4>
          {{ $record->symbol }}
          <i
            class="btn btn-sm fa fa-fw fa-lg
              {{ $record->p_change > 0
               ? 'fa-arrow-up'
               : ($record->p_change < 0
                ? 'fa-arrow-down'
                : 'fa-arrow-right')
              }}"></i>
        </h4>
        <span style="font-size: 0.6rem;">{{ $record->company_name }}</span>
        <div class="info-price">
          <span style="float: inline-start;">
            <b>{{ $record->last_price }}</b>
          </span>
          <span style="float: inline-end;">
            <b>{{ $record->change }} ({{ $record->p_change }}%)</b>
          </span>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>
@endsection
