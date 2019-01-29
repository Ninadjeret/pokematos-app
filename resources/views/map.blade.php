@extends('layouts.app')

@section('content')
<div class="container">
    <p><a href="/logout">Se déconnecter</a></p>
    @auth
    <p>Coucou {{ Auth::user()->name }}</p>
    @endauth
</div>
@endsection
