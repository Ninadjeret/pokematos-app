<?php

namespace App\Console\Commands;

use App\User;
use App\Models\Raid;
use App\Models\Guild;
use App\Models\Quest;
use App\Models\Pokemon;
use App\Models\UserAction;
use App\Models\QuestInstance;
use App\Models\RocketInvasion;
use Illuminate\Console\Command;
use App\Core\Analyzer\GymSearch;
use App\Core\Rankings\UserRanking;
use App\Core\Tools\PokemonImagify;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Core\Analyzer\RewardSearch;
use Illuminate\Support\Facades\Log;
use App\Core\Analyzer\EggClassifier;
use App\Core\Analyzer\ImageAnalyzer;
use App\Core\Analyzer\PokemonSearch;
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

        $user = User::find(1);
        $ranking = UserRanking::forRaids()
            ->forUser($user)
            ->forPeriod('2010-01-01', '2020-11-23')
            ->forCity(1)
            ->getShort();
        $this->line(print_r($ranking, true));

        /*$instance = new \App\Core\Analyzer\Image\Raid([
            'source_url' => 'https://cdn.discordapp.com/attachments/446373100926271499/681247410789679138/Screenshot_20200223-210054.jpg',
            'guild' => Guild::find(1),
            'user' => User::find(1),
            'channel_discord_id' => null,
        ]);
        $instance->perform();
        $this->line(print_r($instance->result));*/

        /*$instance = new \App\Core\Analyzer\Text\Raid([
            'source_text' => '+raid Raid salamèche à chartres de biorez. Reste 20 min',
            'guild' => Guild::find(1),
            'user' => User::find(1),
            'channel_discord_id' => null,
        ]);
        $instance->perform();
        $this->line(print_r($instance->result));*/

        /*$instance = new \App\Core\Analyzer\Image\Quest([
            'source_url' => 'https://cdn.discordapp.com/attachments/602385822339039243/765171155644448768/Screenshot_20201012_131432_com.nianticlabs.pokemongo.jpg',
            'source_text' => 'Salamèche',
            'guild' => Guild::find(1),
            'user' => User::find(1),
            'channel_discord_id' => null,
        ]);

        $this->line(print_r($instance->getReward()));
        Log::debug(print_r($instance->getReward(), true));*/

        //$instance->perform();
        //$this->line(print_r($instance->result));

    }
}