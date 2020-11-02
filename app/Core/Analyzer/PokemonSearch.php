<?php

namespace App\Core\Analyzer;

use App\Core\Helpers;
use App\Models\Quest;
use App\Models\Pokemon;
use Illuminate\Support\Collection;
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
        $this->pokemons = new Collection();
        $this->sanitizedNames = [];
        $this->accuracy = 50;
        $this->num_line = false;
    }

    public static function init()
    {
        return new PokemonSearch();
    }

    public function addQuestPokemon()
    {
        $quest_pokemon = Quest::all();
        $collection = new Collection();
        foreach ($quest_pokemon as $quest) {
            if ($quest->pokemons) {
                foreach ($quest->pokemons as $pokemon) {
                    if (!$collection->has($pokemon->id)) $collection->put($pokemon->id, $pokemon);
                }
            }
        }
        $this->pokemons = $this->pokemons->merge($collection);
        return $this;
    }

    public function addRaidPokemon()
    {
        $pokemon = Pokemon::where('boss', 1)->get();
        $this->pokemons = $this->pokemons->merge($pokemon);
        return $this;
    }

    /**
     *
     * @return type
     */
    private function getSanitizedNames()
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

    public function setAccuracy($acc)
    {
        $this->accuracy = $acc;
        return $this;
    }


    /**
     *
     * @param type $query
     * @param type $min
     * @return boolean|\POGO_gym
     */
    function find($query = null, $cp = null)
    {
        $this->sanitizedNames = $this->getSanitizedNames();
        $result = false;
        if (is_array($query)) {
            $result = $this->findPokemonFromName($query, $this->accuracy);
        } else {
            $result = $this->findPokemonFromstring($query, $this->accuracy);
        }

        if (!$result && !empty($cp)) {
            $result = $this->findPokemonFromCp($cp);
        }
        if (!$result && is_array($query)) {
            $result = $this->findPokemonFromFragments($query[1], $query[2]);
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
            $sanitized_name = Helpers::sanitize($pokemon->name_ocr);
            if (preg_match('/^' . $sanitized_start . '/i', $sanitized_name) && preg_match('/' . $sanitized_end . '$/i', $sanitized_name)) {
                return (object) [
                    'pokemon' => $pokemon,
                    'probability' => 75,
                ];;
            }
        }

        return false;
    }

    public function sanitizePattern($pattern)
    {
        return str_replace('%e2%99%82', '', $pattern);
    }
}