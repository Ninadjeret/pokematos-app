<?php

namespace App\Console\Commands;

use App\Models\Guild;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
        $path = 'tests/analyzer/raid/eggv2';
        $images = array_diff(scandir(storage_path($path)), array('..', '.'));
        foreach ($images as $image) {
            $complete_path = storage_path($path) . '/' . $image;
            $analyzer = new ImageAnalyzer($complete_path, Guild::find(1));
            $egg_level = $analyzer->getEggLevelv2();
        }
    }
}