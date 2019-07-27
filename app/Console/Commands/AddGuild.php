<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Guild;
use App\Models\Install;
use App\Helpers\Helpers;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class AddGuild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guild:add {city}';

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

        $city_name = $this->argument('city');
        $city = City::where('name', $city_name)->first();
        if( !$city ) {
            $this->line('Création de la ville '.$city_name.'...');
            $city = City::create([
                'name' => $city_name,
                'slug' => Helpers::sanitize($city_name),
            ]);
        }

        $this->line('Création de la guild...');
        $guild = Guild::create();
        $guild->update([
            'token' => $guild->id.'.'.Str::random(10),
            'city_id' => $city->id,
        ]);

        $this->info("L'ajout d'une nouvelle commu est prête. Voici le token provisoire : {$guild->token}");
    }
}
