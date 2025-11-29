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
      <h3 class="tile-title">Holiday List</h3>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Week Day</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>
          @foreach($holidays as $holiday)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $holiday->date }}</td>
            <td>{{ $holiday->week_day }}</td>
            <td>{{ $holiday->description }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection