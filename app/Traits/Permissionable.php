<?php

namespace App\Traits;

use App\Models\Guild;
use Illuminate\Support\Facades\Log;

trait Permissionable
{
  static $permissions = [
    'city_access' => [
      'label' => 'Accéder à la ville',
      'context' => 'city'
    ],
    'guild_access' => [
      'label' => 'Accéder à la guild',
      'context' => 'guild'
    ],
    'raid_delete' => [
      'label' => 'Supprimer des raids',
      'context' => 'city'
    ],
    'raidex_add' => [
      'label' => 'Annoncer des Raids EX',
      'context' => 'city'
    ],
    'poi_edit' => [
      'label' => 'Gérer les POIs',
      'context' => 'city'
    ],
    'zone_edit' => [
      'label' => 'Gérer les zones',
      'context' => 'city'
    ],
    'logs_manage' => [
      'label' => 'Gérer les logs',
      'context' => 'city'
    ],
    'boss_edit' => [
      'label' => 'Mettre à jour les boss de raid',
      'context' => 'global'
    ],
    'quest_edit' => [
      'label' => 'Mettre à jour les quêtes',
      'context' => 'global'
    ],
    'rocket_bosses_edit' => [
      'label' => 'Mettre à jour les Boss Rocket',
      'context' => 'global'
    ],
    'guild_manage' => [
      'label' => 'Gérer la guild',
      'context' => 'guild'
    ],
    'events_manage' => [
      'label' => 'Gérer les évents',
      'context' => 'guild'
    ],
    'events_train_check' => [
      'label' => 'Gérer l\'avancement d\'un pokétrain',
      'context' => 'guild'
    ],
    'quiz_manage' => [
      'label' => 'Gérer les quizs',
      'context' => 'specific'
    ],
    'settings_manage' => [
      'label' => 'Gérer les réglages généraux',
      'context' => 'global'
    ]
  ];

  public static function getPermissions($types = null)
  {
    if (empty($types)) return self::$permissions;
    if (is_string($types)) {
      return array_filter(self::$permissions, function ($permission) use ($types) {
        return $permission['context'] == $types;
      });
    }
    if (is_array($types)) {
      return array_filter(self::$permissions, function ($permission) use ($types) {
        return in_array($permission['context'], $types);
      });
    }
    return [];
  }

  public function getCurrentPermissions()
  {
    //Get all existing permission, without personal permissions which do not depends on guilds
    $permissions = self::getPermissions(['guild', 'city', 'global']);
    $userPermissions = [];

    foreach ($this->getGuilds() as $guild) {
      switch ($guild->permissions) {
        case 30:
          $userPermissions[$guild->id] = array_keys($permissions);
          break;
        case 20:
          $userPermissions[$guild->id] = array_keys($permissions);
          break;
        case 10:
          $userPermissions[$guild->id] = $guild->settings->access_moderation_permissions;
          $userPermissions[$guild->id][] = 'guild_access';
          $userPermissions[$guild->id][] = 'city_access';
          break;
        case 0:
          $userPermissions[$guild->id] = ['city_access', 'guild_access'];
          break;
      }
      $specificPermissions = \App\Models\UserPermission::where('user_id', $this->id)
        ->get()
        ->pluck('permission')->toArray();
      $userPermissions[$guild->id] = array_merge($userPermissions[$guild->id], $specificPermissions);
    }

    return $userPermissions;
  }

  public function can($permission, $context = [])
  {
    $permissions = self::getPermissions();
    $userPermissions = $this->permissions;

    if ($this->superadmin) {
      return true;
    }

    if (!array_key_exists($permission, $permissions)) {
      return false;
    }

    $permissionContext = $permissions[$permission]['context'];
    switch ($permissionContext) {
      case 'global':
        foreach ($userPermissions as $guild_id => $guild_permissions) {
          $guild = Guild::find($guild_id);
          if (!$guild) {
            continue;
          }
          if (in_array($permission, $guild_permissions)) {
            return true;
          }
        }
        return false;
        break;
      case 'city':
        foreach ($userPermissions as $guild_id => $guild_permissions) {
          $guild = Guild::find($guild_id);
          if (!$guild) {
            continue;
          }
          if ($guild->city_id == $context['city_id'] && in_array($permission, $guild_permissions)) {
            return true;
          }
        }
        return false;
        break;
      case 'guild':
        foreach ($userPermissions as $guild_id => $guild_permissions) {
          $guild = Guild::find($guild_id);
          if (!$guild) {
            continue;
          }
          if ($guild->id == $context['guild_id'] && in_array($permission, $guild_permissions)) {
            return true;
          }
        }
        return false;
        break;
      case 'specific':
        $result = \App\Models\UserPermission::where('user_id', $this->id)
          ->where('permission', $permission)
          ->first();
        return (!empty($result)) ? true : false;
        break;
    }

    return false;
  }
}