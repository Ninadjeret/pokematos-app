<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\City;
use App\Models\Role;
use App\Models\Raid;
use App\Models\Guild;
use GuzzleHttp\Client;
use RestCord\DiscordClient;
use App\Models\RoleCategory;
use Illuminate\Http\Request;
use App\RaidAnalyzer\TextAnalyzer;
use App\RaidAnalyzer\ImageAnalyzer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class BotController extends Controller {

    /**
    * ==================================================================
    * GESTION DES GUILDS
    * ==================================================================
    */

   public function getGuilds( Request $request ) {
       $return = [];
       $guilds = Guild::where('active', 1)->get();
       return response()->json($guilds, 200);
   }

    public function addGuild( Request $request ) {

        if( !isset($request->guild_id) || empty($request->guild_id) || !isset($request->name) || empty($request->name) ) {
            return response()->json('Paramètres manquants', 400);
        }

        $token = $request->guild_token;
        $guild = Guild::where('token', $token)
            ->where('active', 0)
            ->first();

        if( $guild ) {
            $guild->update([
                'name' => $request->name,
                'discord_id' => $request->guild_id,
                'active' => 1,
            ]);

            $roles_to_add = $guild->getDiscordRoles();
            Log::debug( print_r($roles_to_add, true) );
            if( !empty( $roles_to_add ) ) {
                foreach( $roles_to_add as $role_to_add ) {
                    Role::create([
                        'discord_id' => $role_to_add->id,
                        'guild_id' => $guild->id,
                        'name' => $role_to_add->name,
                        'color' => '#'.dechex($role_to_add->color),
                    ]);
                }
            }

            //On avertit le bot de la MAJ
            $client = new Client();
            $url = config('app.bot_sync_url');
            if( !empty($url) ) {
                $res = $client->get($url);
            }

            return response()->json($guild, 200);

        }

        return response()->json('Aucune guild en attente de création avec ce token', 400);
    }

    /**
    * ==================================================================
    * GESTION DES ROLES
    * ==================================================================
    */

    /**
     * [getRoles description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getRoles( Request $request, $guild_id ) {
        $guild = Guild::where('discord_id', $guild_id)
            ->where('active', 1)
            ->first();

        if( !$guild ) {
            return response()->json('La guild n\'a pas été trouvée', 400);
        }

        $roles = Role::where('guild_id', $guild->id)->get();
        return response()->json($roles, 200);
    }


    /**
     * [getRole description]
     * @param  Request $request [description]
     * @param  [type]  $role    [description]
     * @return [type]           [description]
     */
    public function getRole( Request $request, $guild_id, $role ) {

        $guild = Guild::where('discord_id', $guild_id)
            ->where('active', 1)
            ->first();

        if( !$guild ) {
            return response()->json('La guild n\'a pas été trouvée', 400);
        }

        $role = Role::where('discord_id', $role)->first();
        if( empty($role) ) return response()->json('Le role n\'a pas été trouvé', 400);

        $return = $role->toArray();
        unset($return['guild']['settings']);
        if( $return['category'] ) {
            unset($return['category']['guild']);
        }

        return response()->json($return, 200);
    }


    /**
     * Création d'un role
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function createRole( Request $request, $guild_id ) {

        $guild = Guild::where('discord_id', $guild_id)
            ->where('active', 1)
            ->first();

        if( !$guild ) {
            return response()->json('La guild n\'a pas été trouvée', 400);
        }

        $role = Role::where('discord_id', $request->discord_id)->first();
        if( empty($role) ) {
            //$fromDiscord = ($request->discord_event) ? true : false;
            $fromDiscord = true;

            $args = [
                'guild_id' => $guild->id,
                'discord_id' => $request->discord_id,
                'name' => $request->name,
                'color_type' => 'specific',
                'color' => '#'.dechex($request->color),
                'permissions' => $request->permissions,
                'mentionable' => $request->mentionable
            ];
            $role = Role::add($args, $fromDiscord);
        };
        return response()->json($role, 200);
    }


    /**
     * [deleteRole description]
     * @param  Request $request [description]
     * @param  [type]  $role    [description]
     * @return [type]           [description]
     */
    public function deleteRole( Request $request, $guild_id, $role ) {

        $guild = Guild::where('discord_id', $guild_id)
            ->where('active', 1)
            ->first();

        if( !$guild ) {
            return response()->json('La guild n\'a pas été trouvée', 400);
        }

        $role = Role::where('discord_id', $role)->first();
        if( empty($role) ) return response()->json('Le role n\'a pas été trouvé', 400);

        //$fromDiscord = ($request->discord_event) ? true : false;
        $fromDiscord = true;

        $role->suppr($fromDiscord);

        return response()->json(null, 204);
    }


    /**
     * [updateRole description]
     * @param  Request $request [description]
     * @param  [type]  $role    [description]
     * @return [type]           [description]
     */
    public function updateRole( Request $request, $guild_id, $role ) {

        $guild = Guild::where('discord_id', $guild_id)
            ->where('active', 1)
            ->first();

        if( !$guild ) {
            return response()->json('La guild n\'a pas été trouvée', 400);
        }

        $app_role = Role::where('discord_id', $role)->first();
        if( empty($app_role) ) return response()->json('Le role n\'a pas été trouvé', 400);

        //$fromDiscord = ($request->discord_event) ? true : false;
        $fromDiscord = true;

        $args = [
            'name' => $request->name,
            'color' => '#'.dechex($request->color),
            'permissions' => $request->permissions,
            'mentionable' => $request->mentionable
        ];

        $app_role->change($args, $fromDiscord);

        return response()->json($app_role, 200);
    }

    /**
    * ==================================================================
    * GESTION DES CATEGORIES DE ROLES
    * ==================================================================
    */

    /**
     * [getRoleCategories description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getRoleCategories( Request $request, $guild_id) {

        $guild = Guild::where('discord_id', $guild_id)
            ->where('active', 1)
            ->first();

        if( !$guild ) {
            return response()->json('La guild n\'a pas été trouvée', 400);
        }
        $roleCategories = RoleCategory::where('guild_id', $guild->id)->get();
        if( !empty($roleCategories) ) {
            foreach( $roleCategories as &$roleCategorie ) {
                $roleCategorie->addHidden('guild');
            }
        }
        return response()->json($roleCategories, 200);
    }


    /**
     * [createRoleCategory description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function createRoleCategory( Request $request ) {

        $guild = Guild::where('discord_id', $request->guild_id)->first();
        if( empty($guild) ) return response()->json('L\'ID de la guild n\'existe pas', 400);

        $roleCategory = RoleCategory::create([
            'guild_id' => $guild->id,
            'name' => $request->name,
            'channel_discord_id' => $request->channel_id,
            'restricted' => ($request->restricted) ? $request->restricted : 0,
        ]);
        return response()->json($roleCategory, 200);
    }

    public function updateRoleCategory( Request $request, RoleCategory $categorie ) {

        $guild = Guild::where('discord_id', $request->guild_id)->first();
        if( empty($guild) ) return response()->json('L\'ID de la guild n\'existe pas', 400);

        $roleCategory ->update([
            'name' => ($request->name) ? $request->name : $roleCategory->name,
            'channel_discord_id' => ($request->channel_id) ? $request->channel_id : $roleCategory->channel_discord_id,
            'restricted' => ($request->restricted) ? $request->restricted : $roleCategory->restricted,
        ]);
        return response()->json($roleCategory, 200);
    }


    /**
     * [getRoleCategory description]
     * @param  Request      $request   [description]
     * @param  RoleCategory $categorie [description]
     * @return [type]                  [description]
     */
    public function getRoleCategory( Request $request, $guild_id, RoleCategory $categorie ) {

        $guild = Guild::where('discord_id', $guild_id)
            ->where('active', 1)
            ->first();

        if( !$guild ) {
            return response()->json('La guild n\'a pas été trouvée', 400);
        }

        return response()->json($categorie, 200);
    }


    /**
     * [deleteRoleCategory description]
     * @param  Request      $request   [description]
     * @param  RoleCategory $categorie [description]
     * @return [type]                  [description]
     */
    public function deleteRoleCategory( Request $request, $guild_id, RoleCategory $categorie ) {
        RoleCategoy::destroy($categorie->id);
        return response()->json(null, 204);
    }

    /**
     * [decodeImage description]
     * @param  Request $request [description]
     * @param  City    $city    [description]
     * @return [type]           [description]
     */
    public function addRaid( Request $request ) {

        $url = ( isset($request->url) && !empty($request->url) ) ? $request->url : false ;
        $text = ( isset($request->text) && !empty($request->text) ) ? $request->text : false ;
        $username = $request->user_name;
        $userDiscordId = $request->user_discord_id;
        $guild_discord_id = $request->guild_discord_id;
        $message_discord_id = $request->message_discord_id;
        $channel_discord_id = $request->channel_discord_id;

        if( empty( $guild_discord_id ) ) {
            return response()->json('L\'ID de Guild est obligatoire', 400);
        }

        $guild = Guild::where( 'discord_id', $guild_discord_id )->first();
        $city = City::find( $guild->city->id );

        $user = User::where('discord_id', $userDiscordId)->first();
        if( !$user ) {
            $user = User::create([
                'name' => $username,
                'password' => Hash::make( str_random(20) ),
                'discord_name' => $username,
                'discord_id' => $userDiscordId,
            ]);
        }

        if( $url ) {
            $imageAnalyzer = new ImageAnalyzer($url, $guild);
            $result = $imageAnalyzer->result;
            $source_type = 'image';
        } else {
            $textAnalyzer = new TextAnalyzer($text, $guild);
            $result = $textAnalyzer->result;
            $source_type = 'text';
        }

        if( empty( $result->error ) ) {
            $args = [];
            $args['city_id'] = $city->id;
            $args['user_id'] = $user->id;
            $args['gym_id'] = $result->gym->id;
            $args['message_discord_id'] = $message_discord_id;
            $args['channel_discord_id'] = $channel_discord_id;
            $args['guild_id'] = $guild->id;
            $args['source_type'] = $source_type;
            if( isset( $result->pokemon->id ) ) $args['pokemon_id'] = $result->pokemon->id;
            if( isset( $result->eggLevel ) ) {
                $args['egg_level'] = $result->eggLevel;
                if( $result->eggLevel == '6' ) {
                    $args['ex'] = true;
                }
            }
            if( isset( $result->date ) ) $args['start_time'] = $result->date;

            $raid = Raid::add($args);
            return response()->json($raid, 200);
        }

        return response()->json($result, 400);


    }

}
