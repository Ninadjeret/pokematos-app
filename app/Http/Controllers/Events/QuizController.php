<?php

namespace App\Http\Controllers\Events;

use App\Models\QuizQuestion;
use App\Models\QuizTheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class QuizController extends Controller
{
    public function getAvailableQuestions(Request $request)
    {

        $difficulties = (empty($request->difficulties)) ? [1, 2, 3, 5] : $request->difficulties;
        $themes = (empty($request->themes)) ? \DB::table('quiz_themes')->pluck('id')->toArray() : $request->themes;

        $query = QuizQuestion::whereIn('difficulty', $difficulties)
            ->whereIn('theme_id', $themes);
        if ($request->only_pogo == 'true' || $request->only_pogo == 1) $query->where('about_pogo', 1);
        $questions = $query->get();

        return response()->json($questions->count(), 200);
    }
}