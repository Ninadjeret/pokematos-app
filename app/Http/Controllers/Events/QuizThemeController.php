<?php

namespace App\Http\Controllers\Events;

use App\Models\QuizTheme;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuizThemeController extends Controller
{
    public function index(Request $request)
    {
        $themes = QuizTheme::all();
        return response()->json($themes, 200);
    }
}