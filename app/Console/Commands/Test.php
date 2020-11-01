<?php

namespace App\Console\Commands;

use App\User;
use App\Models\Guild;
use App\Models\Pokemon;
use Illuminate\Console\Command;
use App\Core\Tools\PokemonImagify;
use App\Core\Analyzer\RewardSearch;
use Illuminate\Support\Facades\Log;
use App\Core\Analyzer\EggClassifier;
use App\Core\Analyzer\ImageAnalyzer;
use App\Core\Discord\MessageTranslator;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        /*$instance = new \App\Core\Analyzer\Image\Quest([
            'source_url' => 'https://cdn.discordapp.com/attachments/602385822339039243/765171155644448768/Screenshot_20201012_131432_com.nianticlabs.pokemongo.jpg',
            'source_text' => 'Salamèche',
            'guild' => Guild::find(1),
            'user' => User::find(1),
            'channel_discord_id' => null,
        ]);

        print_r($instance->getReward());*/

        //$instance->perform();
        //print_r($instance->result);
    }
}