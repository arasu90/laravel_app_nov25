<div class="col-md-6">
    <div class="tile">

        <h3 class="tile-title d-flex text-{{ $type }} justify-content-between align-items-center">

            <span>
                <a class="d-block"
                   data-toggle="collapse"
                   href="#{{ $collapseId }}"
                   role="button"
                   aria-expanded="true"
                   aria-controls="{{ $collapseId }}">

                    {{ $title }}
                </a>
            </span>

            <a class="collapse-icon collapsed"
               data-toggle="collapse"
               href="#{{ $collapseId }}"
               role="button"
               aria-expanded="false"
               aria-controls="{{ $collapseId }}">

                <i class="fa fa-chevron-up"></i>
            </a>

        </h3>

        <div class="collapse" id="{{ $collapseId }}">

            <table class="table table-striped">
                <tbody>

                @foreach($stocks as $stockList)

                    <tr>
                        <td>

                            <span class="float-right">

                                <span class="text-{{ $type }} float-right">
                                    {{ $stockList->last_price }}
                                </span>

                                <br />

                                <span class="badge badge-{{ $type }}">
                                    {{ $stockList->change }}
                                    ({{ $stockList->p_change }} %)
                                </span>

                            </span>

                            {{ $stockList->company_name }}

                            <br />

                            <small class="text-muted">
                                <a target="_blank"
                                   href="stock-detail-view?stock_name={{ $stockList->symbol }}">

                                    {{ $stockList->symbol }}
                                </a>
                            </small>

                        </td>
                    </tr>

                @endforeach

                </tbody>
            </table>

        </div>

    </div>
</div>