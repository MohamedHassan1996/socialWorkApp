<?php

namespace App\Services\Select;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserSelectService
{

    public function getAllUsers()
    {
        return User::all(['id as value', 'name as label', 'email as email', 'avatar as avatar']);
    }

    public function getAllUsersWithAvatar()
    {
        $users = User::all(['id as value', 'name as label', 'email as email', 'avatar as avatar']);

        foreach ($users as $user) {
            if (!$user->avatar) {
                $user->avatar = "";
            }

        }

        return $users;

    }
    public function getAllPersons()
    {
        return User::all(['id as value', 'name as label']);
    }

    public function getAllRelatedPersons(?int $workspaceId = null){

        $auth = auth()->user();

        // 1️⃣ Determine which workspace IDs to use
        if ($workspaceId) {
            $workspaceIds = [$workspaceId];
        } else {
            $workspaceIds = DB::table('workspace_users')
                ->where('user_id', $auth->id)
                ->pluck('workspace_id')
                ->toArray();
        }

        // 2️⃣ Get all other users in those workspaces
        $persons = DB::table('workspace_users as wu')
            ->join('users as u', 'wu.user_id', '=', 'u.id')
            ->whereIn('wu.workspace_id', $workspaceIds)
            ->where('u.id', '!=', $auth->id) // exclude self
            ->select('u.id as value', 'u.name as label', 'u.avatar as avatar', 'u.email as email')
            ->distinct()
            ->get();

        foreach ($persons as $person) {
            if (!$person->avatar) {
                $person->avatar = "";
            }else{
                $person->avatar = Storage::disk('public')->url($person->avatar);
            }

        }

        return $persons;

    }
}
