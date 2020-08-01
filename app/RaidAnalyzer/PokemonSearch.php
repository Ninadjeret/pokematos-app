<?php

namespace App\RaidAnalyzer;

use App\Models\Pokemon;
use App\Core\Helpers;
use Illuminate\Support\Facades\Log;

class PokemonSearch
{

    /**
     *
     */
    function __construct()
    {
        $this->debug = false;
        $this->query = false;
        $this->pokemons = Pokemon::where('boss', 1)->get();
        $this->sanitizedNames = $this->getSanitizedNames();
        $this->num_line = false;
    }


    /**
     *
     * @return type
     */
    function getSanitizedNames()
    {
        $names = array();
        foreach ($this->pokemons as $pokemon) {
            $names[Helpers::sanitize($pokemon->name_ocr)] = $pokemon->id;
            if ($pokemon->name_ocr != $pokemon->name_fr) {
                $names[Helpers::sanitize($pokemon->name_fr)] = $pokemon->id;
            }
        }
        $keys = array_map('strlen', array_keys($names));
        array_multisort($keys, SORT_DESC, $names);
        return $names;
    }


    /**
     *
     * @param type $query
     * @param type $min
     * @return boolean|\POGO_gym
     */
    function findPokemon($query = null, $cp = null, $min = 50)
    {
        $result = false;
        if (!empty($query) && is_array($query)) {
            $result = $this->findPokemonFromName($query, $min);
        } elseif (!empty($query)) {
            $result = $this->findPokemonFromstring($query, $min);
        }
        if (!$result && !empty($cp)) {
            $result = $this->findPokemonFromCp($cp);
        }

        //PATCHZARBI
        if ($result && $result->pokemon->pokedex_id == '201') {
            $zarbi = Pokemon::where('pokedex_id', '201')
                ->where('form_id', '00')
                ->frist();
            $result->pokemon = $zarbi;
        }

        return $result;
    }


    /**
     * [public description]
     * @var [type]
     */
    public function findPokemonFromstring($query, $min = 50)
    {
        $sanitizedQuery = Helpers::sanitize($query);
        foreach ($this->sanitizedNames as $name => $pokemon_id) {
            if (strstr($sanitizedQuery, $name)) {
                return (object) [
                    'pokemon' => Pokemon::find($pokemon_id),
                    'probability' => 100
                ];
            }
        }
        return false;
    }


    /**
     * [findPokemonFromName description]
     * @param  [type]  $query [description]
     * @param  integer $min   [description]
     * @return [type]         [description]
     */
    public function findPokemonFromName($query, $min = 50)
    {
        $best_perc = 0;
        $best_result = false;
        $num_line = false;
        $i = 0;
        foreach ($query as $line) {
            $sanitizedQuery = Helpers::sanitize($line);
            foreach ($this->sanitizedNames as $name => $pokemon_id) {
                $similarity = similar_text($name, $sanitizedQuery, $perc);
                if ($perc >= 100) {
                    $this->num_line = $i;
                    return (object) [
                        'pokemon' => Pokemon::find($pokemon_id),
                        'probability' => 100
                    ];
                } elseif ($perc > $best_perc) {
                    $best_perc = $perc;
                    $best_result = $pokemon_id;
                    $num_line = $i;
                }
            }
            $i++;
        }

        if ($best_perc > $min) {
            $this->num_line = $num_line;
            return (object) [
                'pokemon' => Pokemon::find($best_result),
                'probability' => round($best_perc)
            ];
        }

        return false;
    }

    public function findPokemonFromCp($cp)
    {
        $matching = [];
        foreach ($this->pokemons as $pokemon) {
            if ($pokemon->boss_cp == $cp) {
                $matching[] = $pokemon;
            }
        }
        if (!empty($matching) && count($matching) === 1) {
            return (object) [
                'pokemon' => $pokemon,
                'probability' => 100,
            ];
        }
        return false;
    }

    public function findPokemonFromFragments($start, $end)
    {

        $sanitized_start = Helpers::sanitize($start);
        $sanitized_end = Helpers::sanitize($end);

        foreach ($this->pokemons as $pokemon) {
            $sanitized_name = Helpers::sanitize($pokemon->getRaidName());
            if (preg_match('/^' . $sanitized_start . '/i', $sanitized_name) && preg_match('/' . $sanitized_end . '$/i', $sanitized_name)) {
                return $pokemon;
            }
        }

        return false;
    }

    public function sanitizePattern($pattern)
    {
        return str_replace('%e2%99%82', '', $pattern);
    }
}