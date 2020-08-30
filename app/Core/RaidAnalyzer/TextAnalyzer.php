<?php

namespace App\Core\RaidAnalyzer;

use App\Core\Discord;
use Illuminate\Support\Facades\Log;
use App\Core\RaidAnalyzer\GymSearch;
use App\Core\Discord\MessageTranslator;
use App\Core\RaidAnalyzer\PokemonSearch;

class TextAnalyzer
{

    function __construct($source, $guild, $user = false, $channel_discord_id = false)
    {

        $this->debug = true;

        $this->result = (object) array(
            'gym' => false,
            'gym_probability' => 0,
            'eggLevel' => false,
            'pokemon'   => false,
            'pokemon_probability' => 0,
            'date' => false,
            'error' => false,
            'logs' => '',
        );

        $this->start = microtime(true);
        if ($this->debug) $this->_log('========== Début du traitement ' . $source . ' ==========');

        $this->guild = $guild;
        $this->user = $user;
        $this->text = MessageTranslator::from($this->guild)->translate($source);
        $this->channel_discord_id = $channel_discord_id;
        $this->gymSearch = new GymSearch($guild);
        $this->pokemonSearch = new PokemonSearch();

        //Result
        if ($this->isValid()) {
            $this->run();
        }
    }

    private function _log($text, $extra = '')
    {
        if (is_array($text)) {
            Log::channel('raids')->info(print_r($text, true));
        } else {
            $this->result->logs .= "{$text}\r\n";
            Log::channel('raids')->info($text);
        }
    }

    public function isValid()
    {
        $prefixes = $this->guild->settings->raidreporting_text_prefixes;
        if (empty($prefixes)) {
            $this->result->error = 'Aucun préfixe n \'est renseigné';
            return false;
        }
        foreach ($prefixes as $prefix) {
            $prefix = trim($prefix);
            if (strpos($this->text, $prefix) === 0) {
                return true;
            }
        }
        $this->result->error = 'Ce texte ne semble pas être une annonce de raid';
        return false;
    }

    public function run()
    {

        $this->result->date = $this->getTime();
        $this->result->gym = $this->getGym();
        $this->result->eggLevel = $this->getEggLevel();
        $this->result->pokemon = $this->getPokemon();
        if ($this->result->pokemon) {
            $this->result->eggLevel = $this->result->pokemon->boss_level;
        }
        $this->addLog();
        $time_elapsed_secs = microtime(true) - $this->start;
        if ($this->debug) $this->_log('========== Fin du traitement ' . $this->text . ' (' . round($time_elapsed_secs, 3) . 's) ==========');
    }

    public function getEggLevel()
    {

        //Recherche de base avec tetes
        preg_match('/\d(\s?)t[eéèê]te(s?)/i', $this->text, $matches);
        if (!empty($matches)) {
            return (int) preg_replace('`[^0-9]`', '', $matches[0]);
        }

        //Recherche de la version simplifiée
        preg_match('/\d(\s?)t\b/i', $this->text, $matches);
        if (!empty($matches)) {
            return (int) preg_replace('`[^0-9]`', '', $matches[0]);
        }

        if (stristr($this->text, 'mega') || stristr($this->text, 'méga')) {
            return 7;
        }

        return false;
    }

    public function getTime()
    {

        preg_match('/(jusqu\'a|jusqu\'à|jusqu’a|jusqu’à|depop|dépop|depop à|depop a|dépop à|dépop à|fin à|fin a)\s(\d?)\d(\s?)[h:](\s?)(\d?\d?)\b/i', $this->text, $dates_fin);
        if (!empty($dates_fin)) {
            return $this->getTimeFromEndDate($dates_fin[0]);
        }

        preg_match('/(\d?)\d(\s?)[h:](\s?)(\d?\d?)\b/i', $this->text, $dates_debut);
        if (!empty($dates_debut)) {
            return $this->getTimeFromStartDate($dates_debut[0]);
        }

        preg_match('/(d(e|é)pope?\sdans|reste(\sencore)?|pour(\sencore)?)\s\d?\d\s?(min|minute|minutes)/i', $this->text, $delais_fin);
        if (!empty($delais_fin)) {
            return $this->getTimeFromEndDelay($delais_fin[0]);
        }

        preg_match('/(dans|d\'ici)\s\d?\d\s?(min|minute|minutes)/i', $this->text, $delais_debut);
        if (!empty($delais_debut)) {
            return $this->getTimeFromStartDelay($delais_debut[0]);
        }
    }

