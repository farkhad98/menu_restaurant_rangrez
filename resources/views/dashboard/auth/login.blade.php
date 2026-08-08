<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Вход в панель управления</title>
    <link rel="icon" type="image/svg+xml" href="{{asset('favicon.svg')}}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="stylesheet" href="{{asset('css/dashboard.css')}}">
</head>
<body class="admin-login-page">
    <main class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-9 col-md-6 col-lg-4">
                <section class="admin-login-card">
                    @include('dashboard.errors')

                    <h1>Вход в панель управления</h1>

                    <form action="{{route('login')}}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{old('email')}}" required autocomplete="email" autofocus>
                            @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{$message}}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">Пароль</label>
                            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                            @error('password')
                                <span class="invalid-feedback" role="alert"><strong>{{$message}}</strong></span>
                            @enderror
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{old('remember') ? 'checked' : ''}}>
                            <label class="form-check-label" for="remember">Запомнить меня</label>
                        </div>

                        <button type="submit" class="w-100 btn btn-dark">Войти</button>
                    </form>
                </section>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>
</html>
