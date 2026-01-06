@extends('include.app_layout')
@section('content')
<div class="app-title">
  <div>
    <h1><i class="fa fa-th-list"></i> Paper Trade</h1>
  </div>
</div>

<div class="row">
  <!-- Add Stock Form -->
  <div class="col-md-12">
    <div class="tile">
      <div class="tile-body">
        <form class="row" action="{{ route('addMyPortfolio') }}" method="post">
          @csrf
          <div class="form-group col-md-3">
            <label for="stock_name" class="control-label">Stock List</label>
            <select class="form-control select2" name="stock_name" id="stock_name">
              <option value="">Select Stock</option>
              @foreach($stock_list as $stock)
                <option value="{{ $stock->symbol }}">
                  {{ $stock->symbol }} - {{ $stock->details->company_name ?? '' }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-md-3">
            <label for="buy_price" class="control-label">Buy Price</label>
            <input class="form-control" type="text" id="buy_price" name="buy_price" placeholder="Enter Buy Price" value="{{ old('buy_price') }}">
          </div>
          <div class="form-group col-md-3">
            <label for="buy_qty" class="control-label">Buy Qty</label>
            <input class="form-control" type="text" id="buy_qty" name="buy_qty" placeholder="Enter Buy Qty" value="{{ old('buy_qty') }}">
          </div>
          <div class="form-group col-md-3">
            <label for="buy_date" class="control-label">Buy Date</label>
            <input class="form-control" type="date" id="buy_date" name="buy_date" value="{{ old('buy_date', date('Y-m-d')) }}">
            <input type="hidden" value="2" name="portfolio_type">
          </div>
          <div class="form-group col-md-3 align-self-end">
            <button class="btn btn-primary" type="submit">
              <i class="fa fa-fw fa-lg fa-check-circle"></i>
              Add to Portfolio
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Portfolio Summary -->
  <div class="col-md-12">
    <div class="tile">
      <div class="table-responsive table-hover table-striped">
        @php
          $overall_invested = 0;
          $overall_live = 0;
          $overall_profit_loss = 0;
          $overall_profit_loss_per = 0;
          $today_profit_loss = 0;
        @endphp

        @foreach($myPortfolioStocks as $stock)
          @php
            $invested = $stock->avg_buy_price * $stock->total_qty;
            $live = $stock->last_price * $stock->total_qty;
            $profit_loss = $live - $invested;

            $overall_invested += $invested;
            $overall_live += $live;
            $overall_profit_loss += $profit_loss;
            $overall_profit_loss_per = $overall_invested ? round(($overall_profit_loss/$overall_invested)*100,2) : 0;

            $today_profit_loss += $stock->change * $stock->total_qty;
          @endphp
        @endforeach

        <table class="table table-bordered text-center">
          <thead>
            <tr>
              <th colspan="2">Overall Profit/Loss</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <span class="{{ $overall_profit_loss_per > 0 ? 'text-success' : ($overall_profit_loss_per < 0 ? 'text-danger' : 'text-info') }}">
                  Total: Rs. {{ $overall_live }}
                </span>
                <br>
                <span class="{{ $overall_profit_loss_per > 0 ? 'text-success' : ($overall_profit_loss_per < 0 ? 'text-danger' : 'text-info') }}">
                  Profit/Loss: Rs. {{ $overall_profit_loss }} ({{ $overall_profit_loss_per }} %)
                </span>
                <br>
                Invested: Rs. {{ $overall_invested }}
              </td>
              <td class="{{ $today_profit_loss > 0 ? 'text-success' : ($today_profit_loss < 0 ? 'text-danger' : 'text-info') }}">
                @if($overall_invested == 0)
                  Today Profit/Loss: Rs. 0 (0 %)
                @else
                  Today Profit/Loss: Rs. {{ $today_profit_loss }} ({{ round(($today_profit_loss/$overall_invested)*100,2) }} %)
                @endif
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Portfolio Table -->
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
            @foreach($myPortfolioStocks as $stock)
              @php
                $invested = $stock->avg_buy_price * $stock->total_qty;
                $live = $stock->last_price * $stock->total_qty;
                $profit_loss = $live - $invested;
                $profit_loss_per = round(($live - $invested)/$invested*100,2);
                $today_profit_loss_stock = ($stock->change * $stock->total_qty);
                $today_profit_loss_per = $invested ? round(($today_profit_loss_stock/$invested)*100,2) : 0;
              @endphp
              <tr>
                <td>
                  <h4>{{ $stock->company_name ?? '' }}</h4>
                  <h6 class="text-muted">
                    <a target="_blank" href="stock-detail-view?stock_name={{ $stock->symbol }}">{{ $stock->symbol }}</a>
                  </h6>
                </td>
                <td>
                  <span class="badge badge-dark ">Rs. {{ $live }}</span>
                  <span class="badge {{ $profit_loss_per > 0 ? 'badge-success' : ($profit_loss_per < 0 ? 'badge-danger' : 'badge-info') }}">
                    Rs. {{ $invested }}
                  </span>
                  <h6>
                    <span class="text-muted badge badge-light">
                      (Qty: {{ $stock->total_qty }} x Rs.{{ $stock->avg_buy_price }})
                    </span>
                  </h6>
                </td>
                <td class="{{ $stock->p_change > 0 ? 'text-success' : ($stock->p_change < 0 ? 'text-danger' : 'text-info') }}">
                  {{ $stock->last_price }}
                  <p>{{ $stock->change }} ({{ $stock->p_change }} %)</p>
                </td>
                <td>
                  <span class="{{ $profit_loss_per > 0 ? 'text-success' : ($profit_loss_per < 0 ? 'text-danger' : 'text-info') }}">
                    Rs. {{ abs($profit_loss) }}<br>({{ $profit_loss_per }} %)
                  </span>
                </td>
                <td>
                  <span class="{{ $today_profit_loss_stock > 0 ? 'text-success' : ($today_profit_loss_stock < 0 ? 'text-danger' : 'text-info') }}">
                    Rs. {{ abs($today_profit_loss_stock) }}<br>({{ $today_profit_loss_per }} %)
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
