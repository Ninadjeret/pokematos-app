<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\City;
use App\Models\Role;
use App\Models\Stop;
use App\Models\Quest;
use App\Models\Guild;
use App\Core\Helpers;
use App\Models\Connector;
use App\Models\UserAction;
use App\Models\QuestReward;
use Illuminate\Http\Request;
use App\Models\RoleCategory;
use App\Models\QuestInstance;
use App\ImageAnalyzer\Engine;
use App\Models\QuestConnector;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class UserController extends Controller {

    public function getUSer() {
        $user = Auth::user();
        $user->refreshDiscordToken();
        return response()->json($user, 200);
    }

    public function getCities() {
        $user = Auth::user();
        return response()->json($user->getCities(), 200);
    }

    public function updateCity( Request $request, City $city ) {
        $city->update([
            'lat' => $request->lat,
            'lng' => $request->lng,
        ]);
        return response()->json($city, 200);
    }

    public static function getGuildOptions( Request $request, City $city, Guild $guild ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        return response()->json($guild->settings, 200);
    }

    public static function updateGuildOptions( Request $request, City $city, Guild $guild ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
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

        //return response()->json('Annonce actuellement indisponible', 403);

        $params = [
            'city_id' => $city->id,
            'gym_id'  => $request->params['gym_id'],
        ];
        if( $request->params['quest_id'] ) $params['quest_id'] = $request->params['quest_id'];
        if( $request->params['reward_type'] ) $params['reward_type'] = $request->params['reward_type'];
        if( $request->params['reward_id'] ) $params['reward_id'] = $request->params['reward_id'];

        $instance = QuestInstance::add($params);

        return response()->json($instance, 200);
    }

    public function deleteQuest( City $city, QuestInstance $questInstance, Request $request ) {
        //return response()->json('Annonce actuellement indisponible', 403);
        event( new \App\Events\QuestInstanceDeleted( $questInstance ) );;
        $announces = $questInstance->getUserActions();
        if( !empty($announces) ) {
            foreach( $announces as $announce ) {
                UserAction::destroy($announce->id);
            }
        }
        $stop = Stop::find($questInstance->gym_id);
        $stop->touch();
        QuestInstance::destroy($questInstance->id);
        return response()->json(null, 204);
    }

    public function updateQuest( Request $request, City $city, QuestInstance $questInstance ) {

        //return response()->json('Annonce actuellement indisponible', 403);

        $updated = false;

        if(  isset($request->params['reward_type']) &&  isset($request->params['reward_id']) && $request->params['reward_type'] && $request->params['reward_id'] && empty($quest->reward_type) ) {
            $questInstance->update([
                'reward_type' => $request->params['reward_type'],
                'reward_id' => $request->params['reward_id'],
            ]);
            $updated = true;
        }

        if( isset($request->params['quest_id']) && $request->params['quest_id'] && empty($quest->quest_id) ) {
            $questRef = Quest::find($request->params['quest_id']);
            if( $questRef ) {
                $questInstance->update([
                    'quest_id' => $request->params['quest_id'],
                    'name' => $questRef->name,
                ]);
            }
            $updated = true;
        }

        if( $updated ) {
            $announce = UserAction::create([
                'type' => 'quest-update',
                'source' => ( !empty($request->params['type']) ) ? $request->params['type'] : 'map',
                'date' => date('Y-m-d H:i:s'),
                'user_id' => Auth::id(),
                'quest_instance_id' => $questInstance->id,
            ]);
            event( new \App\Events\QuestInstanceUpdated( $questInstance, $announce ) );
        }

        $stop = Stop::find($questInstance->gym_id);
        $stop->touch();

        return response()->json($questInstance, 200);
    }

    /**
    * ==================================================================
    * GESTION DES CONNECTEURS
    * ==================================================================
    */

   public function getConnectors( Request $request, Guild $guild ) {
       $user = Auth::user();
       if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
           return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
       }
       $connecteurs = Connector::where('guild_id', $guild->id)->get();
       return response()->json($connecteurs, 200);
   }

   public function createConnector( Request $request, Guild $guild ) {
       $user = Auth::user();
       if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
           return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
       }
       $connector = Connector::create([
           'name' => ( isset( $request->name ) ) ? $request->name : '' ,
           'guild_id' => $guild->id,
           'channel_discord_id' => ( isset( $request->channel_discord_id ) ) ? $request->channel_discord_id : '' ,
           'filter_gym_type' => ( isset( $request->filter_gym_type ) ) ? $request->filter_gym_type : '' ,
           'filter_gym_zone' => ( isset( $request->filter_gym_zone ) ) ? Helpers::extractIds($request->filter_gym_zone) : '' ,
           'filter_gym_gym' => ( isset( $request->filter_gym_gym ) ) ? Helpers::extractIds($request->filter_gym_gym) : '' ,
           'filter_pokemon_type' => ( isset( $request->filter_pokemon_type ) ) ? $request->filter_pokemon_type : '' ,
           'filter_pokemon_level' => ( isset( $request->filter_pokemon_level ) ) ? Helpers::extractIds($request->filter_pokemon_level) : '' ,
           'filter_pokemon_pokemon' => ( isset( $request->filter_pokemon_pokemon ) ) ? Helpers::extractIds($request->filter_pokemon_pokemon) : '' ,
           'filter_source_type' => ( isset( $request->filter_source_type ) ) ? $request->filter_source_type : '' ,
           'format' => ( isset( $request->format ) ) ? $request->format : 'auto' ,
           'custom_message_before' => ( isset( $request->custom_message_before ) ) ? $request->custom_message_before : '' ,
           'custom_message_after' => ( isset( $request->custom_message_after ) ) ? $request->custom_message_after : '' ,
           'auto_settings' => ( isset( $request->auto_settings ) ) ? $request->auto_settings : '' ,
           'delete_after_end' => ( isset( $request->delete_after_end ) ) ? $request->delete_after_end : '' ,
       ]);
       return response()->json($connector, 200);
   }

   public function updateConnector( Request $request, Guild $guild, Connector $connector ) {
       $user = Auth::user();
       if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
           return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
       }
       $connector->update([
           'name' => ( isset( $request->name ) ) ? $request->name : $connector->name ,
           'channel_discord_id' => ( isset( $request->channel_discord_id ) ) ? $request->channel_discord_id : $connector->channel_discord_id ,
           'filter_gym_type' => ( isset( $request->filter_gym_type ) ) ? $request->filter_gym_type : $connector->filter_gym_type ,
           'filter_gym_zone' => ( isset( $request->filter_gym_zone ) ) ? Helpers::extractIds($request->filter_gym_zone) : $connector->filter_gym_zone ,
           'filter_gym_gym' => ( isset( $request->filter_gym_gym ) ) ? Helpers::extractIds($request->filter_gym_gym) : $connector->filter_gym_gym ,
           'filter_pokemon_type' => ( isset( $request->filter_pokemon_type ) ) ? $request->filter_pokemon_type : $connector->filter_pokemon_type ,
           'filter_pokemon_level' => ( isset( $request->filter_pokemon_level ) ) ? Helpers::extractIds($request->filter_pokemon_level) : $connector->filter_pokemon_level ,
           'filter_pokemon_pokemon' => ( isset( $request->filter_pokemon_pokemon ) ) ? Helpers::extractIds($request->filter_pokemon_pokemon) : $connector->filter_pokemon_pokemon ,
           'filter_source_type' => ( isset( $request->filter_source_type ) ) ? $request->filter_source_type : $connector->filter_source_type ,
           'format' => ( isset( $request->format ) ) ? $request->format : $connector->format ,
           'custom_message_before' => ( isset( $request->custom_message_before ) ) ? $request->custom_message_before : $connector->custom_message_before ,
           'custom_message_after' => ( isset( $request->custom_message_after ) ) ? $request->custom_message_after : $connector->custom_message_after ,
           'auto_settings' => ( isset( $request->auto_settings ) ) ? $request->auto_settings : $connector->auto_settings ,
           'delete_after_end' => ( isset( $request->delete_after_end ) ) ? $request->delete_after_end : $connector->delete_after_end ,
       ]);
       return response()->json($connector, 200);
   }

   public function getConnector( Request $request, Guild $guild, Connector $connector ) {
       $user = Auth::user();
       if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
           return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
       }
       return response()->json($connector, 200);
   }

   public function deleteConnector(Request $request, City $city, Guild $guild, Connector $connector ) {
       $user = Auth::user();
       if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
           return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
       }
       Connector::destroy($connector->id);
       return response()->json(null, 204);
   }

   /**
   * ==================================================================
   * GESTION DES CONNECTEURS DE QUETES
   * ==================================================================
   */

  public function getQuestConnectors( Request $request, Guild $guild ) {
      $user = Auth::user();
      if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
          return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
      }
      $connecteurs = QuestConnector::where('guild_id', $guild->id)->get();
      return response()->json($connecteurs, 200);
  }

  public function createQuestConnector( Request $request, Guild $guild ) {
      $user = Auth::user();
      if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
          return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
      }
      $connector = QuestConnector::create([
          'name' => ( isset( $request->name ) ) ? $request->name : '' ,
          'guild_id' => $guild->id,
          'channel_discord_id' => ( isset( $request->channel_discord_id ) ) ? $request->channel_discord_id : '' ,
          'filter_reward_type' => ( isset( $request->filter_reward_type ) ) ? $request->filter_reward_type : '' ,
          'filter_reward_reward' => ( isset( $request->filter_reward_reward ) ) ? Helpers::extractIds($request->filter_reward_reward) : '' ,
          'filter_reward_pokemon' => ( isset( $request->filter_reward_pokemon ) ) ? Helpers::extractIds($request->filter_reward_pokemon) : '' ,
          'filter_stop_type' => ( isset( $request->filter_stop_type ) ) ? $request->filter_stop_type : '' ,
          'filter_stop_zone' => ( isset( $request->filter_stop_zone ) ) ? Helpers::extractIds($request->filter_stop_zone) : '' ,
          'filter_stop_stop' => ( isset( $request->filter_stop_stop ) ) ? Helpers::extractIds($request->filter_stop_stop) : '' ,
          'format' => ( isset( $request->format ) ) ? $request->format : 'auto' ,
          'custom_message' => ( isset( $request->custom_message ) ) ? $request->custom_message : '' ,
          'delete_after_end' => ( isset( $request->delete_after_end ) ) ? $request->delete_after_end : '' ,
      ]);
      return response()->json($connector, 200);
  }

  public function updateQuestConnector( Request $request, Guild $guild, QuestConnector $connector ) {
      $user = Auth::user();
      if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
          return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
      }
      $connector->update([
          'name' => ( isset( $request->name ) ) ? $request->name : $connector->name ,
          'channel_discord_id' => ( isset( $request->channel_discord_id ) ) ? $request->channel_discord_id : $connector->channel_discord_id ,
          'filter_reward_type' => ( isset( $request->filter_reward_type ) ) ? $request->filter_reward_type : $connector->filter_reward_type ,
          'filter_reward_reward' => ( isset( $request->filter_reward_reward ) ) ? Helpers::extractIds($request->filter_reward_reward) : $connector->filter_reward_reward ,
          'filter_reward_pokemon' => ( isset( $request->filter_reward_pokemon ) ) ? Helpers::extractIds($request->filter_reward_pokemon) : $connector->filter_reward_pokemon ,
          'filter_stop_type' => ( isset( $request->filter_stop_type ) ) ? $request->filter_stop_type : $connector->filter_stop_type ,
          'filter_stop_zone' => ( isset( $request->filter_stop_zone ) ) ? Helpers::extractIds($request->filter_stop_zone) : $connector->filter_stop_zone ,
          'filter_stop_stop' => ( isset( $request->filter_stop_stop ) ) ? Helpers::extractIds($request->filter_stop_stop) : $connector->filter_stop_stop ,
          'format' => ( isset( $request->format ) ) ? $request->format : $connector->format ,
          'custom_message' => ( isset( $request->custom_message ) ) ? $request->custom_message : $connector->custom_message ,
          'delete_after_end' => ( isset( $request->delete_after_end ) ) ? $request->delete_after_end : $connector->delete_after_end ,
      ]);
      return response()->json($connector, 200);
  }

  public function getQuestConnector( Request $request, Guild $guild, QuestConnector $connector ) {
      $user = Auth::user();
      if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
          return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
      }
      return response()->json($connector, 200);
  }

  public function deleteQuestConnector(Request $request, City $city, Guild $guild, QuestConnector $connector ) {
      $user = Auth::user();
      if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
          return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
      }
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
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }

        $args = [
            'guild_id' => $guild->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'color_type' => $request->color_type,
            'color' => $request->color,
            'type' => ( $request->type ) ? $request->type : null,
            'gym_id' => ( $request->gym_id ) ? $request->gym_id : null,
            'zone_id' => ( $request->zone_id ) ? $request->zone_id : null,
            'pokemon_id' => ( $request->pokemon_id ) ? $request->pokemon_id : null,
        ];
        $role = Role::add($args);
        return response()->json($role, 200);
    }

    public function getRole(Request $request, City $city, Guild $guild, Role $role ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        return response()->json($role, 200);
    }

    public function updateRole(Request $request, City $city, Guild $guild, Role $role ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }

        $role->change([
            'name' => ($request->name) ? $request->name : $role->name,
            'category_id' => $request->category_id,
            'color_type' => ( $request->color_type ) ? $request->color_type : $role->color_type,
            'color' => ( $request->color ) ? $request->color : $role->color,
            'type' => ( $request->type ) ? $request->type : $role->type,
            'gym_id' => ( $request->gym_id ) ? $request->gym_id : $role->gym_id,
            'zone_id' => ( $request->zone_id ) ? $request->zone_id : $role->zone_id,
            'pokemon_id' => ( $request->pokemon_id ) ? $request->pokemon_id : $role->pokemon_id,
        ]);
        return response()->json($role, 200);
    }

    public function deleteRole(Request $request, City $city, Guild $guild, Role $role ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
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
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $args = [];
        $args['guild_id'] = $guild->id;
        $args['name'] = $request->name;
        $args['color'] = $request->color;
        $args['notifications'] = ($request->notifications) ? $request->notifications : 0;
        $args['restricted'] = ($request->restricted) ? $request->restricted : 0;
        if( isset($request->channel_discord_id) ) $args['channel_discord_id'] = $request->channel_discord_id;
        $categorie = RoleCategory::create($args);
        $categorie->savePermissions($request->permissions, $request->permissions_to_delete);
        return response()->json($categorie, 200);
    }

    public function getRoleCategory(Request $request, City $city, Guild $guild, RoleCategory $categorie ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        return response()->json($categorie, 200);
    }

    public function updateRoleCategory(Request $request, City $city, Guild $guild, RoleCategory $categorie ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $categorie->update([
            'name' => ($request->name) ? $request->name : $categorie->name,
            'color' => ($request->color) ? $request->color : $categorie->color,
            'channel_discord_id' => ($request->channel_discord_id) ? $request->channel_discord_id : $categorie->channel_discord_id,
            'restricted' => ($request->restricted) ? $request->restricted : $categorie->restricted,
            'notifications' => ($request->notifications) ? $request->notifications : 0,
        ]);
        $categorie->savePermissions($request->permissions, $request->permissions_to_delete);
        return response()->json($categorie, 200);
    }

    public function deleteRoleCategory(Request $request, City $city, Guild $guild, RoleCategory $categorie ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        RoleCategory::destroy($categorie->id);
        return response()->json(null, 204);
    }

    /**
    * ==================================================================
    * GESTION DES POI
    * ==================================================================
    */

    public function getPOIs(City $city, Request $request){

        $query = Stop::where('city_id', '=', $city->id)
            ->orderBy('name', 'asc');

        $lastUpdate = $request->last_update;
        if( !empty( $lastUpdate ) && \DateTime::createFromFormat('Y-m-d H:i:s', $lastUpdate) !== false ) {
            $date = new \DateTime($lastUpdate);
            $date->modify('-10 minutes');
            $pois = $query->where('updated_at', '>=', $date->format('Y-m-d H:i:s'))->get();
        } else {
            $pois = $query->get();
        }

        return response()->json($pois, 200);
    }

    public function getActivePOIs(City $city, Request $request){
        $gyms = Stop::whereHas('raids', function (Builder $query) {
                $start = new \DateTime();
                $start->modify('- 45 minutes');
                $end = new \DateTime();
                $end->modify('+ 60 minutes');
                $query->whereBetween('start_time', [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')]);
            })
            ->where('gym', 1)
            ->get()
            ->toArray();
        $stops = Stop::whereHas('quests', function (Builder $query) {
                $query->where('date', date('Y-m-d 00:00:00'));
            })
            ->where('gym', 0)
            ->get()
            ->toArray();
        $return = array_merge($gyms, $stops);
        return response()->json($return, 200);

    }

    public function getGuildLogs(Guild $guild, Request $request) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $logs = \App\Models\Log::where('guild_id', $guild->id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();
        return response()->json($logs, 200);
    }

    public function getCityLogs(City $city, Request $request) {
        $user = Auth::user();
        if( !$user->can('logs_manage', ['city_id' => $city->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $logs = \App\Models\Log::where('city_id', $city->id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();
        return response()->json($logs, 200);
    }

    public function uploadImage(Request $request) {
        $user = Auth::user();
        if( !empty( $request->image ) ) {
            $imageName = time().'_500x270.'.$request->image->getClientOriginalExtension();
            $path = "app/public/user/{$user->id}/{$imageName}";
            $image = \Intervention\Image\Facades\Image::make($request->image->getRealPath())->fit(500, 270)->save(storage_path($path));
            //$request->image->resize(200, 200)->move(storage_path($path), $imageName);
        }
        return $imageName;
    }

}
