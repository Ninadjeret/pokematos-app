<?php

namespace App\Http\Controllers\App\Quests;

use App\Models\Quest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class QuestController extends Controller
{
    public function index(Request $request)
    {
        $quests = Quest::orderBy('event', 'desc')
            ->orderBy('name', 'asc')
            ->get();
        return response()->json($quests, 200);
    }

    public function show(Request $request, Quest $quest)
    {
        return response()->json($quest, 200);
    }

    public function store(Request $request)
    {
        $args = $request->all();
        $quest = Quest::create($args);
        return response()->json($quest, 200);
    }

    public function update(Request $request, Quest $quest)
    {
        $args = $request->all();
        $quest->update($args);
        return response()->json($quest, 200);
    }

    public function destroy(Request $request, Quest $quest)
    {
        Quest::destroy($quest->id);
        return response()->json(null, 204);
    }
}
