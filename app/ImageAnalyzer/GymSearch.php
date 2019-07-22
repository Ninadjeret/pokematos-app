<?php
namespace App\ImageAnalyzer;

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
            $names[] = Helpers::sanitize($gym->niantic_name);
        }
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
     * @return type
     */
    function getAllIdentifiers() {

        $identifiers = array();

        foreach( $this->gyms as $gym ) {

            $name = Helpers::sanitize($gym->niantic_name);
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
    function findGym( $query, $min = 50 ) {
        $this->query = $query;
        $sanitizedQuery = Helpers::sanitize($this->query);
        if( $this->isBlackListed($sanitizedQuery) ) {
            Log::debug('Query black listed');
            return false;
        }
        foreach( $this->getAllIdentifiers() as $pattern => $data ) {
            if( strstr($sanitizedQuery, $pattern) && $data->percent >= $min ) {
                $gym = Stop::find($data->gymId);
                return $gym;
            }
        }
        return false;
    }

}
