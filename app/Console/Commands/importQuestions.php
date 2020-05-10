<?php

namespace App\Console\Commands;

use App\Models\QuizTheme;
use App\Models\QuizQuestion;
use Illuminate\Console\Command;

class importQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:questions';

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
        $files = array_diff(scandir( storage_path().'/app/questions' ), array('..', '.'));
        $nb = 0;
        $nb_files = 0;
        $total_files = count($files);

        foreach( $files as $file ) {
            $nb_files++;
            $this->line("{$nb_files}/{$total_files} - Traitement du fichier {$file}");
            $questions = json_decode( file_get_contents( storage_path().'/app/questions/'.$file ) );
            foreach( $questions as $data ) {
                $nb++;
                $question = QuizQuestion::firstOrCreate([
                    'question' => $data->question
                ],[
                    'answer' => $data->answer
                ]);
                $question->update([
                    'answer' => $data->answer,
                    'alt_answers' => explode(', ', $data->alt_answers),
                    'explanation' => $data->explanation,
                    'difficulty' => $data->difficulty,
                    'about_pogo' => $data->about_pogo,
                    'theme_id' => ( !empty($data->theme) ) ? QuizTheme::firstOrCreate(['name' => $data->theme])->id : null,
                ]);
            }
        }

        $this->info("{$nb} questions importées ou mises à jour");
    }
}
