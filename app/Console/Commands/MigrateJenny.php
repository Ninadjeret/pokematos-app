<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Guild;
use RestCord\DiscordClient;
use App\Models\RoleCategory;
use Illuminate\Console\Command;

class MigrateJenny extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:jenny';

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
        $discord = new DiscordClient([
            'token' => config('discord.token'),
            'guzzleOptions' => [
                'http_errors' => false
            ]
        ]);

        $discord_id = $this->ask("ID du discord");

        $guild = Guild::where('discord_id', $discord_id)->first();
        if( empty($guild) ) {
            $this->info("La guild n'utilise pas Pokématos.\r\nFin de l'import");
            exit;
        }

        $channel_discord_id = $this->ask("ID du salon à récupérer");

        $this->info("Récupération des roles de la guild {$guild->name}");
        $roles = $discord->guild->getGuildRoles([
            'guild.id' => intval($discord_id)
        ]);

        $roleCategories = RoleCategory::where('guild_id', $guild->id)->get()->toArray();
        if( empty( $roleCategories ) ) {
            $this->info("Merci de créer au préalable au moins une catégories de roles.\r\nFin de l'import");
            exit;
        }

        $choices = [];
        foreach( $roleCategories as $roleCategory ) {
            $choices[$roleCategory['id']] = $roleCategory['name'];
        }
        $chosen_category = $this->choice('Quelle catégorie utiliser ?', $choices);
        $roleCategory = RoleCategory::where('name', $chosen_category)->first();

        $this->info("Mise à jour de la catégorie {$roleCategory->name} ({$roleCategory->id})");
        $roleCategory->update([
            'notifications' => 1,
            'channel_discord_id' => $channel_discord_id
        ]);

        $this->info("Récupération des messages du salon {$channel_discord_id}");
        $messages = $discord->channel->getChannelMessages(array(
            'channel.id' => intval($channel_discord_id),
            'limit' => 100,
        ));

        $nb_messages = count($messages);
        $this->info("{$nb_messages} messages récupérés. Début du traitement");

        $nb_importes = 0;
        foreach( $messages as $message ) {
            sleep(1);
            $line = $message['content'];
            $this->line("Gestion du message {$message['id']}");
            if( preg_match( '/^<@&[0-9]{12,22}>$/i', $line ) ) {
                $role_discord_id = substr($line, 3, strlen($line) - 4 );
                $this->line(" - ID du role : {$role_discord_id}");
                $role = Role::where('discord_id', $role_discord_id)->first();
                if( $role ) {
                    $this->line(" - Role trouvé : {$role->name} ({$role->id}). Passage au role suivant");
                } else {
                    $role = $this->findRole($roles, $role_discord_id);
                    if( $role ) {
                        $this->line(" - Import du role {$role->name}");
                        $new_role = Role::create([
                            'discord_id' => $role->id,
                            'guild_id' => $guild->id,
                            'name' => $role->name,
                            'permissions' => 0,
                            'mentionable' => true,
                            'color_type' => 'category',
                            'color' => $roleCategory->color,
                            'category_id' => $roleCategory->id,
                            'channel_discord_id' => $channel_discord_id,
                            'message_discord_id' => $message['id'],
                        ]);
                        $this->line(" - Ajout de la réaction");
                        $discord->channel->createReaction([
                            'channel.id' => (int) $channel_discord_id,
                            'message.id' => (int) $message['id'],
                            'emoji' => '✅'
                        ]);
                        $discord_role = $discord->guild->modifyGuildRole([
                            'guild.id' => (int) $guild->discord_id,
                            'role.id' => (int) $role->id,
                            'name' => $role->name,
                            'color' => hexdec($roleCategory->color),
                            'permissions' => (int) 0,
                            'mentionable' => true
                        ]);
                        $this->line(" - Nouveau role créé {$new_role->name} ({$new_role->id})");
                    } else {
                        $this->line(" - Le role ne semble plus exister. Passage au role suivant");
                    }
                }

            } else {
                $this->line(" - Ce message n'est pas un message Jenny. Passage au suivant");
            }
        }

        $this->info('Migration terminée');
    }

    private function findRole($roles, $role_discord_id) {
        foreach( $roles as $role ) {
            if( $role->id == $role_discord_id ) {
                return $role;
            }
        }
        return false;
    }
}
