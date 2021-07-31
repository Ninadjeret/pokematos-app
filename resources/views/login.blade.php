<?php
use Illuminate\Support\Facades\Input;
?>

@extends('layouts.auth')

@section('content')
<div class="container page-content-full">
  <div class="branding">
        <img src="<?php echo asset('storage/img/static/logo_pokematos.png'); ?>">
        <h1>POKEMATOS</h1>
        <br>
        <div>
          <a class="login" href="/login">Connexion avec Discord</a>
          <?php
          $code = Request::input('code');
          if( !empty($code) ) {
              echo '<p class="denied"><i class="material-icons">warning</i><span>';
              switch ($code) {
                  case 1 :
                    echo 'Récupération des informations depuis Discord Impossible';
                    break;
                case 2 :
                    echo 'Aucun serveur Discord sur lesquels vous êtes présent n\'utilise Pokématos';
                    break;
                case 3 :
                    echo 'Vos roles sur le(s) serveur(s) Discord utilisant Pokématos sont insuffisants';
                    break;
              }
              echo ' <a href="https://www.pokematos.fr/documentation/connexion/">En savoir plus</a></span></p>';
          }
          ?>
          <a class="minor" href="https://www.pokematos.fr/documentation/connexion/">Pourquoi Pokématos a besoin de ces informations ?</a>
        </div>
      </section>
    </div>
</div>
@endsection
