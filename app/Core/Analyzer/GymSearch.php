<?php

namespace App\Core\Analyzer;

use App\Models\Stop;
use App\Core\Helpers;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Core\Analyzer\PokemonSearch;

class GymSearch
{

    /**
     *
     */
    function __construct($guild)
    {
        $this->debug = false;
        $this->guild = $guild;
        $this->query = false;
        $this->pois = new Collection();
        $this->sanitizedNames = [];
        $this->accuracy = 50;
    }

    public static function init($guild)
    {
        return new GymSearch($guild);
    }

    public function addGyms( $ex = null )
    {
        $query = Stop::where('gym', 1)->where('city_id', $this->guild->city->id);
        if( $ex !== null ) {
            $ex ? $query->where('ex', 1) : $query->where('ex', 0) ;
        }
        $gyms = $query->get();
        $this->pois = $this->pois->merge($gyms);
        return $this;
    }

    public function addStops()
    {
        $gyms = Stop::where('gym', 0)
            ->where('city_id', $this->guild->city->id)
            ->get();
        $this->pois = $this->pois->merge($gyms);
        return $this;
    }

    public function setAccuracy($acc)
    {
        $this->accuracy = $acc;
        return $this;
    }

    /**
     *
     * @return type
     */
    private function getSanitizedNames()
    {
        $names = array();
        foreach ($this->pois as $gym) {
            $names[Helpers::sanitize($gym->niantic_name)] = $gym->id;
            if ($gym->niantic_name != $gym->name) {
                $names[Helpers::sanitize($gym->name)] = $gym->id;
            }
            if (!empty($gym->aliases)) {
                foreach ($gym->aliases as $alias) {
                    if (!array_key_exists($alias->name, $names)) {
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

    public function find($query)
    {
        $this->sanitizedNames = $this->getSanitizedNames();
        $result = false;

        //On checke si on trouve un résultat exact
        $result = $this->findExactGym($query, $this->accuracy);
        if ($result) {
            return (object) [
                'gym' => Stop::find($result->gym_id),
                'probability' => $result->probability
            ];
        } else {
            $result = $this->findGymFromString($query, $this->accuracy);
            return $result;
        }

        return false;
    }


    /**
     * [findExactGym description]
     * @param  [type]  $query [description]
     * @param  integer $min   [description]
     * @return [type]         [description]
     */
    public function findExactGym($query, $min = 50)
    {

        $sanitizedQuery = Helpers::sanitize($query);
        $array_probabilities = [];

        foreach ($this->sanitizedNames as $name => $gym_id) {
            if ($name == $sanitizedQuery) {
                return (object) [
                    'gym_id' => $gym_id,
                    'probability' => 100
                ];
            } elseif (strpos($name, $sanitizedQuery) === 0) {
                $array_probabilities[$gym_id] = 90;
            }
        }

        if (!empty($array_probabilities)) {
            return (object) [
                'gym_id' => array_key_first($array_probabilities),
                'probability' => 100 - (10 * count($array_probabilities))
            ];
        }

        return false;
    }



    /**
     *
     * @return type
     */
    function getAllIdentifiers()
    {
        $identifiers = array();
        foreach ($this->pois as $gym) {

            $names = [$gym->niantic_name, $gym->name];
            if (!empty($gym->aliases)) {
                foreach ($gym->aliases as $alias) {
                    if (!in_array($alias->name, $names)) {
                        $names[] = $alias->name;
                    }
                }
            }

            foreach ($names as $name) {
                $name = Helpers::sanitize($name);
                $nb_chars = strlen($name);
                //Parcours
                $debut = 0;
                while ($debut < $nb_chars - 1) {
                    $fin = $nb_chars - $debut;
                    while ($fin > 2) {
                        $pattern = mb_strimwidth($name, $debut, $fin);
                        //echo $pattern.'<br>';
                        $is_find = 0;
                        foreach ($this->sanitizedNames as $sanitizedName) {
                            if (strstr($sanitizedName, $pattern)) {
                                $is_find++;
                            }
                        }
                        if ($is_find === 1) {
                            //echo 'Identifiant OK<br>';
                            $identifiers[$pattern] = (object) array(
                                'gymId' => $gym->id,
                                'percent' => round(strlen($pattern) * 100 / $nb_chars)
                            );
                            $gym->id;
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
        return $identifiers;
    }


    /**
     *
     * @param type $query
     * @param type $min
     * @return boolean|\POGO_gym
     */
    function findGymFromString($query, $min = 50)
    {
        $this->query = $query;
        $sanitizedQuery = Helpers::sanitize($this->query);
        Log::channel('raids')->info("Gym query : {$sanitizedQuery}");
        $identifiers = $this->getAllIdentifiers();
        foreach ($identifiers as $pattern => $data) {
            if (strstr($sanitizedQuery, $pattern) && $data->percent >= $min) {
                $gym = Stop::find($data->gymId);
                return (object) [
                    'gym' => $gym,
                    'probability' => $data->percent
                ];
            }
        }
        return false;
    }
}
