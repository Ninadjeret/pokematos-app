<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public static function get(Request $request)
    {
        $settings = Setting::get();
        return response()->json($settings, 200);
    }

    public static function update(Request $request)
    {
        $settings = $request->settings;
        Setting::change($settings);
        $settings = Setting::get();
        return response()->json($settings, 200);
    }
}