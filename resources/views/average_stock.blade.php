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
  @include('components.average-stock-calculator', [
    'type' => 'average_stock',
    'inputs' => $inputs,
    'newBuyQuantityAverage' => $newBuyQuantityAverage,
    'newBuyPriceAverage' => $newBuyPriceAverage,
  ])
  @include('components.average-stock-calculator', [
    'type' => 'buy_quantity_calculator',
    'inputs' => $inputs,
    'newBuyQuantityAverage' => $newBuyQuantityAverage,
    'newBuyPriceAverage' => $newBuyPriceAverage,
  ])
</div>
@endsection
