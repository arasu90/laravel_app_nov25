@extends('include.app_layout')
@section('content')
<div class="app-title">
  <div>
    <h1><i class="fa fa-th-list"></i> My Portfolio</h1>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="tile">
      <div class="tile-body">
        <form class="row" action="{{ route('addMyPortfolio') }}" method="post">
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <div class="form-group col-md-3">
            <label class="control-label">Stock List</label>
            <select class="form-control select2" name="stock_name">
              <option value="">Select Stock</option>
              @foreach($stock_list as $stock)
                <option value="{{ $stock->symbol }}">{{ $stock->symbol }} - {{ $stock->details->company_name ?? '' }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">Buy Price</label>
            <input class="form-control" type="text" placeholder="Enter Buy Price" name="buy_price" value="{{ $buy_price ?? '' }}">
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">Buy Qty</label>
            <input class="form-control" type="text" placeholder="Enter Buy Qty" name="buy_qty" value="{{ $buy_qty ?? '' }}">
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">Buy Date</label>
            <input class="form-control" type="date" placeholder="Enter Buy Date" name="buy_date" value="{{ $buy_date ?? '' }}">
          </div>
          <div class="form-group col-md-3 align-self-end">
            <button class="btn btn-primary" type="submit"><i class="fa fa-fw fa-lg fa-check-circle"></i>Add to Portfolio</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-12">
    <div class="tile">
      <!-- <h3 class="tile-title">My Portfolio</h3> -->
      <div class="table-responsive table-hover table-striped">
        <table class="table table-striped table-bordered">
          <thead>
            <tr class="text-bold text-center">
              <th colspan="2">Overall Profit/Loss</th>
            </tr>
          </thead>
          <tbody>
            @php
              $overall_profit_loss = 0;
              $overall_profit_loss_per = 0;
              $overall_invested_amount = 0;
              $overall_live_amount = 0;
              $todayProfitLoss = 0;
              $todayProfitLossPer = 0;
            @endphp
            @foreach($myPortfolioStocks as $myPortfolio)
              @php
                $overall_invested_amount += $myPortfolio->buy_price * $myPortfolio->buy_qty;
                $overall_live_amount += $myPortfolio->last_price * $myPortfolio->buy_qty;
                $overall_profit_loss += ($myPortfolio->last_price * $myPortfolio->buy_qty) - ($myPortfolio->buy_price * $myPortfolio->buy_qty);
                $overall_profit_loss_per = round(($overall_profit_loss / $overall_invested_amount) * 100, 2);
                $todayProfitLoss += $myPortfolio->change * $myPortfolio->buy_qty;
              @endphp
            @endforeach
            <tr>
              <td>
                <span class="{{ $overall_profit_loss_per > 0 ? 'text-success' : ($overall_profit_loss_per < 0 ? 'text-danger' : 'text-info') }}">
                  Total: Rs. {{ $overall_live_amount }}
                </span>
                <br />
                <span class="{{ $overall_profit_loss_per > 0 ? 'text-success' : ($overall_profit_loss_per < 0 ? 'text-danger' : 'text-info') }}">
                  Profit/Loss: Rs. {{ $overall_profit_loss }} ({{ $overall_profit_loss_per }} %)
                </span>
                <br />
                  Invested: Rs. {{ $overall_invested_amount }}
              </td>
              <td class="{{ $todayProfitLoss > 0 ? 'text-success' : ($todayProfitLoss < 0 ? 'text-danger' : 'text-info') }}">
                <span>
                  Today Profit/Loss: Rs. {{ $todayProfitLoss }} ({{ round(($todayProfitLoss/$overall_invested_amount)*100,2) }} %)
                </span>
              </td>
            </tr>
          </tbody>
        </table>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Stocks</th>
              <th>Invested Amount</th>
              <th>Live Price</th>
              <th>Profit/Loss</th>
              <th>Today Profit/Loss</th>
            </tr>
          </thead>
          <tbody>
            @foreach($myPortfolioStocks as $myPortfolio)
              @php
              $p_change = $myPortfolio->p_change;
              $change = $myPortfolio->change;
              $last_price = $myPortfolio->last_price;
              $profit_loss_per = round(($last_price-$myPortfolio->buy_price)/$myPortfolio->buy_price * 100, 2);
              $today_profit_loss_per = round(($change*$myPortfolio->buy_qty)/($myPortfolio->buy_price * $myPortfolio->buy_qty) * 100, 2);
              @endphp
            <tr>
              <td>
                <h4>{{ $myPortfolio->company_name ?? '' }}</h4>
                <h6 class="text-muted">{{ $myPortfolio->symbol }}</h6>
              </td>
              <td>
                Rs. {{ $myPortfolio->last_price * $myPortfolio->buy_qty }}
                <span class="badge {{ $profit_loss_per > 0 ? 'badge-success' : ($profit_loss_per < 0 ? 'badge-danger' : 'badge-info') }}">
                  Rs. {{ $myPortfolio->buy_price * $myPortfolio->buy_qty }}
                </span>
                <h6 class="text-muted">(Qty:{{ $myPortfolio->buy_qty }} x Rs.{{ $myPortfolio->buy_price }})</h6>
              </td>
              <td class="{{ $p_change > 0 ? 'text-success' : ($p_change < 0 ? 'text-danger' : 'text-info') }}">
                {{ $last_price }}
                <p>{{ $myPortfolio->change }} ({{ $myPortfolio->p_change }} %)</p>
              </td>
              <td>
                <span class="{{ $profit_loss_per > 0 ? 'text-success' : ($profit_loss_per < 0 ? 'text-danger' : 'text-info') }}">
                  Rs. {{ abs(($myPortfolio->buy_price * $myPortfolio->buy_qty) - ($last_price * $myPortfolio->buy_qty)) }}
                  <br />
                  ({{ $profit_loss_per }} %)
                </span>
              </td>
              <td>
                <span class="{{ $p_change > 0 ? 'text-success' : ($p_change < 0 ? 'text-danger' : 'text-info') }}">
                  Rs. {{ abs($change * $myPortfolio->buy_qty) }}
                  <br />
                  ({{ $today_profit_loss_per }} %)
                </span>
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