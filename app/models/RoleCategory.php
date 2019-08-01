<?php

namespace App\Models;

use App\Models\RolePermission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class RoleCategory extends Model {

    protected $fillable = ['guild_id', 'name', 'restricted', 'channel_discord_id', 'notifications'];
    protected $appends = ['guild', 'permissions'];
    protected $hidden = ['guild_id'];
    protected $casts = [
        'restricted' => 'boolean',
        'notifications' => 'boolean'
    ];

    public function getGuildAttribute() {
        return Guild::find($this->guild_id);
    }

    public function getPermissionsAttribute() {
        return RolePermission::where('role_category_id', $this->id)->get();
    }

    public function savePermissions( $permissions, $to_delete ) {
        if( $permissions ) {
            foreach( $permissions as $permission ) {
                if( $permission['id'] ) {
                    $object = RolePermission::find($permission['id']);
                    $object->update([
                        'channels' => $permission['channels'],
                        'type' => $permission['type'],
                        'roles' => $permission['roles'],
                    ]);
                } else {
                    $object = RolePermission::create([
                        'role_category_id' => $this->id,
                        'channels' => $permission['channels'],
                        'type' => $permission['type'],
                        'roles' => $permission['roles'],
                    ]);
                }

            }
        }
        if( $to_delete ) {
            foreach( $to_delete as $permission_id ) {
                RolePermission::destroy($permission_id);
            }
        }
    }

}
