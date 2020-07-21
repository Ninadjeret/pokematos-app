<?php

namespace App\Http\Controllers\Events;

use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuizQuestionController extends Controller
{
  public function index(Request $request)
  {
    $questions = QuizQuestion::all();
    return response()->json($questions, 200);
  }
}