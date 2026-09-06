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
            <label for="stock_name" class="control-label">Stock Name</label>
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
          <div class="form-group col-md-3">
            <label for="sort_by" class="control-label">Sort by</label>
            <select id="sort_by" class="form-control" name="sort_by">
              @foreach($sort_options as $value => $label)
                <option value="{{ $value }}" {{ ($sort_by ?? '') == $value ? 'selected' : '' }}>
                  {{ $label }}
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
