<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link @if(Route::currentRouteName() === 'dashboard.index') active @endif" aria-current="page" href="{{route('dashboard.index')}}">
                    <span data-feather="home" class="align-text-bottom"></span>
                    Панель управления
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if(Str::startsWith(Route::currentRouteName(), 'categories.')) active @endif" href="{{route('categories.index')}}">
                    <span data-feather="file" class="align-text-bottom"></span>
                    Категории
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if(Str::startsWith(Route::currentRouteName(), 'products.')) active @endif" href="{{route('products.index')}}">
                    <span data-feather="shopping-cart" class="align-text-bottom"></span>
                    Продукты
                </a>
            </li>
        </ul>
    </div>
</nav>
