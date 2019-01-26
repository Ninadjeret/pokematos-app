<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans%3A300%2C400%2C600%2C700" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/vuetify/dist/vuetify.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="logged-out">
    <div id="app">
        <v-app light v-cloak>

            <div class="layout wrap justify-center align-stretch fill-height">
                <header class="flex lg6 sm4 xs12">
                    <div style="background-image: url('/svg/auth2.svg');" class="background">
                </header>

                <div class="main__wrapper flex lg6 sm8 xs12 column">
                    @guest
                    <nav class="nav">
                        <ul>
                            <li class="nav__item {{ Request::is('login') ? 'active' : '' }}">
                                <a href="{{ route('login') }}">{{ __('Se connecter') }}</a>
                            </li>
                            <li class="nav__item {{ Request::is('register') ? 'active' : '' }}">
                                @if (Route::has('register'))
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('S\'inscrire') }}</a>
                                @endif
                            </li>
                        </ul>
                    </nav>
                    @endguest
                    <main>
                        @yield('content')
                    </main>
                </div>

            </div>

        </v-app>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/manifest.js') }}"></script>
    <script src="{{ asset('js/vendor.js') }}"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
