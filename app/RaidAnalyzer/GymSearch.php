<?php
namespace App\RaidAnalyzer;

use App\Models\Stop;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\Log;

class GymSearch {

    /**
     *
     */
    function __construct( $guild ) {
        $this->debug = false;
        $this->query = false;
        $this->gyms = Stop::where('gym', 1)
            ->where('city_id', $guild->city->id)
            ->get();
        $this->sanitizedNames = $this->getSanitizedNames();
        $this->max_length = 0;
        $this->gym_name = false;
    }


    /**
     *
     * @return type
     */
    function getSanitizedNames() {
        $names = array();
        foreach( $this->gyms as $gym ) {
            $names[Helpers::sanitize($gym->niantic_name)] = $gym->id;
            if( $gym->niantic_name != $gym->name ) {
                $names[Helpers::sanitize($gym->name)] = $gym->id;
            }
            if( !empty($gym->aliases) ) {
                foreach( $gym->aliases as $alias ) {
                    if( !array_key_exists($alias->name, $names) ) {
                        $names[Helpers::sanitize($alias->name)] = $gym->id;
                    }
                }
            }
        }
        $keys = array_map('strlen', array_keys($names));
        array_multisort($keys, SORT_DESC, $names);

        $keys = array_keys($names);
        $this->max_length = strlen($keys[0]);

        return $names;
    }


    /**
     *
     * @param type $query
     * @return boolean
     */
    function isBlackListed( $query ) {
        $black_list = array('bonus');
        foreach( $black_list as $pattern ) {
            if( strstr($query, $pattern) ) {
                return true;
            }
        }
        return false;
    }


    /**
     * [extractGymName description]
     * @param  [type] $array [description]
     * @return [type]        [description]
     */
    public function extractGymName($array) {
        if( !is_array($array) ) return false;
        $num = 0;
        $i = 0;
        $name = false;
        foreach( $array as $line ) {
            $i++;
            if( empty(preg_match( '/^[0-9]:[0-9][0-9]:[0-9][0-9]/i', $line )) ) {
                $name = $line;
                $num = $i;
            }
        }

        if( $name ) {
            if( preg_match( '/^[0-9]:[0-9][0-9]:[0-9][0-9]/i', $array[$num] ) ) {
                //nothing
            } else {
                $name .= ' '.$array[$num];
            }
        }
        Log::debug('Gym name extracted : '.$name);
        $this->gym_name = $name;
        return $name;
    }


    /**
     * [findExactGym description]
     * @param  [type]  $query [description]
     * @param  integer $min   [description]
     * @return [type]         [description]
     */
    public function findExactGym( $query, $min = 50  ) {

        $sanitizedQuery = Helpers::sanitize($query);
        $array_probabilities = [];

        //On supprime les éventuels queries blacklistées(surimpression, etc)
        if( $this->isBlackListed($sanitizedQuery) ) {
            Log::debug('Query black listed');
            return false;
        }

        /**
        * ==================================================================
        * PREMIER TOUR
        * ==================================================================
        */
        foreach( $this->sanitizedNames as $name => $gym_id ) {
            if( $name == $sanitizedQuery ) {
                return (object) [
                    'gym_id' => $gym_id,
                    'probability' => 100
                ];
            }
            elseif( strpos($name, $sanitizedQuery) === 0 ) {
                $array_probabilities[$gym_id] = 90;
            }
        }

        $result = $this->extractBestProba($array_probabilities, $min, 0.25);
        if( $result ) {
            return $result;
        }

        /**
        * ==================================================================
        * DEUXIEME TOUR
        * ==================================================================
        */
        foreach( $this->sanitizedNames as $name => $gym_id ) {
            $similarity = similar_text($sanitizedQuery, $name, $perc);
            if( $perc >= 100 ) {
                return (object) [
                    'gym_id' => $gym_id,
                    'probability' => 100
                ];
            } elseif( $perc > $min ) {
                if( !array_key_exists($gym_id, $array_probabilities) || $array_probabilities[$gym_id] < $perc ) {
                    $array_probabilities[$gym_id] = $perc * 0.8;
                }
            }
        }

        $result = $this->extractBestProba($array_probabilities, $min, 0.15);
        if( $result ) {
            return $result;
        }

        return false;

    }


    /**
     *
     * @param type $query
     * @param type $min
     * @return boolean|\POGO_gym
     */
    function findGym( $query, $min = 50 ) {

        $array_probabilities = [];
        $this->query = $query;

        //On checke si on trouve un résultat exact
        if( is_array($query) ) {
            $gymName = $this->extractGymName($query);
            if( $gymName ) {
                $result = $this->findExactGym($gymName, $min);
                if( $result ) {
                    return (object) [
                        'gym' => Stop::find($result->gym_id),
                        'probability' => $result->probability
                    ];
                }
            }
            return false;
        }

        else {
            $result = $this->findGymFromString( $query, $min );
            return $result;
        }

        return false;

    }


    /**
     *
     * @return type
     */
    function getAllIdentifiers() {
        $identifiers = array();
        foreach( $this->gyms as $gym ) {
            foreach( array( $gym->niantic_name, $gym->name ) as $name ) {
                $name = Helpers::sanitize($name);
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
                                'gymId' => $gym->id,
                                'percent' => round( strlen($pattern) * 100 / $nb_chars )
                            );$gym->id;
                        }
                        $fin--;
                    }
                    $debut++;
                }
                $identifiers[$name] = (object) array(
                    'gymId' => $gym->id,
                    'percent' => 100
                );
            }
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
    function findGymFromString( $query, $min = 50 ) {
        $this->query = $query;
        $sanitizedQuery = Helpers::sanitize($this->query);
        if( $this->isBlackListed($sanitizedQuery) ) {
            Log::debug('Query black listed');
            return false;
        }
        $identifiers = $this->getAllIdentifiers();
        foreach($identifiers as $pattern => $data ) {
            if( strstr($sanitizedQuery, $pattern) && $data->percent >= $min ) {
                $gym = Stop::find($data->gymId);
                return (object) [
                    'gym' => $gym,
                    'probability' => $data->percent
                ];
            }
        }
        return false;
    }


    /**
     * [extractBestProba description]
     * @return [type] [description]
     */
     private function extractBestProba($array_probabilities, $min, $coefPart) {
         if( empty( $array_probabilities ) ) {
             return false;
         }

         arsort($array_probabilities);
         $best_proba = array_key_first($array_probabilities);

         if( count($array_probabilities) > 1 ) {
             $count = count($array_probabilities);
             $coef =  1 - ( $count * $coefPart );
             if( $coef <= 0.5 ) $coef = 0.5;
             $array_probabilities[$best_proba] = $array_probabilities[$best_proba] - ($array_probabilities[$best_proba] * $coef);
         }

         if( $array_probabilities[$best_proba] >= $min ) {
             return (object) [
                 'gym_id' => $best_proba,
                 'probability' => round($array_probabilities[$best_proba])
             ];
         }

         return false;
     }

}
