<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
<aside class="app-sidebar">
    <div class="app-sidebar__user"><img style="width:30%" class="app-sidebar__user-avatar" src="https://fastly.picsum.photos/id/132/200/200.jpg?hmac=meVrCoOURNB7iKK3Mv-yuRrvxvXgv4h2vIRLM4sKwK4" alt="User Image">
    <div>
        <p class="app-sidebar__user-name">Kalaiarasu</p>
        <p class="app-sidebar__user-designation">Software Developer</p>
    </div>
    </div>
    <ul class="app-menu">
        <li>
            <a class="app-menu__item {{ Route::is('dashboard') ? 'active' : '' }} " href="/">
                <i class="app-menu__icon fa fa-dashboard"></i>
                <span class="app-menu__label">Dashboard</span>
            </a>
        </li>
        <li>
            <a class="app-menu__item {{ Route::is('stockListTableView') ? 'active' : '' }} " href="/stock-table">
                <i class="app-menu__icon fa fa-home"></i>
                <span class="app-menu__label">Stock Table</span>
            </a>
        </li>
        <li>
            <a class="app-menu__item {{ Route::is('oneDayView') ? 'active' : '' }} " href="/one-day-view">
                <i class="app-menu__icon fa fa-inbox"></i>
                <span class="app-menu__label">One Day View</span>
            </a>
        </li>
        <li>
            <a class="app-menu__item {{ Route::is('stockDetailView') ? 'active' : '' }} " href="/stock-detail-view">
                <i class="app-menu__icon fa fa-file-text-o"></i>
                <span class="app-menu__label">Stock Detail View</span>
            </a>
        </li>
        <li>
            <a class="app-menu__item {{ Route::is('oneDayIndex') ? 'active' : '' }} " href="/one-day-index">
                <i class="app-menu__icon fa fa-filter"></i>
                <span class="app-menu__label">One Day Index</span>
            </a>
        </li>
        <li>
            <a class="app-menu__item {{ Route::is('viewAllIndex') ? 'active' : '' }} " href="/view-all-index">
                <i class="app-menu__icon fa fa-filter"></i>
                <span class="app-menu__label">View All Index</span>
            </a>
        </li>
        <li>
            <a class="app-menu__item {{ Route::is('holidayList') ? 'active' : '' }} " href="/holiday-list">
                <i class="app-menu__icon fa fa-film"></i>
                <span class="app-menu__label">Holiday List</span>
            </a>
        </li>
        <li>
            <a class="app-menu__item {{ Route::is('todayStock') ? 'active' : '' }} " href="/today-stock">
                <i class="app-menu__icon fa fa-plus"></i>
                <span class="app-menu__label">Today Stocks</span>
            </a>
        </li>
        <li>
            <a class="app-menu__item {{ Route::is('averageStock') ? 'active' : '' }} " href="/average-stock">
                <i class="app-menu__icon fa fa-comment-o"></i>
                <span class="app-menu__label">Average Stock</span>
            </a>
        </li>
        <li>
            <a class="app-menu__item {{ Route::is('myPortfolio') ? 'active' : '' }} " href="/my-portfolio">
                <i class="app-menu__icon fa fa-check-circle"></i>
                <span class="app-menu__label">My Portfolio</span>
            </a>
        </li>
        <li>
            <a class="app-menu__item {{ Route::is('myWatchlist') ? 'active' : '' }} " href="/my-watchlist">
                <i class="app-menu__icon fa fa-plus"></i>
                <span class="app-menu__label">My Watchlist</span>
            </a>
        </li>
        <li>
            <a class="app-menu__item {{ Route::is('corporateInfo') ? 'active' : '' }} " href="/corporate-info">
                <i class="app-menu__icon fa fa-th-list"></i>
                <span class="app-menu__label">Corporate Info</span>
            </a>
        </li>
        <li>
            <a class="app-menu__item {{ Route::is('appUrl') ? 'active' : '' }} " href="/available-url">
                <i class="app-menu__icon fa fa-circle-o"></i>
                <span class="app-menu__label">App Url</span>
            </a>
        </li>
    </ul>
</aside>