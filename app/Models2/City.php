<?php

namespace App\Models;

use App\Models\Guild;
use Illuminate\Database\Eloquent\Model;

class City extends Model {

    protected $fillable = [
        'name',
        'slug',
        'lat',
        'lng',
    ];

    public function getGuildsIds() {
        $return = [];
        $guilds = Guild::where('city_id', $this->id)
            ->get();
        if( empty( $guilds ) ) return $return;
        foreach( $guilds as $guild ) {
            $return[] = $guild->id;
        }
        return $return;
    }

}
