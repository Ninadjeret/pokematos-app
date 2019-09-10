<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        $process = $this->confirm('Nous nous apprêtons à Installer Pokématos. C\'est parti ?');
        if( !$process ) return;
        $bar = $this->output->createProgressBar(10);

        //Installation de composer
        $this->info( $this->title('1/ Installation des dépendances nodejs') );
        exec('npm install');

        //Installation de composer
        $this->info( $this->title('2/ Installation des dépendances composer') );
        exec('composer install');

        //php -r \"copy('.env.example', '.env'
        //Création du fichief de configuration
        $this->info( $this->title('3/ Installation du fichier de configuraiton .env') );
        try {
            file_get_contents('./.env');
            $this->line('Le fichier existe déja');
        } catch (\Exception $e) {
            exec('copy .env.example .env');
            $this->line('Création du fichier effectuée');
        }


        //Gestion de la BDD
        $this->info( $this->title('4/ Connexion MySQL') );
        $db_result = false;
        $retry = true;
        while( !$db_result && $retry ) {

            $db = env('DB_HOST');
            if( empty($db) || $db == 'null' ) {
                $db = $this->ask('Quel est l\'URL de votre base de données (souvent localhost)');
                exec("php artisan env:set DB_HOST {$db}");
            }

            $db_name = env('DB_DATABASE');
            if( empty($db_name) || $db_name == 'null' ) {
                $db_name = $this->ask('Quel est le nom de votre base de données');
                exec("php artisan env:set DB_DATABASE {$db_name}");
            }

            $db_user = env('DB_USERNAME');
            if( empty($db_user) || $db_user == 'null' ) {
                $db_user = $this->ask('Quel est l\'utilisateur de votre base de données');
                exec("php artisan env:set DB_USERNAME {$db_user}");
            }

            $db_pwd = env('DB_PASSWORD');
            if( empty($db_pwd) || $db_pwd == 'null' ) {
                $db_pwd = $this->ask('Quel est le mot de passe de votre base de données');
                exec("php artisan env:set DB_PASSWORD {$db_pwd}");
            }

            try {
                //$result = DB::connection()->getPdo();
                $connection = mysqli_connect($db,$db_user,$db_pwd,$db_name);
                $this->line('Connexion à MySQL établie avec succès');
                $db_result = true;
                $retry = false;
            } catch (\Exception $e) {
                $this->error('Connexion à MySQL impossible : '.$e->getMessage());
                $db_result = false;
                $retry = $this->confirm('Modifier les paramètres de connexion et réessayer ?');
                if( $retry ) {
                    exec("php artisan env:set DB_HOST null");
                    exec("php artisan env:set DB_DATABASE null");
                    exec("php artisan env:set DB_USERNAME null");
                    exec("php artisan env:set DB_PASSWORD null");
                }
            }
        }
        if( !$db_result && !$retry ) {
            $this->line('Installation annulée');
            return;
        }

        $this->call('config:cache');
        $this->call('config:clear');
        $this->call('cache:clear');

        //Installation Mysql
        $this->info( $this->title('5/ Installation de la base de données') );
        $tables = array_map('reset', \DB::select('SHOW TABLES'));
        if( !empty($tables) ) {
            $continue = $this->confirm("La base {$db_name} contient déja des tables. L'installation va les écraser. SOuhaitez-vous continuer ?");
            if( !$continue ) {
                $this->line('Installation annulée');
                return;
            }
        }
        $this->call('migrate:fresh');

        $this->info( $this->title('6/ Génération des informations depuis le gameMaster') );
        $this->call('gamemaster:update');

        //Installation
        $this->info( $this->title('7/ Génération des clés') );
        $this->line('Génération des clés Laravel...');
        $this->call('key:generate');
        $this->line("Génération du JWT");
        $this->call("passport:install");
        $api_token = env('BOT_TOKEN');
        if( empty($api_token) || $api_token == 'null' ) {
            $this->line('Génération des clés d\'API...');
            $api_token = str_random(20);
            exec("php artisan env:set BOT_TOKEN {$api_token}");
            $this->info("Clé d'API générée avec succès : {$api_token}");
        }

        //Configuration de la première ville
        $this->info( $this->title('8/ Paramétrage pour votre ville') );
        $this->line("D'autres villes pourront être ajoutées par la suite");
        $city_name = $this->ask('Quel est le nom de la ville pour laquelle vous souhaiter utiliser Pokématos ?');
        $this->call('guild:add', ['city' => $city_name]);

        //Fin
        $this->info( $this->title('Installation effectuée avec succès') );
    }

    public function title($value) {
        return "\r\n\r\n-----------------------------------------------\r\n{$value}\r\n-----------------------------------------------";
    }
}
