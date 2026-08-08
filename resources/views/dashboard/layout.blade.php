<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dashboard @yield("title")</title>
    <link rel="icon" type="image/svg+xml" href="{{asset('favicon.svg')}}">
    <meta name="theme-color" content="#212529">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="stylesheet" href="{{asset('css/dashboard.css')}}">
</head>
<body class="dashboard-body">
    <header class="navbar navbar-dark sticky-top bg-dark shadow dashboard-navbar">
        <a class="navbar-brand" href="{{route('dashboard.index')}}">Rangrez Restaurant</a>

        <div class="dashboard-navbar-actions">
            <a class="btn btn-sm btn-outline-light dashboard-site-link" href="{{route('index', ['locale' => 'ru'])}}" target="_blank" rel="noopener">
                Открыть сайт
            </a>

            <form class="dashboard-logout-form" action="{{route('logout')}}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light">Выйти</button>
            </form>

            <button class="navbar-toggler d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Открыть навигацию">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            @include('dashboard._sidebar')

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 dashboard-main">
                <div class="dashboard-page-header">
                    <h1>Панель управления</h1>
                </div>

                @yield("content")
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons@4.28.0/dist/feather.min.js" integrity="sha384-uO3SXW5IuS1ZpFPKugNNWqTZRRglnUJK6UAZ/gxOX80nxEkN9NcGZTftn6RzhGWE" crossorigin="anonymous"></script>
    <script src="{{asset('js/dashboard.js')}}"></script>
</body>
</html>
