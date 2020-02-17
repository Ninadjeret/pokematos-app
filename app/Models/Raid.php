<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Models\Stop;
use App\Models\Pokemon;
use App\Models\UserAction;
use App\Models\raidChannel;
use App\Models\RaidMessage;
use RestCord\DiscordClient;
use Illuminate\Support\Facades\Log;

class Raid extends Model {

    protected $fillable = ['status', 'pokemon_id', 'egg_level'];
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
        $annonce = $this->getLastUserAction();
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

    public function getLastUserAction( $include_auto = false ) {
        if( $include_auto ) {
            $annonce = UserAction::where('type', 'like', 'raid-%')
                ->where('relation_id', $this->id)
                ->where('type', '!=', 'raid-duplicate')
                ->orderBy('created_at', 'desc')
                ->first();
            return $annonce;
        }
        $annonce = UserAction::where('type', 'like', 'raid-%')
            ->where('relation_id', $this->id)
            ->where('type', '!=', 'raid-duplicate')
            ->where('source', '!=', 'auto')
            ->orderBy('created_at', 'desc')
            ->first();
        return $annonce;
    }

    public function getUserActions() {
        $annonces = UserAction::where('type', 'like', 'raid-%')
            ->where('relation_id', $this->id)
            ->orderBy('created_at', 'asc')
            ->get();
        return $annonces;
    }

    public function getGym() {
        return Stop::find( $this->gym_id );
    }

    public function isActive() {
        return ( !$this->isPassed() && !$this->isFuture() );
    }

    public function isPassed() {
        return ( $this->end_time < date('Y-m-d H:i:s') );
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
            } else {
                $announceType = 'raid-duplicate';
            }
        }

        else {

            //Gestion du level
            $egg_level = $args['egg_level'];
            if( isset( $args['pokemon_id']) ) {
                $pokemon = Pokemon::find($args['pokemon_id']);
                if($pokemon) $egg_level = $pokemon->boss_level;
            }

            //Enregistrement
            $raid = new Raid();
            $raid->city_id = $city->id;
            $raid->gym_id = $gym->id;
            $raid->egg_level = $egg_level;
            $raid->start_time = $args['start_time'];
            $raid->pokemon_id = ( isset( $args['pokemon_id']) && date('Y-m-d H:i:s') > $raid->start_time ) ? $args['pokemon_id'] : null ;
            $raid->ex = (isset($args['ex'])) ? $args['ex'] : false;
            $raid->save();
            $announceType = 'raid-create';

            //Gestion du statut
            if( $raid->isFuture() ) {
                $raid->update(['status' => 'future']);
            } elseif( $raid->isActive() ) {
                $raid->update(['status' => 'active']);
            }

            $guilds = Guild::where('city_id', $raid->city_id)->get();
            $startTime = new \DateTime($raid->start_time);
            if( $guilds && $raid->egg_level == 6 ) {
                $discord = new DiscordClient(['token' => config('discord.token')]);
                foreach( $guilds as $guild ) {
                    if( $guild->settings->raidsex_active && $guild->settings->raidsex_channels && $guild->settings->raidsex_channel_category_id ) {
                        $channel = $discord->guild->createGuildChannel([
                            'guild.id' => (int) $guild->discord_id,
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

            //Création de l'action utilisateur. permet ensuite la suppression du raid et les stats
            $announce = UserAction::create([
                'type' => $announceType,
                'source' => ( isset($args['source_type']) ) ? $args['source_type'] : 'map',
                'date' => date('Y-m-d H:i:s'),
                'user_id' => $args['user_id'],
                'relation_id' => $raid->id,
                'message_discord_id' => ( isset($args['message_discord_id']) ) ? $args['message_discord_id'] : null ,
                'channel_discord_id' => ( isset($args['channel_discord_id']) ) ? $args['channel_discord_id'] : null ,
                'guild_id' => ( isset($args['guild_id']) ) ? $args['guild_id'] : null ,
            ]);

            if( $announceType == 'raid-create' ) {
                $gym->touch();
                event( new \App\Events\RaidCreated( $raid, $announce ) );
            } elseif( $announceType == 'raid-update') {
                $gym->touch();
                event( new \App\Events\RaidUpdated( $raid, $announce ) );
            } elseif( $announceType == 'raid-duplicate') {
                event( new \App\Events\RaidDuplicate( $raid, $announce ) );
            }

            \App\Models\Log::create([
                'city_id' => $city->id,
                'guild_id' => null,
                'type' => $announceType,
                'success' => 1,
                'error' => null,
                'source_type' => ( isset($args['source_type']) ) ? $args['source_type'] : 'map',
                'source' => ( isset($args['source_type']) ) ? $args['source_type'] : 'map',
                'result' => json_encode([
                    'raid_id' => $raid->id
                ]),
                'user_id' => $args['user_id'],
                'channel_discord_id' => ( isset($args['channel_discord_id']) ) ? $args['channel_discord_id'] : null ,
            ]);

        }

        return $raid;

    }

    public static function updateStatuses() {
        $now = new \DateTime();
        $before = new \DateTime();
        $before->modify( '- 45 minutes' );
        $raids_ended = Raid::where('status', '!=', 'archived')
            ->where('start_time', '<=', $before->format('Y-m-d H:i:s') )
            ->get();
        if( !empty( $raids_ended ) ) {
            foreach( $raids_ended as $raid ) {
                $raid->update(['status' => 'archived']);
                event( new \App\Events\RaidEnded( $raid ) );
            }
        }
        $raids_active = Raid::where('status', 'future')
            ->where('start_time', '<=', $now->format('Y-m-d H:i:s') )
            ->get();
        if( !empty( $raids_active ) ) {
            foreach( $raids_active as $raid ) {
                $raid->update(['status' => 'active']);
                $bosses = Pokemon::where('boss_level', $raid->egg_level)->get();
                if( count($bosses) === 1 ) {
                    $raid->update(['pokemon_id' => $bosses[0]->id ]);
                    $announce = UserAction::create([
                        'type' => 'raid-update',
                        'source' => 'auto',
                        'date' => date('Y-m-d H:i:s'),
                        'user_id' => 0,
                        'raid_id' => $raid->id,
                    ]);
                    event( new \App\Events\RaidUpdated( $raid, $announce ) );
                }
            }
        }
    }

}
