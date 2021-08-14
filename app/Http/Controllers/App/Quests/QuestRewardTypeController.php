<?php

namespace App\Http\Controllers\App\Quests;

use Illuminate\Http\Request;
use App\Models\QuestRewardType;
use App\Http\Controllers\Controller;

class QuestRewardTypeController extends Controller
{
    public function index(Request $request)
    {
        $types = QuestRewardType::orderBy('name', 'asc')->get();
        return response()->json($types, 200);
    }
}
