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
     *
     * @param type $query
     * @param type $min
     * @return boolean|\POGO_gym
     */
    function findGym( $query, $min = 50 ) {
        $this->query = $query;
        $sanitizedQuery = Helpers::sanitize($this->query);

        //On supprime les éventuels queries blacklistées(surimpression, etc)
        if( $this->isBlackListed($sanitizedQuery) ) {
            Log::debug('Query black listed');
            return false;
        }

        //On fait la recherche de correspondance
        $best_perc = 0;
        $best_result = false;
        foreach( $this->sanitizedNames as $name => $gym_id ) {
            $similarity = similar_text($name, $sanitizedQuery);
            $perc = $similarity * 100 / strlen($name);
            if( $perc == 100 ) {
                return (object) [
                    'gym' => Stop::find($gym_id),
                    'probability' => 100
                ];
            } elseif( $perc > $best_perc ) {
                $best_perc = 0;
                $best_result = $gym_id;
            }
        }

        if( $best_perc > $min ) {
            return (object) [
                'gym' => Stop::find($best_result),
                'probability' => round($best_perc)
            ];
        }

        return false;
    }

}
