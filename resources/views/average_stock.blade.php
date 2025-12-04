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

<div class="row">
  <div class="col-md-12">
    <div class="tile">
      <div class="tile-title">
        <h4>Average Stock</h4>
      </div>
      <div class="tile-body">
        <form class="row" action="{{ route('averageStock') }}" method="get">
          <!-- <input type="hidden" name="_token" value="{{ csrf_token() }}"> -->
          <div class="form-group col-md-3">
            <label class="control-label">Current Total Quantity</label>
            <input class="form-control" type="text" placeholder="Enter Current Total Quantity" name="current_total_quantity" value="{{ $current_total_quantity ?? '' }}">
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">Current Average Price</label>
            <input class="form-control" type="text" placeholder="Enter Current Average Price" name="current_average_price" value="{{ $current_average_price ?? '' }}">
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">New Buy Price</label>
            <input class="form-control" type="text" placeholder="Enter New Buy Price" name="new_buy_price" value="{{ $new_buy_price ?? '' }}">
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">Expected Average Price</label>
            <input class="form-control" type="text" placeholder="Enter Expected Average Price" name="expected_average_price" value="{{ $expected_average_price ?? '' }}">
            <input class="form-control" type="hidden" name="calculator_type" value="average_stock">
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">Profit/Loss</label>
            <div class="toggle lg">
              <label>
                <input type="checkbox" name="avg_profit_loss" value="1" {{ $avg_profit_loss == 1 ? 'checked' : '' }}><span class="button-indecator"></span>
              </label>
            </div>
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">Live Price</label>
            <input class="form-control" type="text" placeholder="Enter Live Price" name="avg_live_price" value="{{ $avg_live_price ?? '' }}">
          </div>
          <div class="form-group col-md-4 align-self-end">
            <button class="btn btn-primary" type="submit"><i class="fa fa-fw fa-lg fa-check-circle"></i>Submit</button>
          </div>
        </form>
      </div>
      <div class="tile-body table-responsive">
        <h4>New Buy Quantity: {{ $new_buy_quantity_average }}</h4>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Total Quantity</th>
              <th>Total Investment</th>
              <th>Required Investment</th>
              <th>Profit/Loss</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{{ $current_total_quantity + $new_buy_quantity_average }}</td>
              <td>{{ ($current_total_quantity + $new_buy_quantity_average) * $expected_average_price }}</td>
              <td>{{ $new_buy_quantity_average * $expected_average_price }} ({{ $new_buy_quantity_average }} * {{ $expected_average_price }})</td>
              <td>{{ $avg_profit_loss == 1 ? ($avg_live_price - $expected_average_price) * ($current_total_quantity + $new_buy_quantity_average) : '--' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
 
  <div class="col-md-12">
    <div class="tile">
      <div class="tile-title">
        <h4>Buy Quantity Calculator</h4>
      </div>
      <div class="tile-body">
        <form class="row" action="{{ route('averageStock') }}" method="get">
          <div class="form-group col-md-3">
            <label class="control-label">Current Total Quantity</label>
            <input class="form-control" type="text" placeholder="Enter Current Total Quantity" name="current_total_quantity" value="{{ $current_total_quantity ?? '' }}">
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">Current Average Price</label>
            <input class="form-control" type="text" placeholder="Enter Current Average Price" name="current_average_price" value="{{ $current_average_price ?? '' }}">
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">New Buy Price</label>
            <input class="form-control" type="text" placeholder="Enter New Buy Price" name="new_buy_price" value="{{ $new_buy_price ?? '' }}">
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">New Buy Quantity</label>
            <input class="form-control" type="text" placeholder="Enter New Buy Quantity" name="new_buy_quantity" value="{{ $new_buy_quantity ?? '' }}">
            <input class="form-control" type="hidden" name="calculator_type" value="buy_quantity_calculator">
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">Profit/Loss</label>
            <div class="toggle lg">
              <label>
                <input type="checkbox" name="qty_profit_loss" value="1" {{ $qty_profit_loss == 1 ? 'checked' : '' }}><span class="button-indecator"></span>
              </label>
            </div>
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">Live Price</label>
            <input class="form-control" type="text" placeholder="Enter Live Price" name="qty_live_price" value="{{ $qty_live_price ?? '' }}">
          </div>
          <div class="form-group col-md-4 align-self-end">
            <button class="btn btn-primary" type="submit"><i class="fa fa-fw fa-lg fa-check-circle"></i>Submit</button>
          </div>
        </form>
      </div>
      <div class="tile-body table-responsive">
        <h4>New Average Price: {{ $new_buy_price_average }}</h4>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Total Quantity</th>
              <th>Total Investment</th>
              <th>Required Investment</th>
              <th>Profit/Loss</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{{ $current_total_quantity + $new_buy_quantity }}</td>
              <td>{{ $current_total_quantity * $current_average_price + $new_buy_price * $new_buy_quantity }}</td>
              <td>{{ $new_buy_price * $new_buy_quantity }} ({{ $new_buy_quantity }} * {{ $new_buy_price }})</td>
              <td>{{ $qty_profit_loss == 1 ? ($qty_live_price - $new_buy_price_average) * ($current_total_quantity + $new_buy_quantity) : '--' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection