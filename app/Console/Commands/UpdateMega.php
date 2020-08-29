<?php

namespace App\Console\Commands;

use App\Models\Pokemon;
use Illuminate\Console\Command;

class UpdateMega extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mega:update';

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
        foreach (['003', '006', '009', '015'] as $pokemon_id) {
            $mega = Pokemon::where('pokedex_id', $pokemon_id)->where('form_id', '51')->first();
            if (empty($mega)) {
                $pokemon = Pokemon::where('pokedex_id', $pokemon_id)->first();
                $mega = Pokemon::create([
                    'pokedex_id' => $pokemon_id,
                    'niantic_id' => $pokemon_id . 'NIANTICMEGA',
                    'form_id' => 51,
                    'name_fr' => 'Mega ' . $pokemon->name_fr,
                    'name_ocr' => 'Mega ' . $pokemon->name_fr,
                ]);
            }
            if ($pokemon_id == '006') {
                $mega = Pokemon::where('pokedex_id', $pokemon_id)->where('form_id', '52')->first();
                if (empty($mega)) {
                    $pokemon = Pokemon::where('pokedex_id', $pokemon_id)->first();
                    $mega = Pokemon::create([
                        'pokedex_id' => $pokemon_id,
                        'niantic_id' => $pokemon_id . 'NIANTICMEGA',
                        'form_id' => 52,
                        'name_fr' => 'Mega ' . $pokemon->name_fr,
                        'name_ocr' => 'Mega ' . $pokemon->name_fr,
                    ]);
                }
            }
        }
    }
}