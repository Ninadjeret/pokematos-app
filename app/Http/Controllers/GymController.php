<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Raid;
use App\Models\Stop;
use Illuminate\Support\Facades\Log;

class GymController extends Controller {

    public function getCityGyms(City $city, Request $request){

        $now = new \DateTime();
        $now->modify( '- 45 minutes' );
        $raids_ended = Raid::where('city_id', $city->id)
            ->where('status', '!=', 'archived')
            ->where('start_time', '<=', $now->format('Y-m-d H:i:s') )
            ->get();
        if( !empty( $raids_ended ) ) {
            foreach( $raids_ended as $raid ) {
                $raid->update(['status' => 'archived']);
                event( new \App\Events\RaidEnded( $raid ) );
            }
        }

        $gyms = Stop::where('city_id', '=', $city->id)
            ->where('gym', 1)
            ->get();
        return response()->json($gyms, 200);
    }

}
