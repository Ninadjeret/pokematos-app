<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller {

    public function getUSer() {
        $user = User::find(3);
        return response()->json($user, 200);
    }
}
