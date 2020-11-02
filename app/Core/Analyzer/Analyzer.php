<?php

namespace App\Core\Analyzer;

use Illuminate\Support\Str;

use App\Core\Analyzer\GymSearch;
use App\Core\Analyzer\MicrosoftOCR;
use App\Core\Analyzer\RewardSearch;
use Illuminate\Support\Facades\Log;
use App\Core\Analyzer\EggClassifier;
use App\Core\Analyzer\PokemonSearch;

abstract class Analyzer
{

    function __construct()
    {

        $this->debug = false;

        $this->guild = $this->args['guild'];
        $this->user = $this->args['user'];
        $this->channel_discord_id = $this->args['channel_discord_id'];
        $this->source_url = isset($this->args['source_url']) ? $this->args['source_url'] : null;
        $this->source_text = isset($this->args['source_text']) ? $this->args['source_text'] : null;

        $this->start = microtime(true);

        if ($this->debug) $this->_log('========== Début du traitement ' . $this->source_url . ' ==========');

        $this->gymSearch = new GymSearch($this->guild);
        $this->pokemonSearch = new PokemonSearch();
        $this->rewardSearch = new RewardSearch();
        $this->MicrosoftOCR = new MicrosoftOCR();

        if (is_callable([$this, 'initImageable'])) $this->initImageable();
        if (is_callable([$this, 'initTextable'])) $this->initTextable();
    }

    public function perform()
    {
        $this->run();

        if ($this->user) {
            $this->addLog();
        }

        $time_elapsed_secs = microtime(true) - $this->start;
        if ($this->debug) $this->_log('========== Fin du traitement ' . $this->imageData->source . ' (' . round($time_elapsed_secs, 3) . 's) ==========');
    }

    protected function _log($text, $extra = '')
    {
        if (is_array($text)) {
            Log::channel('raids')->info(print_r($text, true));
        } else {
            $this->result->logs .= "{$text}\r\n";
            Log::channel('raids')->info($text);
        }
    }

    public function addLog()
    {

        //Construction du tableau
        $success = ($this->result->error) ? false : true;
        $result = $this->getLogResult();

        //Ajout du log
        \App\Models\Log::create([
            'city_id' => $this->guild->city->id,
            'guild_id' => $this->guild->id,
            'type' => 'analysis-img',
            'success' => $success,
            'error' => $this->result->error,
            'source_type' => $this->source_type,
            'source' => $this->source_type == 'text' ? $this->source_text : $this->source_url,
            'result' => $result,
            'user_id' => ($this->user) ? $this->user->id : 0,
            'channel_discord_id' => $this->channel_discord_id
        ]);
    }
}