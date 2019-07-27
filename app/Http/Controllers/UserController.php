<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\City;
use App\Models\Role;
use App\Models\Stop;
use App\Models\Guild;
use App\Models\Announce;
use App\Models\Connector;
use App\Models\QuestReward;
use Illuminate\Http\Request;
use App\Models\RoleCategory;
use App\Models\QuestInstance;
use App\ImageAnalyzer\Engine;
use App\Models\QuestConnector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
    * GESTION DES QUETES
    * ==================================================================
    */

    public function createQuest( City $city, Request $request ) {
        $gym = Stop::find($request->params['gym_id']);
        $quest = new QuestInstance();
        $quest->city_id = $city->id;
        $quest->gym_id = $request->params['gym_id'];
        $quest->quest_id = $request->params['quest_id'];
        $quest->date = date('Y-m-d 00:00:00');
        $quest->save();

        $announce = Announce::create([
            'type' => 'quest-create',
            'source' => ( !empty($request->params['type']) ) ? $request->params['type'] : 'map',
            'date' => date('Y-m-d H:i:s'),
            'user_id' => Auth::id(),
            'quest_instance_id' => $quest->id,
        ]);

        event( new \App\Events\QuestInstanceCreated( $quest, $announce ) );

        return response()->json($quest, 200);
    }

    public function deleteQuest( City $city, QuestInstance $questInstance, Request $request ) {
        event( new \App\Events\QuestInstanceDeleted( $questInstance ) );;
        QuestInstance::destroy($questInstance->id);
        return response()->json(null, 204);
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
   * GESTION DES CONNECTEURS DE QUETES
   * ==================================================================
   */

  public function getQuestConnectors( Request $request, Guild $guild ) {
      $connecteurs = QuestConnector::where('guild_id', $guild->id)->get();
      return response()->json($connecteurs, 200);
  }

  public function createQuestConnector( Request $request, Guild $guild ) {
      $connector = QuestConnector::create([
          'name' => ( isset( $request->name ) ) ? $request->name : '' ,
          'guild_id' => $guild->id,
          'channel_discord_id' => ( isset( $request->channel_discord_id ) ) ? $request->channel_discord_id : '' ,
          'filter_reward_type' => ( isset( $request->filter_reward_type ) ) ? $request->filter_reward_type : '' ,
          'filter_reward_reward' => ( isset( $request->filter_reward_reward ) ) ? $request->filter_reward_reward : '' ,
          'filter_reward_pokemon' => ( isset( $request->filter_reward_pokemon ) ) ? $request->filter_reward_pokemon : '' ,
          'filter_stop_type' => ( isset( $request->filter_stop_type ) ) ? $request->filter_stop_type : '' ,
          'filter_stop_zone' => ( isset( $request->filter_stop_zone ) ) ? $request->filter_stop_zone : '' ,
          'filter_stop_stop' => ( isset( $request->filter_stop_stop ) ) ? $request->filter_stop_stop : '' ,
          'format' => ( isset( $request->format ) ) ? $request->format : 'auto' ,
          'custom_message' => ( isset( $request->custom_message ) ) ? $request->custom_message : '' ,
      ]);
      return response()->json($connector, 200);
  }

  public function updateQuestConnector( Request $request, Guild $guild, QuestConnector $connector ) {
      $connector->update([
          'name' => ( isset( $request->name ) ) ? $request->name : $connector->name ,
          'channel_discord_id' => ( isset( $request->channel_discord_id ) ) ? $request->channel_discord_id : $connector->channel_discord_id ,
          'filter_reward_type' => ( isset( $request->filter_reward_type ) ) ? $request->filter_reward_type : $connector->filter_reward_type ,
          'filter_reward_reward' => ( isset( $request->filter_reward_reward ) ) ? $request->filter_reward_reward : $connector->filter_reward_reward ,
          'filter_reward_pokemon' => ( isset( $request->filter_reward_pokemon ) ) ? $request->filter_reward_pokemon : $connector->filter_reward_pokemon ,
          'filter_stop_type' => ( isset( $request->filter_stop_type ) ) ? $request->filter_stop_type : $connector->filter_stop_type ,
          'filter_stop_zone' => ( isset( $request->filter_stop_zone ) ) ? $request->filter_stop_zone : $connector->filter_stop_zone ,
          'filter_stop_stop' => ( isset( $request->filter_stop_stop ) ) ? $request->filter_stop_stop : $connector->filter_stop_stop ,
          'format' => ( isset( $request->format ) ) ? $request->format : $connector->format ,
          'custom_message' => ( isset( $request->custom_message ) ) ? $request->custom_message : $connector->custom_message ,
      ]);
      return response()->json($connector, 200);
  }

  public function getQuestConnector( Request $request, Guild $guild, QuestConnector $connector ) {
      return response()->json($connector, 200);
  }

  public function deleteQuestConnector(Request $request, City $city, Guild $guild, QuestConnector $connector ) {
      QuestConnector::destroy($connector->id);
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

    /**
    * ==================================================================
    * GESTION DES POI
    * ==================================================================
    */

    public function getPOIs(City $city, Request $request){
        $pois = Stop::where('city_id', '=', $city->id)
            ->get();
        return response()->json($pois, 200);
    }

}
