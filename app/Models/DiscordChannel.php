<?php

namespace App\Models;

use App\Core\Discord\Discord;
use App\Models\Guild;
use Illuminate\Database\Eloquent\Model;

class DiscordChannel extends Model
{
    protected $fillable = ['relation_type', 'relation_id', 'guild_id', 'discord_id', 'connector_id', 'to_delete_at'];

    public function relation()
    {
        return $this->morphTo();
    }

    public function getGuildAttribute()
    {
        return Guild::find($this->guild_id);
    }

    public function suppr()
    {
        $result = Discord::deleteChannel([
            'channel.id' => (int) $this->discord_id,
        ]);
        if ($result) {
            $this->delete();
        } else {
            $this->to_delete_at = date('Y-m-d H:i:s');
        }
    }

    public static function getChannelDeletionTime($end_time, $delay)
    {
        switch ($delay) {
            case 'raidend':
                $to_delete_at = $end_time->format('Y-m-d H:i:s');
                break;
            case '15min':
                $end_time->modify('+15 minutes');
                $to_delete_at = $end_time->format('Y-m-d H:i:s');
                break;
            case '1h':
                $end_time->modify('+1 hour');
                $to_delete_at = $end_time->format('Y-m-d H:i:s');
                break;
            case '2h':
                $end_time->modify('+2 hours');
                $to_delete_at = $end_time->format('Y-m-d H:i:s');
                break;
            case 'dayend':
                $to_delete_at = $end_time->format('Y-m-d 23:59:00');
                break;
            default:
                $to_delete_at = $end_time->format('Y-m-d 23:59:00');
        }
        return $to_delete_at;
    }
}
