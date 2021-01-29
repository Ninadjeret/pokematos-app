<?php

namespace App\Models;

use App\User;
use App\Models\Guild;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuildApiAccess extends Model
{
    use SoftDeletes;

    protected $table = 'guild_api_access';
    protected $fillable = ['user_id', 'guild_id', 'key', 'authorizations'];
    protected $appends = ['name'];
    protected $casts = ['authorizations' => 'array'];

    public static function boot()
    {
        parent::boot();
        static::created(function (GuildApiAccess $access) {
            $access->generateToken();
        });
    }


    public function getUserAttribute()
    {
        if (empty($this->user_id)) return false;
        $user = User::find($this->user_id);
        if ($user) return $user;
        return false;
    }

    public function getGuildAttribute()
    {
        $guild = Guild::find($this->guild_id);
        if ($guild) return $guild;
        return false;
    }

    public function getNameAttribute()
    {
        $user = $this->user;
        if ($user) return $user->name;
        return '';
    }

    public function getAuthorizationsAttribute($value)
    {
        if (empty($value)) return [];
        return json_decode($value);
    }

    private function createUser($args)
    {
        $user = User::create([
            'name' => $args['name'],
            'ext' => 1,
            'password' => Hash::make(str_random(20))
        ]);
        $this->update(['user_id' => $user->id]);
        return $user;
    }

    public function editUser($args)
    {
        if (!$this->user) $this->createUser($args);
        $this->user->update(['name' => $args['name']]);
        return true;
    }

    public function generateToken()
    {
        $key = $this->id . str_random(30);
        $this->update(['key' => $key]);
    }
}