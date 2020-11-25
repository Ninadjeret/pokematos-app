<?php

namespace App\Http\Controllers\App\Rankings;

use App\Models\City;
use Illuminate\Http\Request;
use App\Core\Rankings\UserRanking;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ShortRankingController extends Controller
{
    public function show(Request $request, City $city)
    {
        $user = Auth::user();
        $ranking = UserRanking::forRaids()
            ->forUser($user)
            ->forPeriod('2010-01-01', date('Y-m-d'))
            ->forCity($city)
            ->getShort();
        return response()->json($ranking, 200);
    }
}