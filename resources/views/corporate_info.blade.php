@extends('include.app_layout')
@section('content')
<div class="app-title">
  <div>
    <h1><i class="fa fa-th-list"></i> Corporate Action List</h1>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="tile">
      <table class="table table-striped" id="corporateInfo">
        <thead>
          <tr>
            <th class="col-symbol">Symbol</th>
            <th class="col-date">Date</th>
            <th class="col-purpose">Purpose</th>
          </tr>
        </thead>
        <tbody>
          @foreach($corporateInfo as $infoData)
          <tr>
            <td>{{ $infoData->company_name }}
              <br />
              <small><a href="stock-detail-view?stock_name={{ $infoData->symbol }}">{{ $infoData->symbol }}</a></small>
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
