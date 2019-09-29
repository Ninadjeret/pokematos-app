<?php

namespace App\Console\Commands;

use App\Models\Stop;
use Illuminate\Console\Command;

class ImportPOIs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:iitc {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $city_id = 5;
        $url = $this->argument('url');

        $json = file_get_contents($url);
        $array = json_decode($json);
        foreach( $array->gyms as $poi ) {
            $ex = ( property_exists($poi, 'isEx') ) ? 1 : 0;
            Stop::create([
                'name' => $poi->name,
                'niantic_name' => $poi->name,
                'lat' => $poi->lat,
                'lng' => $poi->lng,
                'ex' => $ex,
                'gym' => 1,
                'city_id' => $city_id,
            ]);
            echo $poi->name." importé\r\n";
        }
        foreach( $array->pokestops as $poi ) {
            Stop::create([
                'name' => $poi->name,
                'niantic_name' => $poi->name,
                'lat' => $poi->lat,
                'lng' => $poi->lng,
                'ex' => 0,
                'gym' => 0,
                'city_id' => $city_id,
            ]);
            echo $poi->name." importé\r\n";
        }

    }
}
