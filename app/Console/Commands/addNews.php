<?php

namespace App\Console\Commands;

use App\Models\News;
use App\Models\Guild;
use RestCord\DiscordClient;
use Illuminate\Console\Command;

class addNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:add {text}';

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
        $text = $this->argument('text');
        $text = str_replace("<br>", "\r\n", $text);
        $count = 0;
        $received_guilds = [];

        $news = News::create(['name' => $text, 'received' => 0]);

        $discord = new \RestCord\DiscordClient(['token' => config('discord.token')]);
        $guilds = Guild::all();
        foreach( $guilds as $guild ) {
            if( $guild->settings->comadmin_active && !empty( $guild->settings->comadmin_channel_discord_id ) && in_array('news', $guild->settings->comadmin_types) ) {
                $message = $discord->channel->createMessage(array(
                    'channel.id' => intval($guild->settings->comadmin_channel_discord_id),
                    'content' => $text,
                ));
                if( isset($message['id']) ) {
                    $count++;
                    $received_guilds[] = $guild->id;
                }
            }
        }

        $news->update([
            'received' => $count,
            'guilds' => json_encode($received_guilds)
        ]);
        $this->line("Actu envoyée à {$count} guilds");
    }
}
