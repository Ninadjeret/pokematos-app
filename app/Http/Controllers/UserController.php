<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\City;
<<<<<<< HEAD
use App\Models\Role;
use App\Models\Guild;
use App\Models\RoleCategory;
=======
use App\Models\Guild;
>>>>>>> 7825fa41493a2ae4c4324d72e593c91c40f72664
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller {

    public function getUSer() {
        $user = Auth::user();
        return response()->json($user, 200);
    }

    public function getCities() {
        $user = Auth::user();
        return response()->json($user->getCities(), 200);
    }

    public static function getGuildOptions( Request $request, City $city, Guild $guild ) {
        return response()->json($guild->settings, 200);
    }

    public static function updateGuildOptions( Request $request, City $city, Guild $guild ) {
        $settings = $request->settings;
        $guild->updateSettings($settings);
        return response()->json($guild, 200);
    }

<<<<<<< HEAD
    /**
    * ==================================================================
    * GESTION DES ROLES
    * ==================================================================
    */

    public function getRoles(Request $request, City $city, Guild $guild ) {
        $roles = Role::where('guild_id', $guild->id)->get();
        return response()->json($roles, 200);
    }

    public function createRole(Request $request, City $city, Guild $guild ) {

        $roleCategory = RoleCategory::find($request->category_id);
        if( empty($roleCategory) ) return response()->json('La catégorie n\'existe pas', 400);

        $role = Role::add([
            'guild_id' => $guild->id,
            'category_id' => $roleCategory->id,
            'name' => $request->name,
            'type' => ( $request->type ) ? $request->type : null,
            'relation_id' => ( $request->relation_id ) ? $request->relation_id : null,
        ]);
        return response()->json($role, 200);
    }

    public function getRole(Request $request, City $city, Guild $guild, Role $role ) {
        return response()->json($role, 200);
    }

    public function updateRole(Request $request, City $city, Guild $guild, Role $role ) {
        $roleCategory = RoleCategory::find($request->category_id);
        if( empty($roleCategory) ) return response()->json('La catégorie n\'existe pas', 400);

        $role->change([
            'name' => ($request->name) ? $request->name : $role->name,
            'category_id' => $roleCategory->id,
            'type' => ( $request->type ) ? $request->type : $role->type,
            'relation_id' => ( $request->relation_id ) ? $request->relation_id : $role->relation_id,
        ]);
        return response()->json($role, 200);
    }

    public function deleteRole(Request $request, City $city, Guild $guild, Role $role ) {
        $role->suppr();
        return response()->json(null, 204);
    }

    /**
    * ==================================================================
    * GESTION DES CATEGORIES DE ROLES
    * ==================================================================
    */

    public function getRoleCategories(Request $request, City $city, Guild $guild ) {
        $categories = RoleCategory::where('guild_id', $guild->id)->get();
        return response()->json($categories, 200);
    }

    public function createRoleCategory(Request $request, City $city, Guild $guild ) {
        $categorie = RoleCategory::create([
            'guild_id' => $guild->id,
            'name' => $request->name,
            'channel_discord_id' => $request->channel_discord_id,
            'restricted' => ($request->restricted) ? $request->restricted : 0,
        ]);
        return response()->json($categorie, 200);
    }

    public function getRoleCategory(Request $request, City $city, Guild $guild, RoleCategory $categorie ) {
        return response()->json($categorie, 200);
    }

    public function updateRoleCategory(Request $request, City $city, Guild $guild, RoleCategory $categorie ) {
        $categorie->update([
            'name' => ($request->name) ? $request->name : $categorie->name,
            'channel_discord_id' => ($request->channel_discord_id) ? $request->channel_discord_id : $categorie->channel_discord_id,
            'restricted' => ($request->restricted) ? $request->restricted : $categorie->restricted,
        ]);
        return response()->json($categorie, 200);
    }

    public function deleteRoleCategory(Request $request, City $city, Guild $guild, RoleCategory $categorie ) {
        RoleCategory::destroy($categorie->id);
        return response()->json(null, 204);
    }

=======
>>>>>>> 7825fa41493a2ae4c4324d72e593c91c40f72664
}
