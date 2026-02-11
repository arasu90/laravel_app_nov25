@extends('include.app_layout')
@section('content')
@php
use Carbon\Carbon;
@endphp
<div class="app-title">
  <div>
    <h1><i class="fa fa-th-list"></i> Today Stock Details</h1>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="tile">
      <h3 class="tile-title">Today Added Stock ({{ date("d M Y", strtotime($today)) }})</h3>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Symbol</th>
            <th>Added Date</th>
          </tr>
        </thead>
        <tbody>
          @foreach($todayAddedStock as $dataList)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td><a target="_blank" href="stock-detail-view?stock_name={{ $dataList->symbol }}">{{ $dataList->symbol }}</a></td>
            <td>{{ $dataList->created_at }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="tile">
      <h3 class="tile-title">Recent Stock Added</h3>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Symbol</th>
            <th>Added Date</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentAddedStock as $dataList)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td><a target="_blank" href="stock-detail-view?stock_name={{ $dataList->symbol }}">{{ $dataList->symbol }}</a></td>
            <td>{{ $dataList->created_at }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="3"><p>No stocks found</p></td>
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
      <h3 class="tile-title">Today Missed Stock ({{ count($todayMissedStock) }})</h3>
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Symbol</th>
            <th>Added Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($todayMissedStock as $dataList)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td><a target="_blank" href="stock-detail-view?stock_name={{ $dataList->symbol }}">{{ $dataList->symbol }}</a></td>
            <td>{{ $dataList->created_at }}</td>
            <td>
              <a href="update-stock-data/{{$dataList->symbol}}" target="_blank">
                {{ $dataList->symbol }}
              </a>
              <a href="inactive-stocks-web/{{$dataList->symbol}}" class="btn btn-link btn-sm text-danger">
                <i class="fa fa-trash-o"></i>
              </a>
            </td>
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
<div class="row">
  <div class="col-md-12">
    <div class="tile">
      <h3 class="tile-title">Recent Suspended Stock</h3>
      <table class="table table-responsive table-striped table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th width="10%">Symbol</th>
            <th>Stock Name</th>
            <th>Stock Status</th>
            <th>Trading Status</th>
            <th>Surveillance Surv</th>
            <th width="20%">Surveillance Desc</th>
            <th>Updated On</th>
            <th width="10%">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentSuspendedStock as $dataList)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
              <span class="badge badge-danger">
                {{
                  Carbon::parse($dataList->last_update_time)
                    ->startOfDay()
                    ->diffInDays(Carbon::parse($today))
                }}
              </span>
              <a target="_blank" href="stock-detail-view?stock_name={{ $dataList->symbol }}">{{ $dataList->symbol }}</a></td>
            <td>{{ $dataList->company_name }}</td>
            <td>{{ $dataList->status }}</td>
            <td>{{ $dataList->trading_status }}</td>
            <td>{{ $dataList->surveillance_surv }}</td>
            <td>{{ $dataList->surveillance_desc }}</td>
            <td>{{ $dataList->last_update_time }}</td>
            <td>
              <a href="update-stock-data/{{$dataList->symbol}}" target="_blank">
                {{ $dataList->symbol }}
              </a>
              <a href="inactive-stocks-web/{{$dataList->symbol}}" class="btn btn-link btn-sm text-danger">
                <i class="fa fa-trash-o"></i>
              </a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="9"><p>No stocks found</p></td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
