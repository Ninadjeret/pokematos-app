<?php

namespace App\Console\Commands;

use App\Models\Pokemon;
use Illuminate\Console\Command;

class GameMasterUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gamemaster:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Pokémon data from the last GameMaster file';

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
        $to_add = [];
        $to_update = [];

        $this->info('Téléchargement du dernier GameMaster');

        $game_master = file_get_contents('https://raw.githubusercontent.com/pokemongo-dev-contrib/pokemongo-game-master/master/versions/latest/GAME_MASTER.json');
        $game_master = json_decode($game_master);

        $names_fr = file_get_contents('https://raw.githubusercontent.com/sindresorhus/pokemon/master/data/fr.json');
        $names_fr = json_decode($names_fr, true);

        $this->info('Analyse du dernier GameMaster');

        foreach( $game_master as $game_master_2 ) {
            if( is_array($game_master_2) ) { foreach( $game_master_2 as $node ) {
            if( !isset($node->pokemonSettings) || empty($node->pokemonSettings) ) continue;
            if( strstr($node->templateId, '_NORMAL') ) continue;
            if( strstr($node->templateId, '_PURIFIED') ) continue;
            if( strstr($node->templateId, '_SHADOW') ) continue;

            $pokedex_id = substr($node->templateId, 2, 3);
            $name_ocr = ( isset($names_fr[(int)$pokedex_id]) ) ? $names_fr[(int)$pokedex_id - 1] : null;
            $form_id = ( isset($node->pokemonSettings->form) ) ? $node->pokemonSettings->form : '00';

            $forms = [
                'ALOLA' => 'd\'Alola',
                'SPEED' => 'Vitesse',
                'ATTACK' => 'Attaque',
                'DEFENSE' => 'Défense',
                'PLANT' => 'Plante',
                'SANDY' => 'Sable',
                'TRASH' => 'test',
            ];

            $name_fr = $name_ocr;
            if( !empty( $form_id ) && $form_id != '00' ) {
                foreach( $forms as $form => $label ) {
                    if( strstr($node->templateId, $form) ) {
                        $name_fr = $name_ocr.' '.$label;
                    }
                }
            }

            $data = [
                'pokedex_id' => $pokedex_id,
                'niantic_id'  => $node->templateId,
                'name_fr'   => $name_fr,
                'name_ocr'   => $name_ocr,
                'form_id'  => $form_id,
                'base_att'  => $node->pokemonSettings->stats->baseAttack,
                'base_def'  => $node->pokemonSettings->stats->baseDefense,
                'base_sta'  => $node->pokemonSettings->stats->baseStamina,
                'parent_id' => null,
            ];

            $pokemon = $this->find($data['niantic_id']);
            if( $pokemon ) {
                $diff = $this->compare($pokemon, $data);
                if( $diff > 0 ) {
                    $to_update[$data['niantic_id']] = $data;
                }
            } else {
                $to_add[$data['niantic_id']] = $data;
            }

        }}}


        if( count( $to_add ) > 0 ) {
            $this->info(count($to_add).' POKEMON A AJOUTER');
            foreach( $to_add as $niantic_id => $data ) {
                $this->line('- '.$niantic_id);
            }
        }

        if( count( $to_update ) > 0 ) {
            $this->info(count($to_update).' POKEMON A METTRE A JOUR');
            foreach( $to_update as $niantic_id => $data ) {
                $this->line('- '.$niantic_id);
            }
        }

        if( count( $to_update ) > 0 || count( $to_add ) > 0 ) {
            if ($this->confirm('Mettre à jour selon le dernier GameMaster ?')) {
                $this->perform( $to_add, $to_update );
            }
        } else {
            $this->info('Rien à mettre à jour');
        }
    }

    public function perform( $to_add, $to_update ) {
        if( !empty( $to_add ) ) {
            $this->info('Création des Pokémons');
            foreach( $to_add as $niantic_id => $data ) {
                Pokemon::create([
                    'pokedex_id'    => $data['pokedex_id'],
                    'form_id'       => $data['form_id'],
                    'niantic_id'    => $niantic_id,
                    'name_fr'       => $data['name_fr'],
                    'base_att'      => $data['base_att'],
                    'base_def'      => $data['base_def'],
                    'base_sta'      => $data['base_sta'],
                    'parent_id'     => $data['parent_id'],
                ]);
                $this->line('- '.$niantic_id.' Créé');
            }
        }

        if( !empty( $to_update ) ) {
            $this->info('Mise à jour des Pokémons');
            foreach( $to_update as $niantic_id => $data ) {
                $pokemon = Pokemon::where('niantic_id', $niantic_id);
                if( $pokemon ) {
                    $pokemon->update([
                        'name_fr'   => $data['name_fr'],
                        'base_att'  => $data['base_att'],
                        'base_def'  => $data['base_def'],
                        'base_sta'  => $data['base_sta'],
                    ]);
                    $this->line('- '.$niantic_id.' mis à jour');
                }
            }
        }
    }

    public function find( $niantic_id ) {
        $pokemon = Pokemon::where( 'niantic_id', $niantic_id )->first();
        if( $pokemon ) {
            return $pokemon;
        }
        return false;
    }

    public function compare( $pokemon, $data ) {
        $diff = 0;
        foreach( ['base_att', 'base_def', 'base_sta'] as $spec ) {
            if( isset( $data[$spec] ) && $data[$spec] != $pokemon->$spec ) {
                $diff++;
            }
        }
        return $diff;
    }
}
