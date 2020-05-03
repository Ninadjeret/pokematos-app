<?php
use Illuminate\Support\Facades\Input;
?>

@extends('layouts.auth')

@section('content')
<div class="container page-content-full">
    <div class="branding" style="text-align: center;">
        <h2 style="color: #fff; padding-bottom: 20px;">Rapport de debug transmis</h2>
        <!--<a style="display: inline-block; color: #fff; padding: 10px 30px; border: 2px solid #fff; border-radius: 50px;" href="#" onclick="window.close();">Fermer cette page</a>-->
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    axios({
      method: 'post',
      url: '/api/debug',
      data: {
        user: localStorage.getItem('pokematos_user'),
        settings: localStorage.getItem('pokematos_settings'),
        currentCity: localStorage.getItem('pokematos_currentCity'),
        cities: localStorage.getItem('pokematos_cities'),
        gyms: localStorage.getItem('pokematos_gyms'),
      }
    });
    console.log('toto')
</script>
@endsection
