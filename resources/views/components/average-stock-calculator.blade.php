@php
  $isAverageCalculator = $type === 'average_stock';
  $result = $isAverageCalculator ? $newBuyQuantityAverage : $newBuyPriceAverage;
  $resultLabel = $isAverageCalculator ? 'New Buy Quantity' : 'New Average Price';
  $profitLossField = $isAverageCalculator ? 'avg_profit_loss' : 'qty_profit_loss';
  $livePriceField = $isAverageCalculator ? 'avg_live_price' : 'qty_live_price';
  $profitLossEnabled = $inputs[$profitLossField];
  $livePrice = $inputs[$livePriceField];
  $profitLoss = $isAverageCalculator
    ? ($livePrice - $inputs['expected_average_price']) * ($inputs['current_total_quantity'] + $newBuyQuantityAverage)
    : ($livePrice - $newBuyPriceAverage) * ($inputs['current_total_quantity'] + $inputs['new_buy_quantity']);
@endphp

<div class="col-md-12">
  <div class="tile">
    <div class="tile-title"><h4>{{ $isAverageCalculator ? 'Average Stock' : 'Buy Quantity Calculator' }}</h4></div>
    <div class="tile-body">
      <form class="row" action="{{ route('averageStock') }}" method="get">
        @foreach ([
          'current_total_quantity' => 'Current Total Quantity',
          'current_average_price' => 'Current Average Price',
          'new_buy_price' => 'New Buy Price',
          ($isAverageCalculator ? 'expected_average_price' : 'new_buy_quantity') => $isAverageCalculator ? 'Expected Average Price' : 'New Buy Quantity',
        ] as $name => $label)
        <div class="form-group col-md-3">
          <label for="{{ $name }}" class="control-label">{{ $label }}</label>
          <input class="form-control" type="text" placeholder="Enter {{ $label }}" name="{{ $name }}" value="{{ $inputs[$name] ?? '' }}">
          @if ($loop->last)<input type="hidden" name="calculator_type" value="{{ $type }}">@endif
        </div>
        @endforeach
        <div class="form-group col-md-3">
          <label class="control-label">Profit/Loss</label>
          <div class="toggle lg"><label><input type="checkbox" name="{{ $profitLossField }}" value="1" {{ $profitLossEnabled == 1 ? 'checked' : '' }}><span class="button-indecator"></span></label></div>
        </div>
        <div class="form-group col-md-3">
          <label class="control-label">Live Price</label>
          <input class="form-control" type="text" placeholder="Enter Live Price" name="{{ $livePriceField }}" value="{{ $livePrice ?? '' }}">
        </div>
        <div class="form-group col-md-4 align-self-end"><button class="btn btn-primary" type="submit"><i class="fa fa-fw fa-lg fa-check-circle"></i>Submit</button></div>
      </form>
    </div>
    <div class="tile-body table-responsive">
      <h4>{{ $resultLabel }}: {{ $result }}</h4>
      <table class="table table-bordered">
        <thead><tr><th>Total Quantity</th><th>Total Investment</th><th>Required Investment</th><th>Profit/Loss</th></tr></thead>
        <tbody><tr>
          @if ($isAverageCalculator)
            <td>{{ $inputs['current_total_quantity'] + $newBuyQuantityAverage }}</td>
            <td>{{ ($inputs['current_total_quantity'] + $newBuyQuantityAverage) * $inputs['expected_average_price'] }}</td>
            <td>{{ $newBuyQuantityAverage * $inputs['expected_average_price'] }} ({{ $newBuyQuantityAverage }} * {{ $inputs['expected_average_price'] }})</td>
          @else
            <td>{{ $inputs['current_total_quantity'] + $inputs['new_buy_quantity'] }}</td>
            <td>{{ $inputs['current_total_quantity'] * $inputs['current_average_price'] + $inputs['new_buy_price'] * $inputs['new_buy_quantity'] }}</td>
            <td>{{ $inputs['new_buy_price'] * $inputs['new_buy_quantity'] }} ({{ $inputs['new_buy_quantity'] }} * {{ $inputs['new_buy_price'] }})</td>
          @endif
          <td>{{ $profitLossEnabled == 1 ? $profitLoss : '--' }}</td>
        </tr></tbody>
      </table>
    </div>
  </div>
</div>