<?php

namespace App\Http\Controllers\App\Rankings;

use App\Models\City;
use Illuminate\Http\Request;
use App\Core\Rankings\UserRanking;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RankingController extends Controller
{
    public function show(Request $request, City $city)
    {

        $args = $request->all();
        $period = $this->getPeriod($args);
        $user = Auth::user();

        if ($request->type == 'raids') {
            $query = UserRanking::forRaids()->forCity($city);
        } elseif ($request->type == 'top100') {
            $query = UserRanking::forAll()->setLimit(100);
        } elseif ($request->type == 'quests') {
            $query = UserRanking::forQuests()->forCity($city);
        } else {
            $query = UserRanking::forRocket()->forCity($city);
        }

        $ranking = $query->forUser($user)
            ->forPeriod($period->start, $period->end)
            ->getComplete();
        return response()->json($ranking, 200);
    }

    public function getPeriod($args)
    {
        $now = new \DateTIme();
        $period_type = isset($args['period_type']) ? $args['period_type'] : 'abs';

        if ($period_type == 'month') {
            $start = $now->format('Y-m' . '-01');
            $end = $now->format('Y-m-t');
        } elseif ($period_type == 'custom') {
            $start = isset($args['period_start']) ? $args['period_start'] : $now->format('Y-m' . '-01');
            $end = isset($args['period_end']) ? $args['period_end'] : $now->format('Y-m-t');
        } else {
            $start = '2010-01-01';
            $end = $now->format('Y-m-d');
        }

        return (object) [
            'start' => $start,
            'end' => $end,
        ];
    }
}