    public function getTimeFromStartDate($date_string)
    {
        $dates = (strstr($date_string, ':')) ? explode(':', $date_string) : explode('h', $date_string);
        $hours = preg_replace('`[^0-9]`', '', $dates[0]);
        $minutes = preg_replace('`[^0-9]`', '', $dates[1]);
        if (empty($minutes)) $minutes = '00';
        $date = \DateTime::createFromFormat('H:i', $hours . ':' . $minutes);
        return $date->format('Y-m-d H:i:s');
    }

    public function getTimeFromEndDate($date_string)
    {
        $dates = (strstr($date_string, ':')) ? explode(':', $date_string) : explode('h', $date_string);
        $hours = preg_replace('`[^0-9]`', '', $dates[0]);
        $minutes = preg_replace('`[^0-9]`', '', $dates[1]);
        $date = \DateTime::createFromFormat('H:i', $hours . ':' . $minutes);
        $date->modify('- 45 minutes');
        return $date->format('Y-m-d H:i:s');
    }

    public function getTimeFromStartDelay($date_string)
    {
        $minutes = preg_replace('`[^0-9]`', '', $date_string);
        $date = new \DateTime();
        $date->modify('+ ' . $minutes . ' minutes');
        return $date->format('Y-m-d H:i:s');
    }

    public function getTimeFromEndDelay($date_string)
    {
        $minutes = preg_replace('`[^0-9]`', '', $date_string);
        $minutes = 45 - $minutes;
        $date = new \DateTime();
        $date->modify('- ' . $minutes . ' minutes');
        return $date->format('Y-m-d H:i:s');
    }

    function getGym()
    {
        $query = $this->text;
        $result = $this->gymSearch->findGym($query, 70);
        if ($result) {
            if ($this->debug) $this->_log('Gym finded in database : ' . $result->gym->name);
            $this->result->gym_probability = $result->probability;
            return $result->gym;
        }
        $this->result->error = "L'arène n'a pas été trouvée";
        if ($this->debug) $this->_log('Nothing found in database :(');
    }

    function getPokemon()
    {
        $query = $this->text;
        $result = $this->pokemonSearch->findPokemon($query, $cp = null, 70);
        if ($result) {
            if ($this->debug) $this->_log('Pokemon finded in database : ' . $result->pokemon->name_fr);
            $this->result->pokemon_probability = $result->probability;
            return $result->pokemon;
        }
        if (!$this->result->eggLevel) $this->result->error = "Aucun Pokémon trouvé";
        if ($this->debug) $this->_log('Nothing found in database :(');
        return false;
    }

    public function addLog()
    {

        //Construction du tableau
        $success = ($this->result->error) ? false : true;
        $result = [
            'gym' => $this->result->gym,
            'gym_probability' => $this->result->gym_probability,
            'date' => $this->result->date,
            'pokemon' => $this->result->pokemon,
            'pokemon_probability' => $this->result->pokemon_probability,
            'egg_level' => $this->result->eggLevel,
            'text' => $this->text,
        ];

        Log::channel('raids')->info(print_r($this->result, true));

        //Ajout du log
        \App\Models\Log::create([
            'city_id' => $this->guild->city->id,
            'guild_id' => $this->guild->id,
            'type' => 'analysis-text',
            'success' => $success,
            'error' => $this->result->error,
            'source_type' => 'text',
            'source' => $this->text,
            'result' => $result,
            'user_id' => ($this->user) ? $this->user->id : 0,
            'channel_discord_id' => $this->channel_discord_id
        ]);
    }
}