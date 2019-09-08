<?php

namespace App\Http\Controllers;

use App\Models\Stop;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getVersion( Request $request ) {
        return response()->json(config('app.version'), 200);
    }

    public function test( Request $request ) {
        $stops = Stop::whereHas('raid', function (Builder $query) {
                $start = new \DateTime();
                $start->modify('- 45 minutes');
                $end = new \DateTime();
                $end->modify('+ 60 minutes');
                $query->whereBetween('start_time', [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')]);
            })
            ->where('gym', 1)
            ->get()
            ->keyBy('id');
        return response()->json($stops, 200);
    }
}
