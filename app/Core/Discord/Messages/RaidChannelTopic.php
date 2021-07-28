<?php

namespace App\Core\Discord\Messages;

use App\Models\Raid;
use App\Models\Guild;
use App\Models\RaidGroup;
use App\Models\DiscordChannel;
use App\Core\Raids\RaidHelpers;
use Illuminate\Support\Facades\Log;
use App\Core\Discord\MessageTranslator;

class RaidChannelTopic
{
    public function __construct(Raid $raid)
    {
        $this->raid = $raid;
    }

    public static function forRaid(Raid $raid)
    {
      return new RaidChannelTopic($raid);
    }

    public function forGuild(Guild $guild)
    {
        $this->guild = $guild;
        return $this;
    }

    public function get()
    {
        $prefix = $this->guild->settings->prefix;
        $nb_indication = $this->guild->settings->raidorga_nb_players ? ' <nb>' : '' ;
        $message = "__Comandes utiles__\r\n";
        $message .= "***{$prefix}present{$nb_indication}*** : Indiquer sa présence sur place\r\n";
        $message .= "***{$prefix}distance{$nb_indication}*** : Indiquer sa présence à distance\r\n";
        $message .= "***{$prefix}invitation{$nb_indication}*** : Indiquer sa présence sur invitation\r\n";
        $message .= "***{$prefix}quitter*** : Quitter le raid\r\n";
        return $message;
    }
}
