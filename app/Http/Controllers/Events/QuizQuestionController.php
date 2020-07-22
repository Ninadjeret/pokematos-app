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

  public function show(QuizQuestion $question, Request $request)
  {
    return response()->json($question, 200);
  }

  public function destroy(QuizQuestion $question, Request $request)
  {
    QuizQuestion::destroy($question->id);
    return response()->json(null, 204);
  }

  public function update(QuizQuestion $question, Request $request)
  {
    $question->update($request->all());
    return response()->json($question, 200);
  }
}