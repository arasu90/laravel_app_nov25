@extends('include.app_layout')
@section('content')
<div class="app-title">
  <div>
    <h1><i class="fa fa-th-list"></i> My Portfolio</h1>
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
            <label for="account_type" class="control-label">Account</label>
            <select class="form-control select2" name="account_type" id="account_type" required>
              <option value="">Select Account</option>
              @foreach($account_list as $account)
                <option value="{{ $account->id }}" @selected(old('account_type', $account_list->first()?->id) == $account->id)>
                  {{ $account->name }}
                </option>
              @endforeach
            </select>
            <input type="hidden" name="portfolio_type" value="1">
          </div>
          <div class="form-group col-md-3">
            <label for="stock_name" class="control-label">Stock List</label>
            <select class="form-control select2" name="stock_name" id="stock_name" onchange="showSelectedStockLivePrice()">
              <option value="">Select Stock</option>
              @foreach($stock_list as $stock)
                <option value="{{ $stock->symbol }}"
                        data-live-price="{{ $stock->details?->stock_last_price ?? '' }}"
                        @selected(old('stock_name') === $stock->symbol)>
                  {{ $stock->symbol }} - {{ $stock->details->company_name ?? '' }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-md-3">
            <label for="buy_qty" class="control-label">Buy Qty</label>
            <input class="form-control" type="text" id="buy_qty" name="buy_qty" placeholder="Enter Buy Qty" value="{{ old('buy_qty') }}">
          </div>
          <div class="form-group col-md-3">
            <label for="buy_price" class="control-label">Buy Price</label>
            <input class="form-control" type="text" id="buy_price" name="buy_price" placeholder="Enter Buy Price" value="{{ old('buy_price') }}">
            <small class="text-muted" id="selected-stock-live-price">Select a stock to see its live price.</small>
          </div>
          <div class="form-group col-md-3">
            <label for="buy_date" class="control-label">Buy Date</label>
            <input class="form-control" type="date" id="buy_date" name="buy_date" value="{{ old('buy_date', date('Y-m-d')) }}">
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
      <div class="table-responsive table-hover table-striped" data-portfolio-tabs>
        @if($account_list->isEmpty())
          <p>No active accounts are available. Add an account to view portfolio tabs.</p>
        @else
        <ul class="nav nav-tabs mb-3" role="tablist">
          @foreach($account_list as $index => $account)
            <li class="nav-item">
              <a class="nav-link {{ $index === 0 ? 'active' : '' }}"
                 id="portfolio-account-{{ $account->id }}-tab"
                 href="#portfolio-account-{{ $account->id }}"
                 data-portfolio-tab
                 role="tab"
                 aria-controls="portfolio-account-{{ $account->id }}"
                 aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                {{ $account->name }}
              </a>
            </li>
          @endforeach
        </ul>

        <div class="tab-content">
          @foreach($account_list as $index => $account)
            @php
              $accountStocks = $myPortfolioStocks->get($account->id, collect());
            @endphp
            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                 id="portfolio-account-{{ $account->id }}"
                 role="tabpanel"
                 aria-labelledby="portfolio-account-{{ $account->id }}-tab">
        @php
          $overall_invested = 0;
          $overall_live = 0;
          $overall_profit_loss = 0;
          $overall_profit_loss_per = 0;
          $today_profit_loss = 0;
          $previous_close_value = 0;
          $has_missing_prices = false;
        @endphp

        @foreach($accountStocks as $stock)
          @php
            $overall_invested += $stock->invested_amount;
            if ($stock->last_price === null) {
              $has_missing_prices = true;
            } else {
              $overall_live += $stock->live_value;
              $overall_profit_loss += $stock->overall_profit_loss;
              $today_profit_loss += $stock->today_profit_loss;
              $previous_close_value += $stock->previous_close_value;
            }
          @endphp
        @endforeach
        @php
          $overall_profit_loss_per = $overall_invested
            ? round(($overall_profit_loss / $overall_invested) * 100, 2)
            : 0;
          $today_profit_loss_per = $previous_close_value
            ? round(($today_profit_loss / $previous_close_value) * 100, 2)
            : 0;
        @endphp

        <table class="table table-bordered text-center">
          <thead>
            <tr>
              <th colspan="2">{{ $account->name }} - Overall &amp; Latest Daily Performance</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <span class="{{ $has_missing_prices ? 'text-info' : ($overall_profit_loss_per > 0 ? 'text-success' : ($overall_profit_loss_per < 0 ? 'text-danger' : 'text-info')) }}">
                  Current Value: {{ $has_missing_prices ? 'Unavailable (missing prices)' : 'Rs. ' . number_format($overall_live, 2) }}
                </span>
                <br>
                <span class="{{ $has_missing_prices ? 'text-info' : ($overall_profit_loss_per > 0 ? 'text-success' : ($overall_profit_loss_per < 0 ? 'text-danger' : 'text-info')) }}">
                  Overall Profit/Loss:
                  {{ $has_missing_prices ? 'Unavailable (missing prices)' : 'Rs. ' . number_format($overall_profit_loss, 2) . ' (' . $overall_profit_loss_per . ' %)' }}
                </span>
                <br>
                Invested: Rs. {{ number_format($overall_invested, 2) }}
              </td>
              <td class="{{ $has_missing_prices ? 'text-info' : ($today_profit_loss > 0 ? 'text-success' : ($today_profit_loss < 0 ? 'text-danger' : 'text-info')) }}">
                Latest Daily Profit/Loss:
                {{ $has_missing_prices ? 'Unavailable (missing prices)' : 'Rs. ' . number_format($today_profit_loss, 2) . ' (' . $today_profit_loss_per . ' %)' }}
                <br>
                <small class="text-muted">Uses the latest available daily record for each stock.</small>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Portfolio Table -->
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Stocks</th>
              <th>Average Buy / Invested</th>
              <th>Latest Price / Holding Value</th>
              <th>Overall P/L vs. Average Buy</th>
              <th>Latest Daily P/L</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($accountStocks as $stock)
              @php
                $invested = $stock->invested_amount;
                $live = $stock->live_value;
                $profit_loss = $stock->overall_profit_loss;
                $profit_loss_per = $invested && $profit_loss !== null
                  ? round(($profit_loss / $invested) * 100, 2)
                  : 0;
                $today_profit_loss_stock = $stock->today_profit_loss;
                $today_profit_loss_per = $stock->previous_close_value
                  ? round(($today_profit_loss_stock / $stock->previous_close_value) * 100, 2)
                  : 0;
              @endphp
              <tr>
                <td>
                  <h4>{{ $stock->company_name ?? '' }}</h4>
                  <h6 class="text-muted">
                    <a target="_blank" href="stock-detail-view?stock_name={{ $stock->symbol }}">{{ $stock->symbol }}</a>
                  </h6>
                </td>
                <td>
                  Average buy: Rs. {{ number_format($stock->avg_buy_price, 2) }}
                  <br>
                  Invested:
                  <span class="badge badge-dark">Rs. {{ number_format($invested, 2) }}</span>
                  <h6>
                    <span class="text-muted badge badge-light">
                      Qty: {{ $stock->total_qty }}
                    </span>
                  </h6>
                </td>
                <td class="{{ $stock->p_change > 0 ? 'text-success' : ($stock->p_change < 0 ? 'text-danger' : 'text-info') }}">
                  @if($stock->last_price === null)
                    Unavailable
                  @else
                    Price: Rs. {{ number_format($stock->last_price, 2) }}
                    <p>Daily change: {{ number_format($stock->change, 2) }} ({{ number_format($stock->p_change, 2) }}%)</p>
                    <small class="text-muted">Price date: {{ $stock->price_date }}</small>
                    <span class="badge {{ $profit_loss > 0 ? 'badge-success' : ($profit_loss < 0 ? 'badge-danger' : 'badge-info') }}">
                      Holding Value: Rs. {{ number_format($live, 2) }}
                    </span>
                  @endif
                </td>
                <td>
                  @if($profit_loss === null)
                    Unavailable
                  @else
                    <span class="{{ $profit_loss_per > 0 ? 'text-success' : ($profit_loss_per < 0 ? 'text-danger' : 'text-info') }}">
                      Rs. {{ number_format($profit_loss, 2) }}<br>({{ $profit_loss_per }} %)
                    </span>
                  @endif
                </td>
                <td>
                  @if($today_profit_loss_stock === null)
                    Unavailable
                  @else
                    <span class="{{ $today_profit_loss_stock > 0 ? 'text-success' : ($today_profit_loss_stock < 0 ? 'text-danger' : 'text-info') }}">
                      Rs. {{ number_format($today_profit_loss_stock, 2) }}<br>({{ $today_profit_loss_per }} %)
                    </span>
                    <br>
                    <small class="text-muted">as of {{ $stock->price_date }}</small>
                  @endif
                </td>
                <td>
                  <button type="button"
                          class="btn btn-sm btn-info"
                          data-toggle="modal"
                          data-target="#portfolio-lots-{{ $account->id }}-{{ $loop->index }}">
                    View records
                  </button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
            </div>
          @endforeach
        </div>
        @endif
      </div>
      @foreach($account_list as $account)
        @foreach($myPortfolioStocks->get($account->id, collect()) as $stockIndex => $stock)
          @php
            $stockLots = $portfolioLots->get($account->id, collect())->get($stock->symbol, collect());
          @endphp
          <div class="modal fade"
               id="portfolio-lots-{{ $account->id }}-{{ $stockIndex }}"
               tabindex="-1"
               role="dialog"
               aria-labelledby="portfolio-lots-title-{{ $account->id }}-{{ $stockIndex }}"
               aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <div class="d-flex align-items-center justify-content-between w-100 mr-3">
                    <h5 class="modal-title" id="portfolio-lots-title-{{ $account->id }}-{{ $stockIndex }}">
                      {{ $stock->company_name ?? '' }}
                      <small class="d-block text-muted">{{ $stock->symbol }}</small>
                    </h5>
                    <div class="text-right {{ $stock->p_change > 0 ? 'text-success' : ($stock->p_change < 0 ? 'text-danger' : 'text-info') }}">
                      <strong>
                        Live Price:
                        {{ $stock->live_price === null ? 'Unavailable' : 'Rs. ' . number_format($stock->live_price, 2) }}
                      </strong>
                      <small class="d-block">
                        {{ $stock->change === null ? 'Change unavailable' : number_format($stock->change, 2) . ' (' . number_format($stock->p_change, 2) . '%)' }}
                      </small>
                    </div>
                  </div>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <strong>Current Holding Value:</strong>
                      @if($stock->live_value === null)
                        <span class="text-info">Unavailable</span>
                      @else
                        Rs. {{ number_format($stock->live_value, 2) }}
                      @endif
                    </div>
                    <div class="col-md-4">
                      <strong>Overall P/L:</strong>
                      @if($stock->overall_profit_loss === null)
                        <span class="text-info">Unavailable</span>
                      @else
                        <span class="{{ $stock->overall_profit_loss > 0 ? 'text-success' : ($stock->overall_profit_loss < 0 ? 'text-danger' : 'text-info') }}">
                          Rs. {{ number_format($stock->overall_profit_loss, 2) }}
                          ({{ $stock->invested_amount ? round(($stock->overall_profit_loss / $stock->invested_amount) * 100, 2) : 0 }}%)
                        </span>
                      @endif
                    </div>
                    <div class="col-md-4">
                      <strong>Latest Daily P/L:</strong>
                      @if($stock->today_profit_loss === null)
                        <span class="text-info">Unavailable</span>
                      @else
                        <span class="{{ $stock->today_profit_loss > 0 ? 'text-success' : ($stock->today_profit_loss < 0 ? 'text-danger' : 'text-info') }}">
                          Rs. {{ number_format($stock->today_profit_loss, 2) }}
                          ({{ $stock->previous_close_value ? round(($stock->today_profit_loss / $stock->previous_close_value) * 100, 2) : 0 }}%)
                        </span>
                        <small class="d-block text-muted">as of {{ $stock->price_date }}</small>
                      @endif
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                      <thead>
                        <tr>
                          <th>Buy Date</th>
                          <th>Quantity</th>
                          <th>Buy Price</th>
                          <th>Invested Amount</th>
                          <th>Overall P/L</th>
                          <th>Latest Daily P/L</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($stockLots as $lot)
                          @php
                            $lotInvested = $lot->buy_qty * $lot->buy_price;
                            $lotOverallProfitLoss = $stock->last_price !== null
                              ? ($stock->last_price - $lot->buy_price) * $lot->buy_qty
                              : null;
                            $lotOverallProfitLossPer = $lotOverallProfitLoss !== null && $lotInvested
                              ? round(($lotOverallProfitLoss / $lotInvested) * 100, 2)
                              : 0;
                            $lotDailyProfitLoss = $stock->change !== null
                              ? $stock->change * $lot->buy_qty
                              : null;
                          @endphp
                          <tr>
                            <td>
                              {{ $lot->buy_date }}
                              <form action="{{ route('deletePortfolioRecord', ['accountType' => $account->id, 'record' => $lot->id]) }}"
                                    method="post"
                                    onsubmit="return confirm('Delete this portfolio purchase record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-sm btn-danger mt-1"
                                        aria-label="Delete purchase record"
                                        title="Delete purchase record">
                                  <i class="fa fa-trash" aria-hidden="true"></i>
                                </button>
                              </form>
                            </td>
                            <td>{{ $lot->buy_qty }}</td>
                            <td>Rs. {{ number_format($lot->buy_price, 2) }}</td>
                            <td>Rs. {{ number_format($lotInvested, 2) }}</td>
                            <td>
                              @if($lotOverallProfitLoss === null)
                                Unavailable
                              @else
                                <span class="{{ $lotOverallProfitLoss > 0 ? 'text-success' : ($lotOverallProfitLoss < 0 ? 'text-danger' : 'text-info') }}">
                                  Rs. {{ number_format($lotOverallProfitLoss, 2) }}
                                  ({{ $lotOverallProfitLossPer }}%)
                                </span>
                              @endif
                            </td>
                            <td>
                              @if($lotDailyProfitLoss === null)
                                Unavailable
                              @else
                                <span class="{{ $lotDailyProfitLoss > 0 ? 'text-success' : ($lotDailyProfitLoss < 0 ? 'text-danger' : 'text-info') }}">
                                  Rs. {{ number_format($lotDailyProfitLoss, 2) }}
                                  ({{ number_format($stock->p_change, 2) }}%)
                                </span>
                                <small class="d-block text-muted">as of {{ $stock->price_date }}</small>
                              @endif
                            </td>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="6" class="text-center">No purchase records found.</td>
                          </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      @endforeach
    </div>
  </div>
</div>
  <script>
    function showSelectedStockLivePrice() {
      var stockSelect = document.getElementById('stock_name');
      var livePriceLabel = document.getElementById('selected-stock-live-price');
      var selectedOption = stockSelect ? stockSelect.querySelector('option:checked') : null;
      var livePrice = selectedOption ? selectedOption.getAttribute('data-live-price') : null;
      var parsedLivePrice = livePrice === null || livePrice === '' ? NaN : Number(livePrice);

      livePriceLabel.textContent = Number.isFinite(parsedLivePrice)
        ? 'Latest live price: Rs. ' + parsedLivePrice.toFixed(2)
        : 'Live price unavailable.';
    }

    showSelectedStockLivePrice();

    document.querySelectorAll('[data-portfolio-tabs]').forEach(function (tabGroup) {
      var tabs = tabGroup.querySelectorAll('[data-portfolio-tab]');
      var panels = tabGroup.querySelectorAll('.tab-pane');

      tabs.forEach(function (tab) {
        tab.addEventListener('click', function (event) {
          event.preventDefault();

          var panel = document.getElementById(tab.getAttribute('href').slice(1));
          if (!panel || !tabGroup.contains(panel)) {
            return;
          }

          tabs.forEach(function (item) {
            item.classList.remove('active');
            item.setAttribute('aria-selected', 'false');
          });
          panels.forEach(function (item) {
            item.classList.remove('show', 'active');
          });

          tab.classList.add('active');
          tab.setAttribute('aria-selected', 'true');
          panel.classList.add('show', 'active');
        });
      });
    });
  </script>
  @endsection
