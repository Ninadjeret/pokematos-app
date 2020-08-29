<?php

namespace App\Http\Controllers\App;

use App\Models\Stop;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PoisController extends Controller
{
    public function index(City $city, Request $request)
    {

        $query = Stop::where('city_id', '=', $city->id)
            ->orderBy('name', 'asc');

        $lastUpdate = $request->last_update;
        if ($lastUpdate != 'initial' && !empty($lastUpdate) && \DateTime::createFromFormat('Y-m-d H:i:s', $lastUpdate) == false) {
            Log::error("Date dans un formation incorrect : {$lastUpdate}");
        }
        if ($lastUpdate != 'initial' && !empty($lastUpdate) && \DateTime::createFromFormat('Y-m-d H:i:s', $lastUpdate) !== false) {
            $date = new \DateTime($lastUpdate);
            $date->modify('-10 minutes');
            $pois = $query->where('updated_at', '>=', $date->format('Y-m-d H:i:s'))->get();
        } else {
            $pois = $query->get()->each->setAppends(['zone', 'city', 'google_maps_url', 'aliases']);
        }

        return response()->json($pois, 200);
    }
}