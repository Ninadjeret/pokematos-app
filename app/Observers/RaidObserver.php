<?php

namespace App\Observers;

use App\Raid;
use App\Guild;
use RestCord\DiscordClient;
use Illuminate\Support\Facades\Log;

class RaidObserver
{
    /**
     * Handle the raid "created" event.
     *
     * @param  \App\Raid  $raid
     * @return void
     */
    public function created(Raid $raid) {
        Log::debug('coucou');
        $guilds = Guild::where('city_id', $raid->city_id)->get();
        if( !$guilds ) return;
        if( $raid->egg_level != 6 ) return;

        $discord = new DiscordClient(['token' => config('discord.token')]);
        foreach( $guilds as $guild ) {
            if( $guild->settings->raidsex_active && $guild->settings->raidsex_channels && $guild->settings->raidsex_channel_category_id ) {
                $channel = $discord->guild->getGuildChannels([
                    'guild.id' => $guild->discord_id,
                    'name' => 'test',
                    'type' => 0,
                    'parent_id' => (int) $guild->settings->raidsex_channel_category_id
                ]);
            }
        }
    }

    /**
     * Handle the raid "updated" event.
     *
     * @param  \App\Raid  $raid
     * @return void
     */
    public function updated(Raid $raid)
    {
        //
    }

    /**
     * Handle the raid "deleted" event.
     *
     * @param  \App\Raid  $raid
     * @return void
     */
    public function deleted(Raid $raid)
    {
        //
    }

    /**
     * Handle the raid "restored" event.
     *
     * @param  \App\Raid  $raid
     * @return void
     */
    public function restored(Raid $raid)
    {
        //
    }

    /**
     * Handle the raid "force deleted" event.
     *
     * @param  \App\Raid  $raid
     * @return void
     */
    public function forceDeleted(Raid $raid)
    {
        //
    }
}
