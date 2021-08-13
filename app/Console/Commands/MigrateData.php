<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:data';

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
        $files = array_diff(scandir( base_path().'/database/data' ), array('..', '.'));
        $this->line(count($files));
        foreach( $files as $file ) {
            $type = pathinfo($file)['filename'];
            $this->line($type);

            switch($type) {

                case 'quest_reward_types':
                    $this->importQuestRewardTypes($file);
            }
        }
    }

    public function importQuestRewardTypes($file)
    {
        $imported = 0;
        $data = json_decode(file_get_contents(base_path().'/database/data/'.$file));
        if( $data ) {
            foreach( $data as $item ) {
                $type = \App\Models\QuestRewardType::updateOrCreate(
                    ['slug' => $item->slug],
                    ['niantic_id' => $item->slug, 'name' => $item->slug]                    
                );
                if( $type ) {
                    $imported++;
                    $path = "https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Items/Bag_Dragon_Scale_Sprite.png";
                    $base = storage_path() . "/app/public/img/items/base/item_{$type->slug}.png";
                    $quest = storage_path() . "/app/public/img/items/quest/item_{$type->slug}.png";
                    \App\Core\Tools\PokemonImagify::createQuestRewardTypeThumbnail($path, $base );
                    \App\Core\Tools\PokemonImagify::createQuestRewardTypeMapThumbnail($path, $quest );
                }
            }
        }
        $this->line("{$imported} QuestRewardTypes imported");
    }
}
