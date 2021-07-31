<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Stop;
use Illuminate\Console\Command;

class ImportPOIs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:iitc';

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
        $city_names = City::pluck('name')->toArray();
        $city_name = $this->choice('Pour quelle zone ?', $city_names);

        $city = City::where('name', $city_name)->first();
        if( empty( $city ) ) {
            $this->info('Import avorté. la ville n\'a pas été trovuée.');
            return false;
        }

        $files = array_diff(scandir( public_path().'/import' ), array('..', '.'));
        $file_name = $this->choice('Quel fichier utiliser ?', $files);
        $url = url('/import/'.$file_name);

        $json = file_get_contents($url);
        $array = json_decode($json, true);

        $gyms_count = count($array['gyms']);
        $pokestops_count = count($array['pokestops']);

        $this->info( "Analyse des {$gyms_count} arènes et {$pokestops_count} pokéstops..." );

        $this->result = [
            'gyms' => (object) [
                'to_add_count' => 0,
                'to_update_vals' => [],
                'to_update_count' => 0,
            ],
            'pokestops' => (object) [
                'to_add_count' => 0,
                'to_update_vals' => [],
                'to_update_count' => 0,
            ],
        ];

        foreach( $array['gyms'] as $key => $data ) {
            $this->findPOI($key, $data, $city->id, 'gyms');
        }
        foreach( $array['pokestops'] as $key => $data ) {
            $this->findPOI($key, $data, $city->id, 'pokestops');
        }

        $this->info("Analyse terminée");
        $this->info("     - {$this->result['gyms']->to_add_count} arènes à créer et {$this->result['gyms']->to_update_count} à mettre à ajour");
        $this->info("     - {$this->result['pokestops']->to_add_count} Pokéstops à créer et {$this->result['pokestops']->to_update_count} à mettre à ajour");
        if( !$this->confirm('Confirmer l\'import ?') ) {
            $this->info("Taitement annulé");
            return;
        }
        $this->info("Taitement en cours...");

        $added = 0;
        $updated = 0;
        foreach( $array['gyms'] as $key => $data ) {
            if( !array_key_exists( $key, $this->result['gyms']->to_update_vals ) ) {
                $added++;
                $this->add($data, $city->id, true);
            } else {
                $updated++;
                $stop_to_update = Stop::find( $this->result['gyms']->to_update_vals[$key] );
                $this->update($data, $stop_to_update, $city->id, true);
            }
        }

        foreach( $array['pokestops'] as $key => $data ) {
            if( !array_key_exists( $key, $this->result['pokestops']->to_update_vals ) ) {
                $added++;
                $this->add($data, $city->id, false);
            } else {
                $updated++;
                $stop_to_update = Stop::find( $this->result['pokestops']->to_update_vals[$key] );
                $this->update($data, $stop_to_update, $city->id, false);
            }
        }

        $this->info("{$added} POI ajoutés et {$updated} mis à jour");

        return;
    }

    public function add( $data, $city_id, $gym = true ) {
        if( !isset($data['name']) ) return;
        $gym_type = ( $gym ) ? 1 : 0 ;
        $ex = ( array_key_exists('isEx', $data) ) ? 1 : 0;
        $stop = Stop::create([
            'niantic_id' => $data['guid'],
            'name' => $data['name'],
            'niantic_name' => $data['name'],
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'ex' => $ex,
            'gym' => $gym_type,
            'city_id' => $city_id,
        ]);
        $this->line("     - POI {$data['name']} créé avec succès ($stop->id)");
    }

    public function update( $data, $stop, $city_id, $gym = true ) {
        $this->line( 'tototo ' . print_r($stop->id, true) );
        $gym_type = ( $gym ) ? 1 : 0 ;
        $ex = ( array_key_exists('isEx', $data) ) ? 1 : 0;
        $stop->update([
            'niantic_id' => $data['guid'],
            'name' => $data['name'],
            'niantic_name' => $data['name'],
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'ex' => $ex,
            'gym' => $gym_type,
            'city_id' => $city_id,
        ]);
        $this->line("     - POI {$data['name']} mis à jour avec succès ($stop->id)");
    }

    public function findPOI( $key, $data, $city_id, $type ) {
        if( !isset($data['name']) ) return;
        $to_update = false;
        $import_lat = round($data['lat'], 5, PHP_ROUND_HALF_DOWN);
        $import_lng = round($data['lng'], 5, PHP_ROUND_HALF_DOWN);
        $this->line('Analyse du POI '.$data['name']." ({$data['guid']})");

        $stop = Stop::where('city_id', $city_id )
            ->where('niantic_id', $data['guid'])
            ->first();
        if( !empty( $stop ) ) {
            $to_update = $stop->id;
            $this->line('     => Correspondance trouvée par ID : '.$stop->id);
        }

        $stop = Stop::where('city_id', $city_id )
            ->where('niantic_name', $data['name'])
            ->first();
        if( !empty( $stop ) ) {
            $coords_similarity = self::isSameCoords([
                'lat' => $stop->lat,
                'lng' => $stop->lng,
            ], [
                'lat' => $import_lat,
                'lng' => $import_lng,
            ]);
            if( $coords_similarity ) {
                $to_update = $stop->id;
                $this->line('     => Correspondance trouvée par Titre : '.$stop->id);
            }
        }
        $stop = Stop::where('city_id', $city_id )
            ->whereBetween('lat', [$import_lat-0.00001, $import_lat+0.00001])
            ->whereBetween('lng', [$import_lng-0.00001, $import_lng+0.00001])
            ->first();
        if( !empty( $stop ) ) {
            $to_update = $stop->id;
            $this->line('     => Correspondance trouvée par GPS : '.$stop->id);
        }

        if( !$to_update ) {
            $this->result[$type]->to_add_count++;
            $this->info("{$data['name']} -- lat:{$import_lat} -- lng:{$import_lng}");
        } else {
            $this->result[$type]->to_update_count++;
            $this->result[$type]->to_update_vals[$key] = $to_update;
        }

    }

    public static function isSameCoords( $stop_coords, $import_coords ) {
        $lat = false;
        $lng = false;

        if( $stop_coords['lat'] - $import_coords['lat'] <= 0.00002 || $import_coords['lat'] - $stop_coords['lat'] <= 0.00002 ) {
            $lat = true;
        }
        if( $stop_coords['lng'] - $import_coords['lng'] <= 0.00002 || $import_coords['lng'] - $stop_coords['lng'] <= 0.00002 ) {
            $lng = true;
        }
        return $lat && $lng;
    }
}
