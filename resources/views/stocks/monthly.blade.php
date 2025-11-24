@extends('layouts.app')

@section('content')
<div class="container">

    <h2 class="mb-4">Monthly Stock Performance</h2>

    <form method="GET" class="mb-3">
        <label>Select Month:</label>
        <input type="month" name="month" value="{{ $month }}">
        <button class="btn btn-primary btn-sm">Load</button>
    </form>

    <div class="table-responsive" style="max-height: 80vh; overflow:auto;">
        <table class="table table-bordered table-sm">

            <thead class="table-dark">
                <tr>
                    <th>Symbol</th>
                    @foreach ($dates as $date)
                        <th>{{ \Carbon\Carbon::parse($date)->format('d') }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach ($result as $row)
                    <tr>
                        <td><strong>{{ $row['symbol'] }}</strong></td>

                        @foreach ($dates as $date)
                            @php
                                $value = $row[$date] ?? null;
                                $color = $value > 0 ? 'green' : ($value < 0 ? 'red' : 'black');
                            @endphp

                            <td style="color: {{ $color }}">
                                {{ $value !== null ? $value.'%' : '-' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>

</div>
@endsection
