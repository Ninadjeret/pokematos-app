<?php

namespace App\Console\Commands;

use App\User;
use App\Models\Guild;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Core\Discord\MessageTranslator;
use App\Core\RaidAnalyzer\EggClassifier;
use App\Core\RaidAnalyzer\ImageAnalyzer;

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
        $translator = MessageTranslator::to(Guild::find(1))->addUser(User::find(1))->translate('Coucou {utilisateur}');
        $this->line(print_r($translator, true));
    }
}