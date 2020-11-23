<?php

namespace App\Models;

use App\User;
use App\Core\Discord\Discord;
use App\Models\Guild;
use RestCord\DiscordClient;
use Illuminate\Support\Facades\Log;
use App\Core\Discord\MessageTranslator;
use Illuminate\Database\Eloquent\Model;

class UserAction extends Model
{

    protected $table = 'user_actions';
    protected $fillable = [
        'relation_id',
        'source',
        'type',
        'url',
        'content',
        'date',
        'message_discord_id',
        'channel_discord_id',
        'user_id',
        'guild_id',
        'city_id'
    ];

    var $messages = [
        [
            'message' => '@{utilisateur} Tu me ping, je te ping :wink:',
            'categories' => ['default'],
            'positions' => ['start', 'middle'],
        ],
        [
            'message' => 'https://media.giphy.com/media/yZ2FSn86bf2co/giphy.gif',
            'categories' => ['default'],
            'positions' => ['start', 'middle'],
        ],
        [
            'message' => 'https://media.giphy.com/media/YVPwi7L2izTJS/giphy.gif',
            'categories' => ['default'],
            'positions' => ['start', 'middle'],
        ],
        [
            'message' => 'bip bip, je suis un robot. Désolé du coup je n\'ai pas compris',
            'categories' => ['default'],
            'positions' => ['start', 'middle'],
        ],
        [
            'message' => 'Ca me fait plaisir d\'avoir une message de toi @{utilisateur}, merci :blush:. Par contre tu sais, je suis seulement un robot',
            'categories' => ['default'],
            'positions' => ['start'],
        ],
        [
            'message' => '01101010 01011100 00100111 01100001 01101001 00100000 01110010 01101001 01100101 01101110 00100000 01100011 01101111 01101101 01110000 01110010 01101001 01110011',
            'suite' => ['Je te laisse traduire maintenant :wink:'],
            'categories' => ['default'],
            'positions' => ['middle'],
        ],
        [
            'message' => 'Plait-il ?',
            'categories' => ['length0'],
            'positions' => ['start', 'middle'],
        ],
        [
            'message' => 'https://media.giphy.com/media/CiYImHHBivpAs/giphy.gif',
            'categories' => ['length0'],
            'positions' => ['start', 'middle'],
        ],
        [
            'message' => '@{utilisateur} Coucou :wave:',
            'categories' => ['hello'],
            'positions' => ['start', 'middle'],
        ],
        [
            'message' => 'Hello @{utilisateur}, la forme ?',
            'categories' => ['hello'],
            'positions' => ['start', 'middle'],
        ],
        [
            'message' => '@{utilisateur} :sweet_smile: :wave:',
            'categories' => ['hello'],
            'positions' => ['start', 'middle'],
        ],
        [
            'message' => 'Ohla, tu sais mes compétences sont limitées. Je voudrais pas te répondre une bêtise',
            'categories' => ['question'],
            'positions' => ['start', 'middle'],
        ],
        [
            'message' => 'Aucune idée, mais je veux bien demander à mon créateur',
            'suite' => ['Il comprend plus de choses que moi', '(heureusement :wink:)'],
            'categories' => ['question'],
            'positions' => ['start', 'middle'],
        ],
        [
            'message' => ':thinking:...',
            'suite' => ['(fais semblant de réfléchir)', 'En fait j\'en ai aucune idée, je suis juste un robot', 'désolé'],
            'categories' => ['question'],
            'positions' => ['start', 'middle'],
        ],
        [
            'message' => 'https://media.giphy.com/media/YVPwi7L2izTJS/giphy.gif',
            'categories' => ['question'],
            'positions' => ['start', 'middle'],
        ],
    ];

    public function getUser()
    {
        return User::find($this->user_id);
    }

    public function getGuild()
    {
        if (empty($this->guild_id)) return false;
        return Guild::find($this->guild_id);
    }

    public function reply()
    {
        $before = new \DateTime();
        $before->modify('- 2 minutes');
        $last_messages = $this->getMessagesFrom($before);
        $type = $this->getMessageType();

        if (count($last_messages) === 0) {
            $position = 'start';
        } else {
            $position = 'middle';
        }

        if (count($last_messages) < 5) {

            $message = $this->get_random_message($type, $position);
            if (!$message) return;
            $translator = MessageTranslator::to($this->getGuild())
                ->addUser($this->getUser());

            $discord = new DiscordClient(['token' => config('discord.token')]);
            $discord->channel->createMessage(array(
                'channel.id' => intval($this->channel_discord_id),
                'content' => $translator->translate($message['message']),
            ));

            if (array_key_exists('suite', $message) && !empty($message['suite'])) {
                foreach ($message['suite'] as $content) {
                    usleep(strlen($content) * 100000);
                    $discord->channel->createMessage(array(
                        'channel.id' => intval($this->channel_discord_id),
                        'content' => $translator->translate($content),
                    ));
                }
            }
        }

        return true;
    }

    public function getMessagesFrom($dateTime)
    {
        $messages = UserAction::where('user_id', $this->user_id)
            ->where('guild_id', $this->guild_id)
            ->where('created_at', '>', $dateTime->format('Y-m-d H:i:s'))
            ->get();
        return $messages;
    }

    public function get_random_message($categories = false, $positions = false)
    {

        if (!is_array($categories) && !empty($categories)) $categories = [$categories];
        if (!is_array($positions) && !empty($positions)) $positions = [$positions];

        $matching_messages = [];
        foreach ($this->messages as $value) {
            if (empty($categories)) {
                $in_categories = true;
            } else {
                $matching_categories = array_intersect($value['categories'], $categories);
                $in_categories = !empty($matching_categories);
            }
            if (empty($positions)) {
                $in_positions = true;
            } else {
                $matching_positions = array_intersect($value['positions'], $positions);
                $in_positions = !empty($matching_positions);
            }
            if ($in_categories && $in_positions) $matching_messages[] = $value;
        }

        if (!empty($matching_messages)) {
            $random_key = array_rand($matching_messages);
            return $matching_messages[$random_key];
        }

        return false;
    }

    public function getMessageType()
    {
        $types = [];
        $message = str_replace('<@!' . config('discord.id') . '>', '', $this->content);

        preg_match('/(hello|coucou|bjr|bonjour|:wave:)/i', $message, $hello);
        if (!empty($hello)) {
            $types[] = 'hello';
        }

        if (strlen($message) <= 2) {
            $types[] = 'length0';
        }

        preg_match('/\?/i', $message, $question);
        if (!empty($question)) {
            $types = ['question'];
        }

        if (empty($types)) return ['default'];
        return $types;
    }
}