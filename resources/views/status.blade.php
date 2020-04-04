<?php

use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\URL;

$services = [
    'app' => [
        'name' => 'Application',
        'description' => 'Disponibilité de l\'app, annonces depuis la map',
        'url' => '',
        'method' => 'GET',
        'params' => false,
        'status' => '200',
        'correctStatus' => '200',
    ],
    'discordbot' => [
        'name' => 'Bot Discord',
        'description' => 'Attribution auto des roles, annonces depuis les captures, controle des messages',
        'url' => config('app.bot_sync_url'),
        'method' => 'GET',
        'params' => false,
        'status' => '200',
        'correctStatus' => '200',
    ],
    'discordapi' => [
        'name' => 'API Discord',
        'description' => 'Connexion à l\'app, envoi des messages sur Discord, etc.',
        'url' => 'https://discordapp.com/api/v6/users/@me',
        'method' => 'GET',
        'params' => ['headers' => ['Authorization' => 'Bot '.config('discord.token')]],
        'status' => '200',
        'correctStatus' => '200',
    ],
    'ia' => [
        'name' => 'API Vision Microsoft',
        'description' => 'Service d\'analyse des captures d\'écran',
        'url' => 'https://westeurope.api.cognitive.microsoft.com/vision/v2.0/recognizeText?mode=Printed',
        'method' => 'POST',
        'status' => '200',
        'correctStatus' => '401',
    ],
];

$client = new Client([
    'http_errors' => false,
    'connect_timeout' => 4
]);

foreach( $services as &$service ) {
    if( empty($service['url']) ) continue;
    $params = ( !empty($service['params']) ) ? $service['params'] : [] ;
    try {
        $response = $client->request($service['method'], $service['url'], $params);
        $service['status'] = $response->getStatusCode();
    } catch (\GuzzleHttp\Exception\ConnectException $e) {
        $service['status'] = '400';
    }
}


$stats = json_decode(file_get_contents('http://app.pokematos.fr/api/stats/g/ia'));
$stats_js_month = [['Jour', 'Taux de réussite pour les images comprises commes des screens de raid', 'Taux de réussite pour toutes les images']];
foreach( $stats->year as $date => $data ) {
    $stats_js_month[] = [$date, 100 - $data->percentage_errors2, 100 - $data->percentage_errors];
}
$stats_js_day = [['Heure', 'Taux de réussite']];
foreach( ['00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23'] as $hour ) {
    $hour .= 'h';
    $data = $stats->day->$hour;
    $stats_js_day[] = [$hour, $data->percentage_success];
}
?>

@extends('layouts.auth')

@section('content')
<div class="status__header">
    <div class="container">
          <img src="https://assets.profchen.fr/img/logo_pokematos.png">
          <h1>POKEMATOS&nbsp;<small>Statut</small></h1>
      </div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = google.visualization.arrayToDataTable(<?php echo json_encode($stats_js_month); ?>);

        var options = {
          colors: ['#5a6cae', '#a8a7cf'],

        };

        var chart = new google.visualization.LineChart(document.getElementById('stats_month'));

        chart.draw(data, options);
      }
</script>
<script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = google.visualization.arrayToDataTable(<?php echo json_encode($stats_js_day); ?>);

        var options = {
          colors: ['#5a6cae', '#a8a7cf'],
        };

        var chart = new google.visualization.LineChart(document.getElementById('stats_day'));

        chart.draw(data, options);
      }
</script>

<div class="status__data">
    <div class="container">

        <div class="chart">
            <h3>Statut des services</h3>
            <div class="services">
                <?php foreach( $services as &$service ) { ?>
                    <div class="service">
                        <i class="<?php echo ($service['status'] == $service['correctStatus']) ? 'on' : 'off' ; ?>"></i>
                        <h4>
                            <?php echo $service['name']; ?>
                            <small><?php echo $service['description']; ?></small>
                        </h4>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="chart">
            <h3>Analyse des captures d'écran sur les 30 derniers jours</h3>
            <div id="stats_month" style="width: 100%; height: 300px"></div>
        </div>
        <div class="chart">
            <h3>Analyse des captures d'écran de la journée</h3>
            <div id="stats_day" style="width: 100%; height: 300px"></div>
        </div>
    </div>
</div>
@endsection
