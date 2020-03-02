<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\City;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function getGlobalIAReport( Request $request ) {

        $year_array = [];
        $day_array = [];

        //Résultats par an
        $results = Log::select(\DB::raw('DATE(created_at) as date, COUNT(id) as amount, success'))
            ->groupBy(\DB::raw('DATE(created_at)'), \DB::raw('success'))
            ->get();

        foreach( $results as $result ) {
            if( !array_key_exists($result->date, $year_array) ) {
                $year_array[$result->date] = [
                    'success' => 0,
                    'error'   => 0,
                ];
            }
            if( $result->success ) {
                $year_array[$result->date]['success'] = $result->amount;
            }  else {
                $year_array[$result->date]['error'] = $result->amount;
            }
        }


        //Résultats par jour
        $results = Log::select(\DB::raw('HOUR(created_at) as date, COUNT(id) as amount, success'))
            ->where('created_at', '>=', date('Y-m-d').' 00:00:00')
            ->groupBy(\DB::raw('HOUR(created_at)'), \DB::raw('success'))
            ->get();

        foreach( ['00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23'] as $hour ) {
                $day_array[$hour] = [
                    'success' => 0,
                    'error'   => 0,
                ];
            foreach( $results as $result ) {
                if( $result->success ) {
                    $day_array[strval($result->date)]['success'] = $result->amount;
                }  else {
                    $day_array[strval($result->date)]['error'] = $result->amount;
                }
            }
        }

        $meta = [];

        foreach( $year_array as &$data ) {
            $data['percentage_errors'] = round($data['error'] / $data['success'] * 100, 2);
        }
        foreach( $day_array as &$data ) {
            $total = $data['success'] + $data['error'];
            if( $total == 0 ) $total = 1;
            $data['percentage_errors'] = round($data['error'] / $total  * 100, 2);
            $data['percentage_success'] = round($data['success'] / $total * 100, 2);
        }

        ksort($year_array);
        //ksort($day_array);
        $return = [
            'year' => $year_array,
            'day'  => $day_array,
            'meta' => $meta,
        ];

        return response()->json($return, 200);
    }

    public function getCityIAReport( Request $request, $city_slug ) {

        $city = City::where('slug', $city_slug)->first();

        $year_array = [];
        $day_array = [];

        //Résultats par an
        $results = Log::select(\DB::raw('DATE(created_at) as date, COUNT(id) as amount, success'))
            ->where('city_id', $city->id)
            ->groupBy(\DB::raw('DATE(created_at)'), \DB::raw('success'))
            ->get();

        foreach( $results as $result ) {
            if( !array_key_exists($result->date, $year_array) ) {
                $year_array[$result->date] = [
                    'success' => 0,
                    'error'   => 0,
                ];
            }
            if( $result->success ) {
                $year_array[$result->date]['success'] = $result->amount;
            }  else {
                $year_array[$result->date]['error'] = $result->amount;
            }
        }


        //Résultats par jour
        $results = Log::select(\DB::raw('HOUR(created_at) as date, COUNT(id) as amount, success'))
            ->where('city_id', $city->id)
            ->where('created_at', '>=', date('Y-m-d').' 00:00:00')
            ->groupBy(\DB::raw('HOUR(created_at)'), \DB::raw('success'))
            ->get();

        foreach( ['0','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23'] as $hour ) {
                $day_array[$hour] = [
                    'success' => 0,
                    'error'   => 0,
                ];
            foreach( $results as $result ) {
                if( $result->success ) {
                    $day_array[strval($result->date)]['success'] = $result->amount;
                }  else {
                    $day_array[strval($result->date)]['error'] = $result->amount;
                }
            }
        }

        $meta = [];

        foreach( $year_array as &$data ) {
            $data['percentage_errors'] = round($data['error'] / $data['success'] * 100, 2);
        }
        foreach( $day_array as &$data ) {
            $total = $data['success'] + $data['error'];
            if( $total == 0 ) $total = 1;
            $data['percentage_errors'] = round($data['error'] / $total  * 100, 2);
            $data['percentage_success'] = round($data['success'] / $total * 100, 2);
        }

        ksort($year_array);
        //ksort($day_array);
        $return = [
            'year' => $year_array,
            'day'  => $day_array,
            'meta' => $meta,
        ];

        return response()->json($return, 200);
    }
}
