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
        $this->line('Found '.count($files).' files');
        foreach( $files as $file ) {
            $type = pathinfo($file)['filename'];         
            switch($type) {
                case 'quest_reward_types':                
                    $this->importQuestRewardTypes($file);
            }
        }
    }

    public function importQuestRewardTypes($file)
    {
        $this->line('Import QuestRewardTypes');
        $imported = 0;
        $data = json_decode(file_get_contents(base_path().'/database/data/'.$file));
        if( $data ) {
            foreach( $data as $item ) {
                $type = \App\Models\QuestRewardType::updateOrCreate(
                    ['slug' => $item->slug],
                    ['niantic_id' => $item->niantic_id, 'name' => $item->name]                    
                );
                if( $type ) {
                    $imported++;
                    \App\Core\Tools\ThumbnailMaker::forItemBase($type);
                    \App\Core\Tools\ThumbnailMaker::forItemQuest($type);
                }
            }
        }
        $this->line("{$imported} QuestRewardTypes imported");
    }
}
