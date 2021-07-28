<?php

namespace App\Core\Discord;

use App\Models\Role;
use GuzzleHttp\Client;
use RestCord\DiscordClient;
use Illuminate\Support\Facades\Log;

class Discord
{
    public static function sendMessage($args)
    {
        try {
            $discord = new DiscordClient(['token' => config('discord.token')]);
            $message = $discord->channel->createMessage($args);
            return $message;
        } catch (\GuzzleHttp\Command\Exception\CommandException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $message = $e->getMessage();
            Log::channel('discord')->info("--------------------------------------------------");
            Log::channel('discord')->info("{$statusCode} - channel->createMessage --- {$message}");
            Log::channel('discord')->info(print_r($args, true));
            return false;
        }
    }

    public static function deleteMessage($args)
    {
        try {
            $discord = new DiscordClient(['token' => config('discord.token')]);
            $discord->channel->deleteMessage($args);
            return true;
        } catch (\GuzzleHttp\Command\Exception\CommandException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $message = $e->getMessage();
            Log::channel('discord')->info("--------------------------------------------------");
            Log::channel('discord')->info("{$statusCode} - channel->deleteMessage --- {$message}");
            Log::channel('discord')->info(print_r($args, true));
            return false;
        }
    }

    public static function bulkDeleteMessages($args)
    {
        $client = new Client();
        $res = $client->post("https://discord.com/api/v6/channels/{$args['channel_id']}/messages/bulk-delete?messages=" . json_encode($args['messages_ids']), [
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bot ' . config('discord.token'),
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode(['messages' => $args['messages_ids']]),
        ]);
        if ($res->getStatusCode() >= 300) {
            return false;
        }
        return true;
    }

    public static function deleteChannel($args)
    {
        try {
            $discord = new DiscordClient(['token' => config('discord.token')]);
            $channel = $discord->channel->deleteOrcloseChannel($args);
            return true;
        } catch (\GuzzleHttp\Command\Exception\CommandException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $message = $e->getMessage();
            Log::channel('discord')->info("--------------------------------------------------");
            Log::channel('discord')->info("{$statusCode} - channel->deleteOrcloseChannel --- {$message}");
            Log::channel('discord')->info(print_r($args, true));
            return false;
        }
    }

    public static function createChannel($args)
    {
        try {
            $discord = new DiscordClient(['token' => config('discord.token')]);
            $channel = $discord->guild->createGuildChannel($args);
            return $channel;
        } catch (\GuzzleHttp\Command\Exception\CommandException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $message = $e->getMessage();
            Log::channel('discord')->info("--------------------------------------------------");
            Log::channel('discord')->info("{$statusCode} - guild->createGuildChannel --- {$message}");
            Log::channel('discord')->info(print_r($args, true));
            return false;
        }
    }

    public static function modifyChannel($args)
    {
        try {
            usleep(500000);
            $discord = new DiscordClient(['token' => config('discord.token')]);
            $channel = $discord->channel->modifyChannel($args);
            return $channel;
        } catch (\GuzzleHttp\Command\Exception\CommandException $e) {
            $statusCode = 'ee';
            $message = $e->getMessage();
            Log::debug($message);
            Log::channel('discord')->info("--------------------------------------------------");
            Log::channel('discord')->info("{$statusCode} - channel->modifyChannel --- {$message}");
            Log::channel('discord')->info(print_r($args, true));
            return false;
        }
    }

    public static function SyncBot()
    {
        $client = new Client();
        $url = config('app.bot_sync_url');
        if (!empty($url)) {
            $res = $client->get($url);
        }
    }

    public static function getGuildRoles($args, $role_name = false)
    {
        try {
            $discord = new DiscordClient(['token' => config('discord.token')]);
            $roles = $discord->guild->getGuildRoles($args);

            if (!$role_name) return $roles;

            foreach ($roles as $role) {
                if ($role->name == $role_name) return $role;
            }
            return false;
        } catch (\GuzzleHttp\Command\Exception\CommandException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $message = $e->getMessage();
            Log::channel('discord')->info("--------------------------------------------------");
            Log::channel('discord')->info("{$statusCode} - guild->getGuildRoles --- {$message}");
            Log::channel('discord')->info(print_r($args, true));
            return false;
        }
    }
}
