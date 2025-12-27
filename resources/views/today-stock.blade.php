@extends('include.app_layout')
@section('content')
<div class="app-title">
  <div>
    <h1><i class="fa fa-th-list"></i> Basic Tables</h1>
    <p>Basic bootstrap tables</p>
  </div>
  <ul class="app-breadcrumb breadcrumb">
    <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
    <li class="breadcrumb-item">Tables</li>
    <li class="breadcrumb-item active"><a href="#">Simple Tables</a></li>
  </ul>
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
            <td>{{ $dataList->symbol }}</td>
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
      <h3 class="tile-title">Today Missed Stock</h3>
      <table class="table table-striped">
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
            <td>{{ $dataList->symbol }}</td>
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
            <td>{{ $dataList->symbol }}</td>
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
@endsection