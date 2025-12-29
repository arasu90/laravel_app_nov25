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
$index_name = $request->input('index_name');
@endphp
<div class="row">

  <div class="col-md-12">
    <div class="tile">
      <div class="tile-body">
        <form class="row" action="{{ route('oneDayIndex') }}" method="get">
          <!-- <input type="hidden" name="_token" value="{{ csrf_token() }}"> -->
          <div class="form-group col-md-3">
            <label class="control-label">Index Name</label>
            <input
              class="form-control"
              type="text"
              placeholder="Enter Index Name"
              name="index_name"
              value="{{ $index_name }}">
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">Sort by</label>
            <select class="form-control" name="sort_by">
              <option
                {{ $sort_by == 'default' ? 'selected' : '' }}
                value="default">
                Default
              </option>
              <option
                {{ $sort_by == 'top_gain_asc' ? 'selected' : '' }}
                value="top_gain_asc">
                Top Gainer(%) Asc
              </option>
              <option
                {{ $sort_by == 'top_gain_desc' ? 'selected' : '' }}
                value="top_gain_desc">
                Top Gainer(%) Desc
              </option>
              <option
                {{ $sort_by == 'top_lose_asc' ? 'selected' : '' }}
                value="top_lose_asc">
                Top Losser(%) Asc
              </option>
              <option
                {{ $sort_by == 'top_lose_desc' ? 'selected' : '' }}
                value="top_lose_desc">
                Top Losser(%) Desc
              </option>
              <option
                {{ $sort_by == 'top_gain_price_asc' ? 'selected' : '' }}
                value="top_gain_price_asc">
                Top Gainer(Price) Asc
              </option>
              <option
                {{ $sort_by == 'top_gain_price_desc' ? 'selected' : '' }}
                value="top_gain_price_desc">
                Top Gainer(Price) Desc
              </option>
              <option
                {{ $sort_by == 'top_lose_price_asc' ? 'selected' : '' }}
                value="top_lose_price_asc">
                Top Losser(Price) Asc
              </option>
              <option
                {{ $sort_by == 'top_lose_price_desc' ? 'selected' : '' }}
                value="top_lose_price_desc">
                Top Losser(Price) Desc
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
      {{ $day_records->count() }} NSE Index found for {{ $record_date ?? 'today' }}
    </h4>
  </div>
  @foreach($day_records as $record)
  <div class="col-6 col-sm-3 col-md-4 col-lg-2">
    <div class="widget-small
      {{$record->value_p_change > 0
        ? 'primary'
        : ($record->value_p_change < 0 ? 'danger' : 'info') }}">
      <div class="info-price">
        <h4>
          {{ $record->index_symbol }}
          <i
            class="btn btn-sm fa fa-fw fa-lg 
              {{ $record->value_p_change > 0
               ? 'fa-arrow-up'
               : ($record->value_p_change < 0
                ? 'fa-arrow-down'
                : 'fa-arrow-right') 
              }}"></i>
        </h4>
        <span style="font-size: 0.6rem;">P.Close: {{ $record->previous_close }}</span>
        <div class="info-price">
          <span style="float: inline-start;">
            <b>{{ $record->value_last }}</b>
          </span>
          <span style="float: inline-end;">
            <b>{{ $record->value_change }} ({{ $record->value_p_change }}%)</b>
          </span>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>
@endsection