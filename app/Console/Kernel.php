<?php

namespace App\Console;

use App\Models\Raid;
use App\Models\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //Mise à jour des status de Raid
        $schedule->command(Raid::updateStatuses())->everyMinute();

        //On agit sur les quiz.
        $schedule->call(function () {
            $events = Event::getActiveEvents('quiz');
            if( !empty( $events ) ) {
                foreach( $events as $event ) {
                    if( !$event->quiz ) continue;
                    $event->quiz->process();
                }
            }
        })->everyMinute();

        //On avertit le système quand on change de jour
        $schedule->call(function () {
            event( new \App\Events\DayChanged() );
        })->dailyAt('00:01');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
