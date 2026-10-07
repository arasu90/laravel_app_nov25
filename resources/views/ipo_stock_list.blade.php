@extends('include.app_layout')

@section('content')
<div class="app-title">
  <div>
    <h1><i class="fa fa-th-list"></i> IPO Stock List</h1>
  </div>
</div>

@php
  $ipoLists = [
    'active' => ['title' => 'Active', 'stocks' => $activeIpoStockList],
    'listing' => ['title' => 'Listing', 'stocks' => $listingIpoStockList],
    'closed' => ['title' => 'Closed', 'stocks' => $closedIpoStockList],
  ];
@endphp

<div class="tile">
  <div class="tile-body">
    <ul class="nav nav-tabs mb-3" role="tablist">
      @foreach($ipoLists as $key => $ipoList)
        <li class="nav-item">
          <a class="nav-link {{ $loop->first ? 'active' : '' }}"
             id="ipo-{{ $key }}-tab"
             data-toggle="tab"
             href="#ipo-{{ $key }}"
             role="tab"
             aria-controls="ipo-{{ $key }}"
             aria-selected="{{ $loop->first ? 'true' : 'false' }}">
            {{ $ipoList['title'] }} ({{ $ipoList['stocks']->count() }})
          </a>
        </li>
      @endforeach
    </ul>

    <div class="tab-content">
      @foreach($ipoLists as $key => $ipoList)
        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
             id="ipo-{{ $key }}"
             role="tabpanel"
             aria-labelledby="ipo-{{ $key }}-tab">
          <div class="table-responsive">
            <table class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>Company</th>
                  <th>Symbol</th>
                  <th>Type</th>
                  <th>Issue Start</th>
                  <th>Issue End</th>
                  <th>Issue Price Range</th>
                  <th>Issue Price</th>
                  @if($key === 'closed')
                    <th>Live Price</th>
                  @endif
                  <th>Listing Date</th>
                </tr>
              </thead>
              <tbody>
                @forelse($ipoList['stocks'] as $stock)
                  <tr>
                    <td>{{ $stock->symbol_name }}</td>
                    <td>
                      @if($stock->status === 'Closed')
                        <a href="{{ route('stockDetailView', ['stock_name' => $stock->symbol]) }}" target="_blank" rel="noopener noreferrer">{{ $stock->symbol }}</a>
                      @else
                        {{ $stock->symbol }}
                      @endif
                    </td>
                    <td>{{ $stock->security_type }}</td>
                    <td>{{ $stock->issue_start_date ?? '-' }}</td>
                    <td>{{ $stock->issue_end_date ?? '-' }}</td>
                    <td>{{ $stock->issue_price_range ?? '-' }}</td>
                    <td>{{ $stock->issue_price ?? '-' }}</td>
                    @if($key === 'closed')
                      @php  
                        $livePriceBadge = !is_numeric($stock->live_price)
                          ? 'badge-secondary'
                          : (!is_numeric($stock->issue_price)
                            ? 'badge-secondary'
                            : ($stock->live_price > $stock->issue_price ? 'badge-success' : 'badge-danger'));
                      @endphp
                      <td><span class="badge {{ $livePriceBadge }}">{{ $stock->live_price ?? '-' }}</span></td>
                    @endif
                    <td>{{ $stock->date_of_listing ?? '-' }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="{{ $key === 'closed' ? 9 : 8 }}" class="text-center">No {{ strtolower($ipoList['title']) }} IPOs found.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
@endsection
