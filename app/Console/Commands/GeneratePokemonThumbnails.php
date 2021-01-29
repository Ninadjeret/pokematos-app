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
        $pokemons = Pokemon::all();
        if (!empty($this->argument('pkmn_id'))) $pokemons = Pokemon::where('pokedex_id', $this->argument('pkmn_id'))->get();

        foreach ($pokemons as $pokemon) {
            $this->line("Génération pour {$pokemon->name_fr}");

            $default_path = storage_path() . "/app/pokemon/default/pokemon_icon_{$pokemon->pokedex_id}_{$pokemon->form_id}.png";
            $cropped_path = storage_path() . "/app/pokemon/cropped/pokemon_icon_{$pokemon->pokedex_id}_{$pokemon->form_id}.png";
            $raid_path = storage_path() . "/app/pokemon/raid/map_marker_pokemon_{$pokemon->pokedex_id}.png";
            if ($pokemon->form_id != '00') {
                $raid_path = storage_path() . "/app/pokemon/raid/map_marker_pokemon_{$pokemon->pokedex_id}_{$pokemon->form_id}.png";
            }
            $quest_path = storage_path() . "/app/pokemon/quest/map_marker_quest_pokemon_{$pokemon->pokedex_id}_{$pokemon->form_id}.png";


            try {
                $file = "https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_{$pokemon->pokedex_id}_{$pokemon->form_id}.png";
                $copied = copy($file, $default_path);
            } catch (\Exception $e) {
                $this->alert($e->getMessage());
                continue;
            }


            if (!file_exists($default_path)) {
                $this->alert("Image non générée pour {$pokemon->name_fr}");
                continue;
            }

            $image = new PokemonImagify($default_path);
            $image->cropTransparentBg();
            $image->save($cropped_path);
            $image->createRaidThumbnail($cropped_path, $raid_path);
            $image->createQuestThumbnail($cropped_path, $quest_path);
        }
    }
}