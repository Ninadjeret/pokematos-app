<?php

namespace App\Console\Commands;

use App\Models\Stop;
use Illuminate\Console\Command;

class GuildMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guild:migrate {old_city_slug} {new_city_id}';

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
        $old_city = $this->argument('old_city_slug');
        $new_city = $this->argument('new_city_id');
        $arenes = file_get_contents('https://'.$old_city.'.profchen.fr/api/v1/gyms?token=AsdxZRqPkrst67utwHVM2w4rt4HjxGNcX8XVJDryMtffBFZk3VGM47HkvnF9');
        $arenes = json_decode($arenes);
        foreach( $arenes as $arene ) {
            error_log('Import de '.$arene->nameFr);
            Stop::create([
                'niantic_name'  => $arene->nianticId,
                'name' => $arene->nameFr,
                'lat' => $arene->GPSCoordinates->lat,
                'lng' => $arene->GPSCoordinates->lng,
                'ex' => $arene->raidEx,
                'gym' => 1,
                'city_id' => $new_city,
            ]);
        }
    }
}
