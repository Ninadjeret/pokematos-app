<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Teko" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/vuetify/dist/vuetify.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">

            <header class="header__wrapper--app">
                    <div class="header-title">
                        <city-choice></city-choice>
                    </div>
            </header>

            <nav id="main" class="navbar">
                <ul>
                    <li>
                        <a class="{{ Request::is('/') ? 'active' : '' }}" href="{{ url('/') }}">
                            <i aria-hidden="true" class="material-icons">map</i>
                            <span>Map</span>
                        </a>
                    </li>
                    <li>
                        <a class="{{ Request::is('list') ? 'active' : '' }}" href="{{ url('/list') }}">
                            <i aria-hidden="true" class="material-icons">notifications_active</i>
                            <span>Liste</span>
                        </a>
                    </li>
                    <li>
                        <a class="{{ Request::is('settings') ? 'active' : '' }}" href="{{ url('/settings') }}">
                            <i aria-hidden="true" class="material-icons">settings</i>
                            <span>Réglages</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <main>
                @yield('content')
            </main>
    </div>

    <!-- Scripts -->
    <!--<script src="{{ asset('js/manifest.js') }}"></script>
    <script src="{{ asset('js/vendor.js') }}"></script>-->
    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
