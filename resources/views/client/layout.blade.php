<!DOCTYPE html>
<html lang="{{app()->getLocale()}}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Rangrez Restaurant menu">
    <title>Rangrez Restaurant — Menu</title>
    <link rel="icon" type="image/svg+xml" href="{{asset('favicon.svg')}}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('build/app.css')}}">
</head>
<body>
    @yield('content')
    <script type="module" src="{{asset('build/app.js')}}"></script>
</body>
</html>
