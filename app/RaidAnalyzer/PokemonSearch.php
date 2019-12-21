<?php
namespace App\RaidAnalyzer;

use App\Models\Pokemon;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\Log;

class PokemonSearch {

    /**
     *
     */
    function __construct() {
        $this->debug = false;
        $this->query = false;
        $this->pokemons = Pokemon::where('boss', 1)->get();
        $this->sanitizedNames = $this->getSanitizedNames();
    }


    /**
     *
     * @return type
     */
    function getSanitizedNames() {
        $names = array();
        foreach( $this->pokemons as $pokemon ) {
            $names[] = Helpers::sanitize($pokemon->name_ocr);
        }
        return $names;
    }


    /**
     *
     * @return type
     */
    function getAllIdentifiers() {
        $identifiers = array();

        foreach( $this->pokemons as $pokemon ) {
            $name = Helpers::sanitize($pokemon->name_ocr);
            $name = $this->sanitizePattern($name);
            $nb_chars = strlen($name);

            //Parcours
            $debut = 0;
            while( $debut < $nb_chars - 1) {

                $fin = $nb_chars - $debut;
                while( $fin > 2 ) {
                    $pattern = mb_strimwidth($name, $debut, $fin);
                    //echo $pattern.'<br>';
                    $is_find = 0;
                    foreach( $this->sanitizedNames as $sanitizedName ) {
                        if( strstr($sanitizedName, $pattern) ) {
                            $is_find++;
                        }
                    }

                    if( $is_find === 1 ) {
                        //echo 'Identifiant OK<br>';
                        $identifiers[$pattern] = (object) array(
                            'pokemonId' => $pokemon->id,
                            'percent' => round( strlen($pattern) * 100 / $nb_chars )
                        );
                    }

                    $fin--;
                }

                $debut++;
            }
            //die();
        }
        $keys = array_map('strlen', array_keys($identifiers));
        array_multisort($keys, SORT_DESC, $identifiers);
        //Log::debug( print_r($identifiers, true) );
        return $identifiers;
    }


    /**
     *
     * @param type $query
     * @param type $min
     * @return boolean|\POGO_gym
     */
    function findPokemon( $query, $min = 50 ) {
        $this->query = $query;
        $sanitizedQuery = Helpers::sanitize($this->query);
        foreach( $this->getAllIdentifiers() as $pattern => $data ) {

            if( strstr($sanitizedQuery, $pattern) && $data->percent >= $min ) {
                $pokemon = Pokemon::find($data->pokemonId)
                return (object) [
                    'pokemon' => $pokemon,
                    'probability' => $data->percent,
                ];
            }
        }
        return false;
    }

    public function findPokemonFromFragments( $start, $end ) {

        $sanitized_start = Helpers::sanitize($start);
        $sanitized_end = Helpers::sanitize($end);

        foreach( $this->pokemons as $pokemon ) {
            $sanitized_name = Helpers::sanitize($pokemon->getRaidName());
            if( preg_match('/^'.$sanitized_start.'/i', $sanitized_name) && preg_match('/'.$sanitized_end.'$/i', $sanitized_name) ) {
                return $pokemon;
            }
        }

        return false;
    }

    public function sanitizePattern( $pattern ) {
        return str_replace('%e2%99%82', '', $pattern);
    }

}
