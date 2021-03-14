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
use App\Models\DiscordMessage;
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
use App\Models\RaidGroup;
use App\Core\Discord\Messages\RaidChannelTopic;

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

        $message = DiscordMessage::where('discord_id', '820557203894304788')
        ->where('relation_type', 'raid')
        ->first();
        $raid = Raid::find($message->relation_id);
        request()->merge(['connector_id' => $message->connector_id]); // On récupère le connecteur pour savoir ou créer le canal de raid
        $raid_group = RaidGroup::firstOrCreate(['guild_id' => $message->guild_id, 'raid_id' => $raid->id]);
        //$raid_group->add( User::find(1), 'present', 2 );
        $raid_group->add( User::find(2), 'present', 1 );
        //$raid_group->add( User::find(3), 'present', 1 );
        //$raid_group->remove( User::find(1));

        /*$microsoftOCR = new \App\Core\Analyzer\MicrosoftOCR();
        $microsoftOCR->read('https://cdn.discordapp.com/attachments/475918919241170944/820242129158668309/Screenshot_20210313-111947.jpg');
        $this->line(print_r($microsoftOCR));*/

        /*$instance = new \App\Core\Analyzer\Image\Raid([
            'source_url' => 'https://cdn.discordapp.com/attachments/475918919241170944/820242129158668309/Screenshot_20210313-111947.jpg',
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
