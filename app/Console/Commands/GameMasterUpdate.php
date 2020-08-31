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

        $game_master = file_get_contents('https://raw.githubusercontent.com/PokeMiners/game_masters/master/latest/latest.json');
        $game_master = json_decode($game_master);

        $names_fr = file_get_contents('https://raw.githubusercontent.com/sindresorhus/pokemon/master/data/fr.json');
        $names_fr = json_decode($names_fr, true);

        $this->info('Analyse du dernier GameMaster');

        foreach ($game_master as $node) {
            if (!isset($node->data->pokemonSettings) || empty($node->data->pokemonSettings)) continue;
            if (strstr($node->templateId, '_NORMAL')) continue;
            if (strstr($node->templateId, '_PURIFIED')) continue;
            if (strstr($node->templateId, '_SHADOW')) continue;
            if (strstr($node->templateId, '_FALL_2019')) continue;

            $pokedex_id = substr($node->templateId, 2, 3);
            $name_ocr = (isset($names_fr[(int)$pokedex_id])) ? $names_fr[(int)$pokedex_id - 1] : null;
            $form_id = (isset($node->data->pokemonSettings->form)) ? $node->data->pokemonSettings->form : '00';

            $forms = [
                'ALOLA' => 'd\'Alola',
                'SPEED' => 'Vitesse',
                'ATTACK' => 'Attaque',
                'DEFENSE' => 'Défense',
                'PLANT' => 'Plante',
                'SANDY' => 'Sable',
                'TRASH' => 'Déchet',
                'RAINY' => 'Pluie',
                'SNOWY' => 'Neige',
                'SUNNY' => 'Soleil',
                'OVERCAST' => 'Couvert',
                'GALARIAN' => 'de Galar',
            ];

            $name_fr = $name_ocr;
            if (!empty($form_id) && $form_id != '00') {
                foreach ($forms as $form => $label) {
                    if (strstr($node->templateId, $form)) {
                        $name_fr = $name_ocr . ' ' . $label;
                    }
                }
            }

            //On transforme les IDS des formes en numéro pour correspondre aux sprites officielles
            if (strstr($form_id, 'GALARIAN')) {
                $form_id = '31';
            }
            if (strstr($form_id, 'ALOLA')) {
                $form_id = '61';
            }

            $data = [
                'pokedex_id' => $pokedex_id,
                'niantic_id'  => $node->templateId,
                'name_fr'   => $name_fr,
                'name_ocr'   => $name_ocr,
                'form_id'  => $form_id,
                'base_att'  => $node->data->pokemonSettings->stats->baseAttack,
                'base_def'  => $node->data->pokemonSettings->stats->baseDefense,
                'base_sta'  => $node->data->pokemonSettings->stats->baseStamina,
                'parent_id' => null,
            ];

            $pokemon = $this->find($data['niantic_id']);
            if ($pokemon) {
                $diff = $this->compare($pokemon, $data);
                if ($diff > 0) {
                    $to_update[$data['niantic_id']] = $data;
                }
            } else {
                $to_add[$data['niantic_id']] = $data;
            }

            //Gestion des Méga
            if (isset($node->data->pokemonSettings->obTemporaryEvolutions)) {
                foreach ($node->data->pokemonSettings->obTemporaryEvolutions as $mega) {
                    $name = 'Méga-' . $name_fr;
                    $suffix_id = str_replace('TEMP_EVOLUTION', '', $mega->obTemporaryEvolution);
                    $suffixe = str_replace('TEMP_EVOLUTION_MEGA', '', $mega->obTemporaryEvolution);
                    if (!empty($suffixe)) $name .= ' ' . str_replace('_', '', $suffixe);
                    $form_id = ($suffixe == '_Y') ? 52 : 51;
                    $data = [
                        'pokedex_id' => $pokedex_id,
                        'niantic_id'  => $node->templateId . $suffix_id,
                        'name_fr'   => $name,
                        'name_ocr'   => $name,
                        'form_id'  => $form_id,
                        'base_att'  => $mega->stats->baseAttack,
                        'base_def'  => $mega->stats->baseDefense,
                        'base_sta'  => $mega->stats->baseStamina,
                        'parent_id' => null,
                    ];

                    $pokemon = $this->find($data['niantic_id']);
                    if ($pokemon) {
                        $diff = $this->compare($pokemon, $data);
                        if ($diff > 0) {
                            $to_update[$data['niantic_id']] = $data;
                        }
                    } else {
                        $to_add[$data['niantic_id']] = $data;
                    }
                }
            }
        }


        if (count($to_add) > 0) {
            $this->info(count($to_add) . ' POKEMON A AJOUTER');
            foreach ($to_add as $niantic_id => $data) {
                $this->line('- ' . $niantic_id);
            }
        }

        if (count($to_update) > 0) {
            $this->info(count($to_update) . ' POKEMON A METTRE A JOUR');
            foreach ($to_update as $niantic_id => $data) {
                $this->line('- ' . $niantic_id);
            }
        }

        if (count($to_update) > 0 || count($to_add) > 0) {
            $count_to_add = count($to_add);
            $count_to_update = count($to_update);
            if ($this->confirm("Mettre à jour selon le dernier GameMaster ? ({$count_to_add} Pokémon à ajouter, {$count_to_update} à mettre à jour)")) {
                $this->perform($to_add, $to_update);
            }
        } else {
            $this->info('Rien à mettre à jour');
        }
    }

    public function perform($to_add, $to_update)
    {
        if (!empty($to_add)) {
            $this->info('Création des Pokémons');
            foreach ($to_add as $niantic_id => $data) {
                Pokemon::create([
                    'pokedex_id'    => $data['pokedex_id'],
                    'form_id'       => $data['form_id'],
                    'niantic_id'    => $niantic_id,
                    'name_fr'       => $data['name_fr'],
                    'name_ocr'      => $data['name_ocr'],
                    'base_att'      => $data['base_att'],
                    'base_def'      => $data['base_def'],
                    'base_sta'      => $data['base_sta'],
                    'parent_id'     => $data['parent_id'],
                ]);
                $this->line('- ' . $niantic_id . ' Créé');
            }
        }

        if (!empty($to_update)) {
            $this->info('Mise à jour des Pokémons');
            foreach ($to_update as $niantic_id => $data) {
                $pokemon = Pokemon::where('niantic_id', $niantic_id);
                if ($pokemon) {
                    $pokemon->update([
                        'form_id'   => $data['form_id'],
                        'base_att'  => $data['base_att'],
                        'base_def'  => $data['base_def'],
                        'base_sta'  => $data['base_sta'],
                    ]);
                    $this->line('- ' . $niantic_id . ' mis à jour');
                }
            }
        }
    }

    public function find($niantic_id)
    {
        $pokemon = Pokemon::where('niantic_id', $niantic_id)->first();
        if ($pokemon) {
            return $pokemon;
        }
        return false;
    }

    public function compare($pokemon, $data)
    {
        $diff = 0;
        foreach (['base_att', 'base_def', 'base_sta'] as $spec) {
            if (isset($data[$spec]) && $data[$spec] != $pokemon->$spec) {
                $diff++;
            }
        }
        return $diff;
    }
}