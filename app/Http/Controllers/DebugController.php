<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugController extends Controller
{
    public function log(Request $request) {
        Log::channel('appdebug')->info($request->ip().' ============================================================');
        Log::channel('appdebug')->info($request->header('User-Agent'));
        Log::channel('appdebug')->info('USER ----------------------------------------------------');
        Log::channel('appdebug')->info($request->user);
        Log::channel('appdebug')->info('SETTINGS ----------------------------------------------------');
        Log::channel('appdebug')->info($request->settings);
        Log::channel('appdebug')->info('CURRENTCITY ----------------------------------------------------');
        Log::channel('appdebug')->info($request->currentCity);
        Log::channel('appdebug')->info('CITIES ----------------------------------------------------');
        Log::channel('appdebug')->info($request->cities);
        Log::channel('appdebug')->info('GYMS ----------------------------------------------------');
        Log::channel('appdebug')->info($request->gyms);
    }
}
