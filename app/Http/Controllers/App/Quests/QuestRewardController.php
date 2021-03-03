<?php

namespace App\Http\Controllers\App\Quests;

use App\Models\QuestReward;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuestRewardController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(QuestReward::all(), 200);
    }
}
