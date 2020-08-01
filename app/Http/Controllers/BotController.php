<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\City;
use App\Models\Role;
use App\Models\Raid;
use App\Models\Guild;
use GuzzleHttp\Client;
use App\Models\UserAction;
use RestCord\DiscordClient;
use App\Models\RoleCategory;
use Illuminate\Http\Request;
use App\RaidAnalyzer\TextAnalyzer;
use App\RaidAnalyzer\ImageAnalyzer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{

    /**
     * ==================================================================
     * GESTION DES GUILDS
     * ==================================================================
     */

    public function getGuilds(Request $request)
    {
        $return = [];
        $guilds = Guild::where('active', 1)->get();
        return response()->json($guilds, 200);
    }

    public function addGuild(Request $request)
    {

        if (!isset($request->guild_id) || empty($request->guild_id) || !isset($request->name) || empty($request->name)) {
            return response()->json(__('system.missing_args'), 400);
        }

        $token = $request->guild_token;
        $guild = Guild::where('token', $token)
            ->where('active', 0)
            ->first();

        if ($guild) {
            $guild->update([
                'name' => $request->name,
                'discord_id' => $request->guild_id,
                'active' => 1,
            ]);

            $roles_to_add = $guild->getDiscordRoles();
            if (!empty($roles_to_add)) {
                foreach ($roles_to_add as $role_to_add) {
                    Role::create([
                        'discord_id' => $role_to_add->id,
                        'guild_id' => $guild->id,
                        'name' => $role_to_add->name,
                        'color' => '#' . dechex($role_to_add->color),
                    ]);
                }
            }

            //On avertit le bot de la MAJ
            \App\Core\Discord::SyncBot();

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
    public function getRoles(Request $request, $guild_id)
    {
        $guild = Guild::where('discord_id', $guild_id)
            ->where('active', 1)
            ->first();

        if (!$guild) {
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
    public function getRole(Request $request, $guild_id, $role)
    {

        $guild = Guild::where('discord_id', $guild_id)
            ->where('active', 1)
            ->first();

        if (!$guild) {
            return response()->json('La guild n\'a pas été trouvée', 400);
        }

        $role = Role::where('discord_id', $role)->first();
        if (empty($role)) return response()->json('Le role n\'a pas été trouvé', 400);

        $return = $role->toArray();
        unset($return['guild']['settings']);
        if ($return['category']) {
            unset($return['category']['guild']);
        }

        return response()->json($return, 200);
    }


    /**
     * Création d'un role
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function createRole(Request $request, $guild_id)
    {

        $guild = Guild::where('discord_id', $guild_id)
            ->where('active', 1)
            ->first();

        if (!$guild) {
            return response()->json('La guild n\'a pas été trouvée', 400);
        }

        $role = Role::where('discord_id', $request->discord_id)->first();
        if (empty($role)) {
            //$fromDiscord = ($request->discord_event) ? true : false;
            $fromDiscord = true;

            $args = [
                'guild_id' => $guild->id,
                'discord_id' => $request->discord_id,
                'name' => $request->name,
                'color_type' => 'specific',
                'color' => '#' . dechex($request->color),
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
    public function deleteRole(Request $request, $guild_id, $role)
    {

        $guild = Guild::where('discord_id', $guild_id)
            ->where('active', 1)
            ->first();

        if (!$guild) {
            return response()->json('La guild n\'a pas été trouvée', 400);
        }

        $role = Role::where('discord_id', $role)->first();
        if (empty($role)) return response()->json('Le role n\'a pas été trouvé', 400);

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
    public function updateRole(Request $request, $guild_id, $role)
    {

        $guild = Guild::where('discord_id', $guild_id)
            ->where('active', 1)
            ->first();

        if (!$guild) {
            return response()->json('La guild n\'a pas été trouvée', 400);
        }

        $app_role = Role::where('discord_id', $role)->first();
        if (empty($app_role)) return response()->json('Le role n\'a pas été trouvé', 400);

        //$fromDiscord = ($request->discord_event) ? true : false;
        $fromDiscord = true;

        $args = [
            'name' => $request->name,
            'color' => '#' . dechex($request->color),
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
    public function getRoleCategories(Request $request, $guild_id)
    {

        $guild = Guild::where('discord_id', $guild_id)
            ->where('active', 1)
            ->first();

        if (!$guild) {
            return response()->json('La guild n\'a pas été trouvée', 400);
        }
        $roleCategories = RoleCategory::where('guild_id', $guild->id)->get();
        if (!empty($roleCategories)) {
            foreach ($roleCategories as &$roleCategorie) {
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
    public function createRoleCategory(Request $request)
    {

        $guild = Guild::where('discord_id', $request->guild_id)->first();
        if (empty($guild)) return response()->json('L\'ID de la guild n\'existe pas', 400);

        $roleCategory = RoleCategory::create([
            'guild_id' => $guild->id,
            'name' => $request->name,
            'channel_discord_id' => $request->channel_id,
            'restricted' => ($request->restricted) ? $request->restricted : 0,
        ]);
        return response()->json($roleCategory, 200);
    }

    public function updateRoleCategory(Request $request, RoleCategory $categorie)
    {

        $guild = Guild::where('discord_id', $request->guild_id)->first();
        if (empty($guild)) return response()->json('L\'ID de la guild n\'existe pas', 400);

        $categorie->update([
            'name' => ($request->name) ? $request->name : $categorie->name,
            'channel_discord_id' => ($request->channel_id) ? $request->channel_id : $categorie->channel_discord_id,
            'restricted' => ($request->restricted) ? $request->restricted : $categorie->restricted,
        ]);
        return response()->json($categorie, 200);
    }


    /**
     * [getRoleCategory description]
     * @param  Request      $request   [description]
     * @param  RoleCategory $categorie [description]
     * @return [type]                  [description]
     */
    public function getRoleCategory(Request $request, $guild_id, RoleCategory $categorie)
    {

        $guild = Guild::where('discord_id', $guild_id)
            ->where('active', 1)
            ->first();

        if (!$guild) {
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
    public function deleteRoleCategory(Request $request, $guild_id, RoleCategory $categorie)
    {
        RoleCategory::destroy($categorie->id);
        return response()->json(null, 204);
    }
}