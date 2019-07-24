<?php

namespace App\RaidAnalyzer;

use App\RaidAnalyzer\GymSearch;
use App\RaidAnalyzer\PokemonSearch;
use Illuminate\Support\Facades\Log;

class TextAnalyzer {

    function __construct( $source, $guild ) {

        $this->debug = true;

        $this->result = (object) array(
            'gym' => false,
            'eggLevel' => false,
            'pokemon'   => false,
            'date' => false,
            'error' => false,
            'logs' => '',
        );

        $this->start = microtime(true);
        if( $this->debug ) $this->_log('========== Début du traitement '.$source.' ==========');

        $this->text = $source;
        $this->guild = $guild;
        $this->gymSearch = new GymSearch($guild);
        $this->pokemonSearch = new PokemonSearch();

        //Result
        if( $this->isValid() ) {
            $this->run();
        }
    }

    private function _log( $text, $extra = '' ) {
        if( is_array( $text ) ) {
            Log::debug( print_r($text, true) );
        } else {
            $this->result->logs .= "{$text}\r\n";
            Log::debug( $text );
        }
    }

    public function isValid() {
        $prefixes = explode( ',', $this->guild->settings->raidreporting_text_prefixes );
        if( empty( $prefixes ) ) {
            $this->result->error = 'Aucun préfixe n \'est renseigné';
            return false;
        }
        foreach( $prefixes as $prefix ) {
            $prefix = trim($prefix);
            if( strpos( $this->text, $prefix ) === 0 ) {
                return true;
            }
        }
        $this->result->error = 'Ce texte ne semble pas être une annonce de raid';
        return false;
    }

    public function run() {

        $this->result->date = $this->getTime();
        $this->result->gym = $this->gymSearch->findGym($this->text, 70);
        $this->result->pokemon = $this->pokemonSearch->findPokemon($this->text, 70);
        if( $this->result->pokemon ) {
            Log::debug('tutu');
            $this->result->eggLevel = $this->result->pokemon->boss_level;
        } else {
            Log::debug('tata');
            $this->result->eggLevel = $this->getEggLevel();
        }
        $this->getTime();
        $time_elapsed_secs = microtime(true) - $this->start;
        //$this->result->error = 'demo';
        if( $this->debug ) $this->_log('========== Fin du traitement '.$this->text.' ('.round($time_elapsed_secs, 3).'s) ==========');
    }

    public function getEggLevel() {

        //Recherche de base avec tetes
        preg_match('/\d(\s?)t[eéèê]te(s?)/i', $this->text, $matches);
        if( !empty( $matches ) ) {
            return (int) preg_replace('`[^0-9]`', '', $matches[0]);
        }

        //Recherche de la version simplifiée
        preg_match('/\d(\s?)t\b/i', $this->text, $matches);
        if( !empty( $matches ) ) {
            return (int) preg_replace('`[^0-9]`', '', $matches[0]);
        }

        return false;
    }

    public function getTime() {

        preg_match('/(jusqu\'a|jusqu\'à|depop|dépop|depop à|depop a|dépop à|dépop à)\s(\d?)\d(\s?)h(\s?)(\d?\d?)\b/i', $this->text, $dates_fin);
        if( !empty( $dates_fin ) ) {
            return $this->getTimeFromEndDate($dates_fin[0]);
        }

        preg_match('/(\d?)\d(\s?)h(\s?)(\d?\d?)\b/i', $this->text, $dates_debut);
        if( !empty( $dates_debut ) ) {
            return $this->getTimeFromStartDate($dates_debut[0]);
        }

    }

    public function getTimeFromStartDate( $date_string ) {
        $dates = explode('h', $date_string);
        $hours = preg_replace('`[^0-9]`', '', $dates[0]);
        $minutes = preg_replace('`[^0-9]`', '', $dates[1]);
        $date = \DateTime::createFromFormat('H:i', $hours.':'.$minutes);
        return $date->format('Y-m-d H:i:s');
    }

    public function getTimeFromEndDate( $date_string ) {
        $dates = explode('h', $date_string);
        $hours = preg_replace('`[^0-9]`', '', $dates[0]);
        $minutes = preg_replace('`[^0-9]`', '', $dates[1]);
        $date = \DateTime::createFromFormat('H:i', $hours.':'.$minutes);
        $date->modify('- 45 minutes');
        return $date->format('Y-m-d H:i:s');
    }

    function getGym() {

        $query = implode(' ', $this->ocr);
        $gym = $this->gymSearch->findGym($query, 70);
        if( $gym ) {
            if( $this->debug ) $this->_log('Gym finded in database : ' . $gym->name );
            return $gym;
        }
        if( $this->debug ) $this->_log('Nothing found in database :(' );

    }

    function getPokemon() {
        $pokemon = $this->pokemonSearch->findPokemon($query, 70);
        if( $pokemon ) {
            if( $this->debug ) $this->_log('Pokemon finded in database : ' . $pokemon->getNameFr() );
            return $pokemon;
        }

        if( $this->debug ) $this->_log('Nothing found in database :(' );
        return false;

    }

}
