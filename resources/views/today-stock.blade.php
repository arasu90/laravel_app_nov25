@extends('include.app_layout')
@section('content')
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
      <h3 class="tile-title">Today Missed Stock</h3>
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
            <td><a href="update-stock-data/{{$dataList->symbol}}" target="_blank">{{ $dataList->symbol }}</a></td>
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
