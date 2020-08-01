<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddZarbiForms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:zarbi';

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
        $forms =  ['U', 'L', 'T', 'R', 'A'];
        $Zarbi = \App\Models\Pokemon::where('pokedex_id', '201')->first();
        foreach ($forms as $form) {
            \App\Models\Pokemon::create([
                'pokedex_id' => '201',
                'niantic_id' => 'V0201_POKEMON_UNOWN_' . $form,
                'name_fr' => 'Zarbi ' . $form,
                'name_ocr' => 'Zarbi',
                'form_id' => $form,
                'base_att' => 136,
                'base_def' => 91,
                'base_sta' => 134,
                'shiny' => 1,
                'parent_id' => $Zarbi->id,
            ]);
        }
    }
}