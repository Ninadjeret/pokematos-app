<?php

namespace App\Core\Discord\Messages;

use App\Models\Raid;
use App\Models\Guild;
use App\Models\RaidGroup;
use App\Models\DiscordChannel;
use App\Core\Raids\RaidHelpers;
use Illuminate\Support\Facades\Log;
use App\Core\Discord\MessageTranslator;

class RaidEmbed
{

    public function __construct(Raid $raid)
    {
        $this->raid = $raid;
    }

    public static function forRaid(Raid $raid)
    {
      return new RaidEmbed($raid);
    }

    public function forGuild(Guild $guild)
    {
        $this->guild = $guild;
        $this->translator = MessageTranslator::to($this->guild)
            ->addGym($this->raid->getGym())
            ->addRaid($this->raid)
            ->addUser($this->raid->getLastUserAction()->getUser());
        return $this;
    }

    public function setsettings($settings)
    {
        $this->settings = $settings;
        return $this;
    }

    public function get()
    {

        $this->raidGroup = RaidGroup::where('guild_id', $this->guild->id)->where('raid_id', $this->raid->id)->first();
        $this->startTime = new \DateTime($this->raid->start_time);
        $this->endTime = new \DateTime($this->raid->end_time);

        $description = $this->getDescription();
        $embed = array(
            'title' => $this->getTitle(),
            'description' => (!empty($description)) ? implode("\r\n\r\n", $description) : '',
            'color' => self::getEggColor($this->raid->egg_level),
            'thumbnail' => array(
                'url' => $this->getThumbnailUrl(),
            ),
            'author' => array(
                'name' => $this->getGymName(),
                'url' => $this->raid->getGym()->google_maps_url,
                'icon_url' => $this->getGymUrl(),
            ),
        );
        return $embed;
    }

    private function getTitle()
    {

        if( $this->raid->pokemon ) {
            $title = html_entity_decode('Raid ' . $this->raid->pokemon->name_fr . ' jusqu\'à ' . $this->endTime->format('H\hi'));
        } elseif( $this->raid->egg_level == 7 ) {
            $title = 'Méga-raid à ' . $this->startTime->format('H\hi');;
        } elseif( $this->raid->egg_level == 6 ) {
            $title = 'Raid EX le ' . $this->startTime->format('d/m') . ' à ' . $this->startTime->format('H\hi');
        } else {
            $title = 'Raid ' . $this->raid->egg_level . ' têtes à ' . $this->startTime->format('H\hi');
        }

        return $title;
    }

    private function getDescription()
    {
        $description = [];
        $description[] = "Pop : de " . $this->startTime->format('H\hi') . " à " . $this->endTime->format('H\hi');

        if (!empty($this->settings)) {
            if (in_array('cp', $this->settings) && $this->raid->pokemon) {
                $pokemon = RaidHelpers::getPokemonForCp($this->raid);
                $description[] = "Normal : CP entre " . $pokemon->cp['lvl20']['min'] . " et " . $pokemon->cp['lvl20']['max'] . "\r\n" .
                    "Boost Météo : CP entre " . $pokemon->cp['lvl25']['min'] . " et " . $pokemon->cp['lvl25']['max'];
            }
            if (in_array('arene_desc', $this->settings) && !empty($this->raid->getGym()->description)) {
                $description[] = $this->translator->translate($this->raid->getGym()->description, $this->raid, $this->guild);
            }
            if (in_array('participants_nb', $this->settings) && $this->raidGroup ) {
                $total_present = $this->raidGroup->getNbParticipants('present');
                $total_remote = $this->raidGroup->getNbParticipants('remote');
                $total_invit = $this->raidGroup->getNbParticipants('invit');
                $total = $total_present + $total_remote + $total_invit;
                $description[] = "{$total} Participants ({$total_present} sur place, {$total_remote} à distance, {$total_invit} sur invitation)";
            }
            if (in_array('participants_list', $this->settings) && $this->raidGroup && $this->raidGroup->getNbParticipants() > 0 ) {
                $list = $this->raidGroup->getListeParticipants();
                $description[] = $this->raidGroup->getNbParticipants()." participants :\r\n{$list}";
            }
        }
        return $description;
    }

    private function getGymName()
    {
        $gymName = html_entity_decode($this->raid->getGym()->name);
        if ($this->raid->getGym()->zone_id) {
            $gymName = $this->raid->getGym()->zone->name . ' - ' . $gymName;
        }
        return $gymName;
    }

    private function getThumbnailUrl()
    {
        return $this->raid->pokemon ? $this->raid->pokemon->thumbnail_url : asset('storage/img/static/raid/egg_' . $this->raid->egg_level . '.png');
    }

    private function getGymUrl()
    {
        return $this->raid->getGym()->ex ? asset('storage/img/static/connector_gym_ex.png') : asset('storage/img/static/connector_gym.png');
    }

    public static function getEggColor($eggLevel)
    {
        $colors = array(
            1 => 'de6591',
            2 => 'de6591',
            3 => 'efad02',
            4 => 'efad02',
            5 => '222',
            6 => '222',
            7 => 'efad02',
        );

        if (array_key_exists($eggLevel, $colors)) {
            return hexdec($colors[$eggLevel]);
        }
        return false;
    }

}
