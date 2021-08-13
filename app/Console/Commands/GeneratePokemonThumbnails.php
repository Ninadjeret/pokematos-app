<?php

namespace App\Console\Commands;

use App\Models\Pokemon;
use Illuminate\Console\Command;
use App\Core\Tools\PokemonImagify;

class GeneratePokemonThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:thumbnails {pkmn_id?}';

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
        if (!empty($this->argument('pkmn_id'))) {
            $pokemons = Pokemon::where('pokedex_id', $this->argument('pkmn_id'))->get();
        } else {
            $pokemons = Pokemon::all();
        }

        $bar = $this->output->createProgressBar(count($pokemons));
        $bar->setFormat("%current%/%max% [%bar%] %percent:3s%%\n%message%");
        $bar->setMessage("Initialisation");
        $errors = [];

        foreach ($pokemons as $pokemon) {
            $bar->advance();
            $bar->setMessage("Génération pour {$pokemon->name_fr}");

            $default_path = storage_path() . "/app/public/img/pokemon/base/pokemon_icon_{$pokemon->pokedex_id}_{$pokemon->form_id}.png";
            $cropped_path = storage_path() . "/app/public/img/pokemon/cropped/pokemon_icon_{$pokemon->pokedex_id}_{$pokemon->form_id}.png";
            $raid_path = storage_path() . "/app/public/img/pokemon/raid/map_marker_pokemon_{$pokemon->pokedex_id}.png";
            if ($pokemon->form_id != '00') {
                $raid_path = storage_path() . "/app/public/img/pokemon/raid/map_marker_pokemon_{$pokemon->pokedex_id}_{$pokemon->form_id}.png";
            }
            $quest_path = storage_path() . "/app/public/img/pokemon/quest/map_marker_quest_pokemon_{$pokemon->pokedex_id}_{$pokemon->form_id}.png";

            try {
                $file = "https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon%20-%20256x256/pokemon_icon_{$pokemon->pokedex_id}_{$pokemon->form_id}.png";
                $copied = copy($file, $default_path);
            } catch (\Exception $e) {
                //$this->alert($e->getMessage());
                $errors[] = ['id' => $pokemon->pokedex_id, 'form_id' => $pokemon->form_id, 'name' => $pokemon->name_fr];
                continue;
            }


            if (!file_exists($default_path)) {
                //$this->alert("Image non générée pour {$pokemon->name_fr}");
                $errors[] = ['id' => $pokemon->pokedex_id, 'name' => $pokemon->name_fr];
                continue;
            }

            $image = new PokemonImagify($default_path);
            $image->cropTransparentBg();
            $image->save($cropped_path);
            $image->createRaidThumbnail($cropped_path, $raid_path);
            $image->createQuestThumbnail($cropped_path, $quest_path);

            //Gestion des mega énergies
            
            if(  $pokemon->form_id == '51' || $pokemon->form_id == '52' ) {
                $base = Pokemon::where('pokedex_id', $pokemon->pokedex_id)->where('form_id', '00')->first();
                $default_energy_path = storage_path() . "/app/public/img/pokemon/energy/mega_energy_{$pokemon->pokedex_id}.png";
                $quest_energy_path = storage_path() . "/app/public/img/pokemon/energyquest/map_marker_quest_energy_{$pokemon->pokedex_id}.png";
                $image->createEnergyThumbnail($cropped_path, $default_energy_path);
                $image->createEnergyQuestThumbnail($cropped_path, $quest_energy_path);
            }
        
        }
        $bar->setMessage("<fg=green>Génération terminée</>");
        $bar->finish();
        
        if( !empty($errors) ) {
            $this->line('');
            $this->error(count($errors)." erreur(s) lors de la génération");
            $this->table( ['ID', 'FORM ID', 'NAME'], $errors);
        }
        
    }
}