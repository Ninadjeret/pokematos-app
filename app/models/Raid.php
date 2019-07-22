<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Models\Stop;
use App\Models\Pokemon;
use App\Models\Announce;
use App\Models\raidChannel;
use App\Models\RaidMessage;
use RestCord\DiscordClient;

class Raid extends Model {

    protected $fillable = ['status'];
    protected $hidden = ['gym_id', 'city_id', 'pokemon_id'];
    protected $appends = ['end_time', 'pokemon', 'source', 'channels', 'messages', 'thumbnail_url'];

    /*public function getGymAttribute() {
        return Stop::find($this->gym_id);
    }*/

    public function getEndTImeAttribute() {
        $endTime = new \DateTime($this->start_time);
        $endTime->modify('+ 45 minutes');
        return $endTime->format('Y-m-d H:i:s');
    }

    public function getPokemonAttribute() {
        if( empty( $this->pokemon_id ) ) {
            return false;
        }
        return Pokemon::find($this->pokemon_id);
    }

    public function getThumbnailUrlAttribute() {
        return 'https://assets.profchen.fr/img/pokemon/pokemon_icon_'.$this->pokedex_id.'_'.$this->form_id.'.png';
    }

    public function getSourceAttribute() {
        $annonce = $this->getLastAnnounce();
        if( empty( $annonce ) ) return false;
        $return = [
            'source' => $annonce->source,
            'user' => User::find($annonce->user_id),
        ];
        return $return;
    }

    public function getChannelsAttribute() {
        $channels = raidChannel::where('raid_id', $this->id)->get();
        if( $channels ) {
            return $channels;
        }
        return [];
    }

    public function getMessagesAttribute() {
        $messages = RaidMessage::where('raid_id', $this->id)->get();
        if( $messages ) {
            return $messages;
        }
        return [];
    }

    public function getLastAnnounce() {
        $annonce = Announce::where('raid_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->first();
        return $annonce;
    }

    public function getGym() {
        return Stop::find( $this->gym_id );
    }

    public function isFuture() {
        return ( $this->start_time > date('Y-m-d H:i:s') );
    }

    public function getStartTime() {
        return new \DateTime( $this->start_time );
    }

    public function getEndTime() {
        return new \DateTime( $this->end_time );
    }

    public static function add( $args ) {

        $gym = Stop::find( $args['gym_id'] );
        $city = City::find( $args['city_id'] );

        $announceType = false;

        if( $gym->getActiveRaid() || $gym->getFutureRaid() ) {
            $raid = $gym->raid;
            if( !$raid->pokemon_id && isset($args['pokemon_id']) && !empty($args['pokemon_id']) ) {
                $raid->pokemon_id = $args['pokemon_id'];
                $raid->save();
                $announceType = 'raid-update';
            }
        }

        else {
            $raid = new Raid();
            $raid->city_id = $city->id;
            $raid->gym_id = $gym->id;
            $raid->egg_level =$args['egg_level'];
            $raid->start_time = $args['start_time'];
            $raid->pokemon_id = ( isset( $args['pokemon_id']) && date('Y-m-d H:i:s') > $raid->start_time ) ? $args['pokemon_id'] : null ;
            $raid->ex = (isset($args['ex'])) ? $args['ex'] : false;
            $raid->save();
            $announceType = 'raid-create';

            $guilds = Guild::where('city_id', $raid->city_id)->get();
            $startTime = new \DateTime($raid->start_time);
            if( $guilds && $raid->egg_level == 6 ) {
                $discord = new DiscordClient(['token' => config('discord.token')]);
                foreach( $guilds as $guild ) {
                    if( $guild->settings->raidsex_active && $guild->settings->raidsex_channels && $guild->settings->raidsex_channel_category_id ) {
                        $channel = $discord->guild->createGuildChannel([
                            'guild.id' => $guild->discord_id,
                            'name' => $gym->name.'-'.$startTime->format('d').'-'.$startTime->format('m'),
                            'type' => 0,
                            'parent_id' => (int) $guild->settings->raidsex_channel_category_id
                        ]);
                        raidChannel::create([
                            'raid_id' => $raid->id,
                            'guild_id' => $guild->id,
                            'channel_discord_id' => $channel->id,
                        ]);
                    }
                }
            }
        }

        if( $announceType ) {
            $announce = Announce::create([
                'type' => $announceType,
                'source' => ( isset($args['source_type']) ) ? $args['source_type'] : 'map',
                'date' => date('Y-m-d H:i:s'),
                'user_id' => $args['user_id'],
                'raid_id' => $raid->id,
            ]);

            if( $announceType == 'raid-create' ) {
                event( new \App\Events\RaidCreated( $raid, $announce ) );
            } elseif( $announceType == 'raid-update') {
                event( new \App\Events\RaidUpdated( $raid, $announce ) );
            }
        }

        return $raid;

    }

}
