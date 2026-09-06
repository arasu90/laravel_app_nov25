@foreach ($indicators as $indicatorKey => $indicator)
  @if ($value == $indicator['value'] && ($key !== $indicatorKey || $key === 'last_price'))
  <i class="fa {{ $indicator['icon'] }}"></i>
  @endif
@endforeach
