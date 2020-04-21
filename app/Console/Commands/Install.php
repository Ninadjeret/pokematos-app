<?php

namespace App\Console\Commands;

use Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Full first Installation';

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
        $process = $this->confirm('We gonna install Pokematos. Here we go ?');
        if( !$process ) return;
        $bar = $this->output->createProgressBar(10);

        //Installation de composer
        $this->info( $this->title('1/ Nodejs dependencies install') );
        //exec('npm install');

        //Installation de composer
        $this->info( $this->title('2/ Composer dependencies install') );
        //exec('composer install');

        //Création du fichief de configuration
        $this->info( $this->title('3/ .env file install') );
        try {
            file_get_contents('./.env');
            $this->line('File already exists');
        } catch (\Exception $e) {
            exec('copy .env.example .env');
            $this->line('File created');
        }


        //Gestion de la BDD
        $this->info( $this->title('4/ MySQL Connection') );

        $db = env('DB_HOST');
        $db_name = env('DB_DATABASE');
        $db_user = env('DB_USERNAME');
        $db_pwd = env('DB_PASSWORD');

        if( empty($db) || empty($db_name) || empty($db_user) || empty($db_pwd) ) {

            $db_result = false;
            while( !$db_result ) {

                $db = $this->ask('What is your database host ? (usually localhost)');
                $db_name = $this->ask('What is your database name');
                $db_user = $this->ask('What is your database user');
                $db_pwd = $this->ask('What is your database password');
                try {
                    //$result = DB::connection()->getPdo();
                    $connection = mysqli_connect($db,$db_user,$db_pwd,$db_name);
                    $this->line('Connection establish to MySQL');
                    $db_result = true;
                    exec("php artisan env:set DB_HOST {$db}");
                    exec("php artisan env:set DB_DATABASE {$db_name}");
                    exec("php artisan env:set DB_USERNAME {$db_user}");
                    exec("php artisan env:set DB_PASSWORD {$db_pwd}");
                } catch (\Exception $e) {
                    $this->error('Unable to connect to MySQL : '.$e->getMessage());
                    $db_result = false;
                }
            }

            $this->info('Install process stopped to include db information. Please restart php artisan install to continue installation process');
            return;

        } else {
            $connection = mysqli_connect($db,$db_user,$db_pwd,$db_name);
            //$this->line(print_r($connection, true));
            $this->line('Connection establish to MySQL');
        }


        //Installation Mysql
        $this->info( $this->title('5/ Database tables install') );
        $tables = array_map('reset', \DB::select('SHOW TABLES'));
        if( !empty($tables) ) {
            $continue = $this->confirm("{$db_name} database already contains tables. Install process will erase them. Are you sure you want to continue ?");
            if( !$continue ) {
                $this->line('Installation cancelled');
                return;
            }
        }
        $this->call('migrate:fresh');

        $this->info( $this->title('6/ Getting information from the GameMaster') );
        $this->call('gamemaster:update');

        //Bot Information
        $this->info( $this->title('7/ Register discord bot data') );
        $bot_id = env('DISCORD_POKEMATOS_ID');
        $bot_secret = env('DISCORD_POKEMATOS_SECRET');
        $bot_token = env('DISCORD_POKEMATOS_TOKEN');
        $bot_callback = env('DISCORD_POKEMATOS_CALLBACK');
        if( !empty($bot_id) && !empty($bot_secret) && !empty($bot_token) && !empty($bot_callback) ) {
            $this->info('Bot credentials are already registered');
        } else {
            $bot_id = $this->ask('What is Discord bot ID');
            exec("php artisan env:set DISCORD_POKEMATOS_ID {$bot_id}");
            $bot_secret = $this->ask('What is Discord bot decret key');
            exec("php artisan env:set DISCORD_POKEMATOS_SECRET {$bot_secret}");
            $bot_token = $this->ask('What is Discord bot token');
            exec("php artisan env:set DISCORD_POKEMATOS_TOKEN {$bot_token}");
            $bot_callback = $this->ask('What is Discord bot callback URL');
            exec("php artisan env:set DISCORD_POKEMATOS_CALLBACK {$bot_callback}");
        }

        //Installation
        $this->info( $this->title('8/ Générating various app keys') );
        $this->line('Generating Laravel auth keys...');
        $this->call('key:generate');
        $this->line("Generating JWT");
        $this->call("passport:install");
        $this->call("passport:keys");
        $api_token = env('BOT_TOKEN');
        if( empty($api_token) || $api_token == 'null' ) {
            $this->line('Generating API key');
            $api_token = str_random(20);
            exec("php artisan env:set BOT_TOKEN {$api_token}");
            $this->info("API key succesfully created : {$api_token}");
        }

        //Configuration de la première ville
        $this->info( $this->title('8/ Firt city install') );
        $this->line("Other cities or game areas can be installed later");
        $city_name = $this->ask('What is the name of the city or game area you are installing Pokematos for ?');
        $this->call('guild:add', ['city' => $city_name]);
        $guild= \App\Models\Guild::first();

        //Fin
        $urlencode = urlencode($bot_callback);
        $login_url = "https://discordapp.com/api/oauth2/authorize?client_id={$bot_id}&permissions=1544023121&redirect_uri={$urlencode}&scope=bot";
        $steup_command = '@{Your bot name, with discriminator} setup '.$guild->token;

        Log::info($login_url);
        Log::info($steup_command);

        $this->info( $this->title('Instalaltion succesfull') );
        $this->line('Before using Pokématos, you need to add your discord bot to your discord server, using this invit link :');
        $this->info($login_url);
        $this->line('After added the bot to your server, you need to run the command below in your discord server to link your discord server & Pokématos app');
        $this->info($steup_command);
        $this->line('For a move convenient way, invit URL & setup command have been added to storage > Logs file. You can copy-paster them easily');
    }

    public function title($value) {
        return "\r\n\r\n-----------------------------------------------\r\n{$value}\r\n-----------------------------------------------";
    }
}
