<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MonitorController extends Controller
{
    public function monitorStatus( Request $request ) 
    {
        return response()->json('OK', 200);
    }
    
    public function monitorAnalyzer( Request $request )
    {
        $status = [
            'success' => 0,
            'error' => 0,
        ];
        $results = Log::select(\DB::raw('HOUR(created_at) as date, COUNT(id) as amount, success'))
            ->where('created_at', '>=', date('Y-m-d').' 00:00:00')
            ->groupBy(\DB::raw('HOUR(created_at)'), \DB::raw('success'))
            ->get();

        foreach( $results as $result ) {
            if( $result->success ) {
                $status['success'] += $result->amount;
            }  else {
                $status['error'] += $result->amount;
            }
        }
        
        $return_code = '200';
        $total = $status['success'] + $status['error'];
        if( $total > 50 ) {
            $percent =  $status['success'] / $total; 
            if( $percent < 0.5 ) $return_code = '450';
        }       
        
        return response()->json($return_code, 200);
        
    }
}
