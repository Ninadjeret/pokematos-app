<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokématos</title>

    @laravelPWA

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/vuetify/dist/vuetify.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?version={{config('app.version.current')}}" rel="stylesheet">
</head>
<body>

@yield('content')

    <!-- Scripts -->
    <!--<script src="{{ asset('js/manifest.js') }}"></script>
    <script src="{{ asset('js/vendor.js') }}"></script>-->
    <script type="text/javascript">
        window.pokematos = <?php echo json_encode( \App\Core\App::config() ); ?>;
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{config('app.maps_api_key')}}&libraries=places"></script>
    <script src="{{ asset('js/app.js') }}?version={{config('app.version.current')}}" defer></script>
</body>
</html>
