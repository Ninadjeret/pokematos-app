<?php

namespace App\Http\Controllers;

use App\models\Role;
use App\models\Guild;
use RestCord\DiscordClient;
use Illuminate\Http\Request;

class BotController extends Controller {

    public function getRoles( Request $request ) {
        $guild = Guild::where('discord_id', $request->guild_id)->first();
        if( empty($guild) ) return response()->json('L\'ID de la guild n\'existe pas', 400);

        $roles = Role::where('guild_id', $guild->id)->get();
        return response()->json($roles, 200);
    }

    public function createRole( Request $request ) {

        $guild = Guild::where('discord_id', $request->guild_id)->first();
        if( empty($guild) ) return response()->json('L\'ID de la guild n\'existe pas', 400);

        $discord = new DiscordClient(['token' => config('discord.token')]);
        $discord_role = $discord->guild->createGuildRole([
            'guild.id' => (int) $request->guild_id,
            'name' => $request->name,
        ]);
        if( !$discord_role ) return response()->json('Le role n\'a pas pu être créé sur Discord', 400);

        $role = Role::create([
            'discord_id' => $discord_role->id,
            'guild_id' => $guild->id,
            'name' => $request->name,
            'type' => ( $request->type ) ? $request->type : null,
            'relation_id' => ( $request->relation_id ) ? $request->relation_id : null,
        ]);
        return response()->json($role, 200);
    }

    public function deleteRole( Request $request, $role ) {

        $role = Role::where('discord_id', $role)->first();
        if( empty($role) ) return response()->json('Le role n\'a pas été trouvé', 400);

        $discord = new DiscordClient(['token' => config('discord.token')]);
        $discord_role = $discord->guild->deleteGuildRole([
            'guild.id' => (int) $role->guild->discord_id,
            'role.id' => (int) $role->discord_id
        ]);

        Role::destroy($role->id);

        return response()->json(null, 204);
    }

    public function updateRole( Request $request, $role ) {

        $role = Role::where('discord_id', $role)->first();
        if( empty($role) ) return response()->json('Le role n\'a pas été trouvé', 400);

        $discord = new DiscordClient(['token' => config('discord.token')]);
        $discord_role = $discord->guild->modifyGuildRole([
            'guild.id' => (int) $role->guild->discord_id,
            'role.id' => (int) $role->discord_id,
            'name' => $request->name,
        ]);

        if( $request->name ) $role->update([ 'name' => $request->name ]);


        return response()->json($role, 200);
    }

}
