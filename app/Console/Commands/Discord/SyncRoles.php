<?php

namespace App\Console\Commands\Discord;

use App\Models\Role;
use App\Models\Guild;
use RestCord\DiscordClient;
use Illuminate\Console\Command;

class SyncRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discord:syncroles';

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
        $guild_names = Guild::pluck('name')->toArray();
        $guild_name = $this->choice('Pour quelle guild ?', $guild_names);

        $guild = Guild::where('name', $guild_name)->first();
        if( empty( $guild ) ) {
            $this->info('Synchronisation avortée. la guild n\'a pas été trovuée.');
            return false;
        }

        $discord = new DiscordClient([
            'token' => config('discord.token'),
        ]);

        $to_delete = [];
        $to_add = [];
        $pRoles_ids = [];
        $roles_ids = [];

        $pRoles = Role::where('guild_id', $guild->id)->get();
        foreach( $pRoles as $pRole ) {
            $pRoles_ids[] =  $pRole->discord_id;
        }
        $roles = $discord->guild->getGuildRoles([
            'guild.id' => intval($guild->discord_id)
        ]);
        foreach( $roles as $role ) {
            $roles_ids[] =  $role->id;
        }

        foreach( $pRoles as $pRole ) {
            if( !in_array( $pRole->discord_id, $roles_ids ) ) {
                $to_delete[] = $pRole;
                $this->line(" - {$pRole->name} à supprimer");
            }
        }

        foreach( $roles as $role ) {
            if( !in_array( $role->id, $pRoles_ids ) ) {
                $to_add[] = $role;
                $this->line(" - {$role->name} à ajouter");
            }
        }

        $this->info( count($roles_ids).' roles Discord / '.count($pRoles).' roles Pokématos' );
        $this->info( count($to_delete).' roles à supprimer et '.count($to_add).' roles à créer' );

        if( empty($to_add) && empty($to_delete) ) {
            $this->info('RAS. Synchronisation terminée.');
            return;
        }

        $confirm = $this->confirm("Effectuer la syncrhonisation ?");
        if( !$confirm ) {
            $this->error('Synchronisation annulée');
            return;
        }

        if( !empty( $to_delete ) ) {
            foreach( $to_delete as $pRole ) {
                Role::destroy($pRole->id);
            }
        }

        if( !empty( $to_add ) ) {
            foreach( $to_add as $role ) {
                $args = [
                    'guild_id' => $guild->id,
                    'discord_id' => $role->id,
                    'name' => $role->name,
                    'color_type' => 'specific',
                    'color' => '#'.dechex($role->color),
                    'permissions' => $role->permissions,
                    'mentionable' => $role->mentionable
                ];
                Role::add($args, true);
            }
        }

        $this->info('Synchronisation OK');
    }

    public function getRolesSyncStatus( $guild ) {

    }
}
