<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\City;
use App\Models\Role;
use App\Models\Guild;
use App\Models\Connector;
use App\Models\RoleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller {

    public function getUSer() {
        $user = Auth::user();
        return response()->json($user, 200);
    }

    public function getCities() {
        $user = Auth::user();
        return response()->json($user->getCities(), 200);
    }

    public static function getGuildOptions( Request $request, City $city, Guild $guild ) {
        return response()->json($guild->settings, 200);
    }

    public static function updateGuildOptions( Request $request, City $city, Guild $guild ) {
        $settings = $request->settings;
        $guild->updateSettings($settings);
        return response()->json($guild, 200);
    }

    /**
    * ==================================================================
    * GESTION DES CONNECTEURS
    * ==================================================================
    */

   public function getConnectors( Request $request, Guild $guild ) {
       $connecteurs = Connector::where('guild_id', $guild->id)->get();
       return response()->json($connecteurs, 200);
   }

   public function createConnector( Request $request, Guild $guild ) {
       $connector = Connector::create([
           'name' => ( isset( $request->name ) ) ? $request->name : '' ,
           'guild_id' => $guild->id,
           'channel_discord_id' => ( isset( $request->channel_discord_id ) ) ? $request->channel_discord_id : '' ,
           'publish' => ( isset( $request->publish ) ) ? $request->publish : '' ,
           'filter_gym_type' => ( isset( $request->filter_gym_type ) ) ? $request->filter_gym_type : '' ,
           'filter_gym_zone' => ( isset( $request->filter_gym_zone ) ) ? $request->filter_gym_zone : '' ,
           'filter_gym_gym' => ( isset( $request->filter_gym_gym ) ) ? $request->filter_gym_gym : '' ,
           'filter_pokemon_type' => ( isset( $request->filter_pokemon_type ) ) ? $request->filter_pokemon_type : '' ,
           'filter_pokemon_level' => ( isset( $request->filter_pokemon_level ) ) ? $request->filter_pokemon_level : '' ,
           'filter_pokemon_pokemon' => ( isset( $request->filter_pokemon_pokemon ) ) ? $request->filter_pokemon_pokemon : '' ,
           'format' => ( isset( $request->format ) ) ? $request->format : 'auto' ,
           'custom_message_before' => ( isset( $request->custom_message_before ) ) ? $request->custom_message_before : '' ,
           'custom_message_after' => ( isset( $request->custom_message_after ) ) ? $request->custom_message_after : '' ,
       ]);
       return response()->json($connector, 200);
   }

   public function updateConnector( Request $request, Guild $guild, Connector $connector ) {
       $connector->update([
           'name' => ( isset( $request->name ) ) ? $request->name : $connector->name ,
           'channel_discord_id' => ( isset( $request->channel_discord_id ) ) ? $request->channel_discord_id : $connector->channel_discord_id ,
           'publish' => ( isset( $request->publish ) ) ? $request->publish : $connector->publish ,
           'filter_gym_type' => ( isset( $request->filter_gym_type ) ) ? $request->filter_gym_type : $connector->filter_gym_type ,
           'filter_gym_zone' => ( isset( $request->filter_gym_zone ) ) ? $request->filter_gym_zone : $connector->filter_gym_zone ,
           'filter_gym_gym' => ( isset( $request->filter_gym_gym ) ) ? $request->filter_gym_gym : $connector->filter_gym_gym ,
           'filter_pokemon_type' => ( isset( $request->filter_pokemon_type ) ) ? $request->filter_pokemon_type : $connector->filter_pokemon_type ,
           'filter_pokemon_level' => ( isset( $request->filter_pokemon_level ) ) ? $request->filter_pokemon_level : $connector->filter_pokemon_level ,
           'filter_pokemon_pokemon' => ( isset( $request->filter_pokemon_pokemon ) ) ? $request->filter_pokemon_pokemon : $connector->filter_pokemon_pokemon ,
           'format' => ( isset( $request->format ) ) ? $request->format : $connector->format ,
           'custom_message_before' => ( isset( $request->custom_message_before ) ) ? $request->custom_message_before : $connector->custom_message_before ,
           'custom_message_after' => ( isset( $request->custom_message_after ) ) ? $request->custom_message_after : $connector->custom_message_after ,
       ]);
       return response()->json($connector, 200);
   }

   public function getConnector( Request $request, Guild $guild, Connector $connector ) {
       return response()->json($connector, 200);
   }

   public function deleteConnector(Request $request, City $city, Guild $guild, Connector $connector ) {
       Connector::destroy($connector->id);
       return response()->json(null, 204);
   }

    /**
    * ==================================================================
    * GESTION DES ROLES
    * ==================================================================
    */

    public function getRoles(Request $request, City $city, Guild $guild ) {
        $roles = Role::where('guild_id', $guild->id)->get();
        return response()->json($roles, 200);
    }

    public function createRole(Request $request, City $city, Guild $guild ) {

        $roleCategory = RoleCategory::find($request->category_id);
        if( empty($roleCategory) ) return response()->json('La catégorie n\'existe pas', 400);

        $role = Role::add([
            'guild_id' => $guild->id,
            'category_id' => $roleCategory->id,
            'name' => $request->name,
            'type' => ( $request->type ) ? $request->type : null,
            'gym_id' => ( $request->gym_id ) ? $request->gym_id : null,
            'zone_id' => ( $request->zone_id ) ? $request->zone_id : null,
            'pokemon_id' => ( $request->pokemon_id ) ? $request->pokemon_id : null,
        ]);
        return response()->json($role, 200);
    }

    public function getRole(Request $request, City $city, Guild $guild, Role $role ) {
        return response()->json($role, 200);
    }

    public function updateRole(Request $request, City $city, Guild $guild, Role $role ) {
        $roleCategory = RoleCategory::find($request->category_id);
        if( empty($roleCategory) ) return response()->json('La catégorie n\'existe pas', 400);

        $role->change([
            'name' => ($request->name) ? $request->name : $role->name,
            'category_id' => $roleCategory->id,
            'type' => ( $request->type ) ? $request->type : $role->type,
            'gym_id' => ( $request->gym_id ) ? $request->gym_id : $role->gym_id,
            'zone_id' => ( $request->zone_id ) ? $request->zone_id : $role->zone_id,
            'pokemon_id' => ( $request->pokemon_id ) ? $request->pokemon_id : $role->pokemon_id,
        ]);
        return response()->json($role, 200);
    }

    public function deleteRole(Request $request, City $city, Guild $guild, Role $role ) {
        $role->suppr();
        return response()->json(null, 204);
    }

    /**
    * ==================================================================
    * GESTION DES CATEGORIES DE ROLES
    * ==================================================================
    */

    public function getRoleCategories(Request $request, City $city, Guild $guild ) {
        $categories = RoleCategory::where('guild_id', $guild->id)->get();
        return response()->json($categories, 200);
    }

    public function createRoleCategory(Request $request, City $city, Guild $guild ) {
        $categorie = RoleCategory::create([
            'guild_id' => $guild->id,
            'name' => $request->name,
            'channel_discord_id' => $request->channel_discord_id,
            'restricted' => ($request->restricted) ? $request->restricted : 0,
            'notifications' => ($request->notifications) ? $request->notifications : 0,
        ]);
        $categorie->savePermissions($request->permissions, $request->permissions_to_delete);
        return response()->json($categorie, 200);
    }

    public function getRoleCategory(Request $request, City $city, Guild $guild, RoleCategory $categorie ) {
        return response()->json($categorie, 200);
    }

    public function updateRoleCategory(Request $request, City $city, Guild $guild, RoleCategory $categorie ) {
        $categorie->update([
            'name' => ($request->name) ? $request->name : $categorie->name,
            'channel_discord_id' => ($request->channel_discord_id) ? $request->channel_discord_id : $categorie->channel_discord_id,
            'restricted' => ($request->restricted) ? $request->restricted : $categorie->restricted,
            'notifications' => ($request->notifications) ? $request->notifications : 0,
        ]);
        $categorie->savePermissions($request->permissions, $request->permissions_to_delete);
        return response()->json($categorie, 200);
    }

    public function deleteRoleCategory(Request $request, City $city, Guild $guild, RoleCategory $categorie ) {
        RoleCategory::destroy($categorie->id);
        return response()->json(null, 204);
    }

}
