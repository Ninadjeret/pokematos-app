<?php

namespace App\Console\Commands;

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
        $quiz = \App\Models\EventQuiz::find(5);
        $quiz->start();
        /*$discord = new DiscordClient([
            'token' => config('discord.token'),
        ]);

        try {
            $guild = $discord->guild->getGuild(['guild.id' => 48065406879879]);;
        }
        catch (GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
        }
        //Log::debug( print_r($discord, true) );
        //$this->line( print_r($guild, true) );
        $this->info('OK');*/
    }

}
