@extends('include.app_layout')
@section('content')
<div class="app-title">
  <div>
    <h1><i class="fa fa-th-list"></i> Last Few Days Stock</h1>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="tile">
      <h3 class="tile-title">Today Upper CP</h3>
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Symbol</th>
            <th>Price</th>
            <th>UpperCP</th>
            <th>52WeekHigh</th>
          </tr>
        </thead>
        <tbody>
          @forelse($todayHitUpperCP as $listData)
          <tr class="{{ $listData->p_change > 0
                ? 'text-success'
                : ($listData->p_change < 0
                  ? 'text-danger'
                  : 'text-info')
                }}">
            <td>{{ $loop->iteration }}</td>
            <td>
              <p>{{ $listData-> company_name }}</p>
              <a target="_blank" href="stock-detail-view?stock_name={{ $listData->symbol }}">{{ $listData->symbol }}</a>
            </td>
            <td>
              {{ $listData->last_price }}
              @if($listData->last_price == $listData->week_high_low_max)
                <i class="fa fa-bookmark"></i>
              @endif
              <p>{{ $listData->change }} ({{ $listData->p_change }} %)</p>
            </td>
            <td>{{ $listData->upper_cp }}</td>
            <td>
              {{ $listData->week_high_low_max }}
              <p>{{ $listData->week_high_low_max_date }}</p>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5"><p>No stocks found</p></td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="tile">
      <h3 class="tile-title">Today Lower CP</h3>
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Symbol</th>
            <th>Price</th>
            <th>LowerCP</th>
            <th>52WeekLow</th>
          </tr>
        </thead>
        <tbody>
          @forelse($todayHitLowerCP as $listData)
          <tr class="{{ $listData->p_change > 0
                ? 'text-success'
                : ($listData->p_change < 0
                  ? 'text-danger'
                  : 'text-info')
                }}">
            <td>{{ $loop->iteration }}</td>
            <td>
              <p>{{ $listData-> company_name }}</p>
              <a target="_blank" href="stock-detail-view?stock_name={{ $listData->symbol }}">{{ $listData->symbol }}</a>
            </td>
            <td>
              {{ $listData->last_price }}
              @if($listData->last_price == $listData->week_high_low_min)
                <i class="fa fa-bookmark"></i>
              @endif
              <p>{{ $listData->change }} ({{ $listData->p_change }} %)</p>
            </td>
            <td>{{ $listData->lower_cp }}</td>
            <td>
              {{ $listData->week_high_low_min }}
              <p>{{ $listData->week_high_low_min_date }}</p>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5"><p>No stocks found</p></td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="tile">
      <h3 class="tile-title">Last Few Days(3) Upper CP</h3>
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Symbol</th>
            @foreach($lastFewDaysUpperCPDate as $dates)
            <th> {{ $dates }} </th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @forelse($lastFewDaysUpperCP as $key=>$dates)
          <tr class="text-success">
            <td>{{ $loop->iteration }}</td>
            <td><a target="_blank" href="stock-detail-view?stock_name={{ $key }}">{{ $key }}</a></td>
            @foreach($dates as $date => $dataList)
              <td>
                <p>{{ $dataList->last_price }}</p>
                <p>{{ $dataList->change }} ({{ $dataList->p_change }}%)</p>
              </td>
            @endforeach
          </tr>
          @empty
          <tr>
            <td colspan="7"><p>No stocks found</p></td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="tile">
      <h3 class="tile-title">Last Few Days(3) Lower CP</h3>
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Symbol</th>
            @foreach($lastFewDaysLowerCPDate as $dates)
            <th> {{ $dates }} </th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @forelse($lastFewDaysLowerCP as $key=>$dates)
          <tr class="text-danger">
            <td>{{ $loop->iteration }}</td>
            <td><a target="_blank" href="stock-detail-view?stock_name={{ $key }}">{{ $key }}</a></td>
            @foreach($dates as $date => $dataList)
              <td>
                <p>{{ $dataList->last_price }}</p>
                <p>{{ $dataList->change }} ({{ $dataList->p_change }}%)</p>
              </td>
            @endforeach
          </tr>
          @empty
          <tr>
            <td colspan="7"><p>No stocks found</p></td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="tile">
      <h3 class="tile-title">Last 5 days Gainer</h3>
      <table class="table table-striped table-hover">
        <thead>
          <tr class=" table-success">
            <th>#</th>
            <th>Symbol</th>
            @foreach($lastFewGainerDates as $dates)
            <th> {{ $dates }} </th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @forelse($lastFewDaysGainer as $key=>$dates)
          <tr class="text-success">
            <td>{{ $loop->iteration }}</td>
            <td><a target="_blank" href="stock-detail-view?stock_name={{ $key }}">{{ $key }}</a></td>
            @foreach($dates as $date => $dataList)
                <td>
                  <p>{{ $dataList->last_price }}</p>
                  <p>{{ $dataList->change }} ({{ $dataList->p_change }}%)</p>
                </td>
                @endforeach
              </tr>
          @empty
          <tr>
            <td colspan="5"><p>No stocks found</p></td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="tile">
      <h3 class="tile-title">Last 5 days Losser</h3>
      <table class="table table-striped table-hover">
        <thead>
          <tr class="table-danger">
            <th>#</th>
            <th>Symbol</th>
            @foreach($lastFewLosserDates as $dates)
            <th> {{ $dates }} </th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @forelse($lastFewDaysLoser as $key=>$dates)
          <tr class="text-danger">
            <td>{{ $loop->iteration }}</td>
            <td>
              <a target="_blank" href="stock-detail-view?stock_name={{ $key }}">
                {{ $key }}
              </a>
            </td>
            @foreach($dates as $date => $dataList)
            <td>
              <p>{{ $dataList->last_price }}</p>
              <p>{{ $dataList->change }} ({{ $dataList->p_change }}%)</p>
            </td>
            @endforeach
          </tr>
          @empty
          <tr>
            <td colspan="4"><p>No stocks found</p></td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection