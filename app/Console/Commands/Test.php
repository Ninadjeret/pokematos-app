<?php

namespace App\Console\Commands;

use App\Core\Discord;
use App\Models\Raid;
use App\Models\RaidGroup;
use RestCord\DiscordClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
        request()->merge(['connector_id' => 1]);
        $raid_group = RaidGroup::firstOrCreate(['guild_id' => 1, 'raid_id' => 57]);
        $raid_group->add(\App\User::find(3), 'present');
        /*$discord = new DiscordClient(['token' => config('discord.token')]);
        $result = $discord->channel->getChannel([
            'channel.id' => (int) 742732146384437260
        ]);*/
        /*$result = Discord::getGuildRoles(['guild.id' => (int) 377559922214305792], '@everyone');
        $this->line(print_r($result, true));*/
    }
}