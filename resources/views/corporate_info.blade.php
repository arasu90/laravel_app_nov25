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
      <h3 class="tile-title">Corporate Action List</h3>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Symbol</th>
            <th>Date</th>
            <th>Purpose</th>
          </tr>
        </thead>
        <tbody>
          @foreach($corporateInfo as $infoData)
          <tr>
            <td>{{ $infoData->company_name }} 
              <br />
              <small>{{ $infoData->symbol }}</small>
            </td>
            <td>{{ date("d-m-Y", strtotime($infoData->actions_date)) }}</td>
            <td>{{ $infoData->actions_purpose }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection