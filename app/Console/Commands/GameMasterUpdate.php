<?php

namespace App\Console\Commands;

use App\Models\Pokemon;
use App\Core\Tools\GameMaster;
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

        $result = GameMaster::toUpdate();
        $to_add = $result->to_add;
        $to_update = $result->to_update;

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