@extends('include.app_layout')
@section('content')
<div class="app-title">
  <div>
    <h1><i class="fa fa-th-list"></i> Applications URLs</h1>
  </div>
</div>
<div class="col-md-12">
  <div class="tile">
    <h3 class="tile-title">App URL</h3>
    <div class="table-responsive table-hover table-striped">
      <table class="table table-striped table-bordered">
        <th>Name</th>
        <th>URL</th>
        <tbody>
          <tr>
            <td>
              Get Insert all Stock Day Records
            </td>
            <td>
              <a href="/trigger-stock-update" target="_blank">{{ url('/') }}/trigger-stock-update</a>
            </td>
          </tr>
          <tr>
            <td>
              Get Insert single stock day records
            </td>
            <td>
              <a href="/update-stock-data/TNTELE" target="_blank">{{ url('/') }}/update-stock-data/{symbol}</a>
            </td>
          </tr>
          <tr>
            <td>
              Insert all stock day json records
            </td>
            <td>
              <a href="/insert-stock-daily-data" target="_blank">{{ url('/') }}/insert-stock-daily-data</a>
            </td>
          </tr>
          <tr>
            <td>
              Generate Insert Query all Stocks
            </td>
            <td>
              <a href="/db-query" target="_blank">{{ url('/') }}/db-query</a>
            </td>
          </tr>
          <tr>
            <td>
              Generate Insert Corporate Info
            </td>
            <td>
              <a href="/get-corporate-info" target="_blank">{{ url('/') }}/get-corporate-info</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="table-responsive table-hover table-striped">
      <h3 class="tile-title">API URL</h3>
      <table class="table table-striped table-bordered">
        <th>Name</th>
        <th>URL</th>
        <tbody>
          <tr>
            <td>
              Get Stock Day Data
            </td>
            <td>
              <a href="/api/stock/TNTELE" target="_blank">{{ url('/') }}/api/stock/{stock}</a>
            </td>
          </tr>
          <tr>
            <td>
              Market Status
            </td>
            <td>
              <a href="/api/market-status" target="_blank">{{ url('/') }}/api/market-status</a>
            </td>
          </tr>
          <tr>
            <td>
              Stock Historical Data
            </td>
            <td>
              <a href="/api/stock/TNTELE/historical" target="_blank">{{ url('/') }}/api/stock/{symbol}/historical</a>
            </td>
          </tr>
          <tr>
            <td>
              Get All Indices Data
            </td>
            <td>
              <a href="/api/indices" target="_blank">{{ url('/') }}/api/indices</a>
            </td>
          </tr>
          <tr>
            <td>
              Get All Stocks & Insert
            </td>
            <td>
              <a href="/api/all-stocks" target="_blank">{{ url('/') }}/api/all-stocks</a>
            </td>
          </tr>
          <tr>
            <td>
              Get Holidays
            </td>
            <td>
              <a href="/api/holidays" target="_blank">{{ url('/') }}/api/holidays</a>
            </td>
          </tr>
          <tr>
            <td>
              Get Stock Corporate Information
            </td>
            <td>
              <a href="/api/corporate-info/TNTELE" target="_blank">{{ url('/') }}/api/corporate-info/{symbol}</a>
            </td>
          </tr>
          <tr>
            <td>
              Get all-index-names List
            </td>
            <td>
              <a href="/api/all-index-names" target="_blank">{{ url('/') }}/api/all-index-names</a>
            </td>
          </tr>
          <tr>
            <td>
              Get equity-master
            </td>
            <td>
              <a href="/api/equity-master" target="_blank">{{ url('/') }}/api/equity-master</a>
            </td>
          </tr>
          <tr>
            <td>
              Get circular
            </td>
            <td>
              <a href="/api/circulars" target="_blank">{{ url('/') }}/api/circular</a>
            </td>
          </tr>
          <tr>
            <td>
              Get historical-data/{symbol}
            </td>
            <td>
              <a href="/api/historical-data/TNTELE" target="_blank">{{ url('/') }}/api/historical-data/{symbol}</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
