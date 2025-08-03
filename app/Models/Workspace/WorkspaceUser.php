<?php

namespace App\Models\Workspace;

use App\Models\Authorization\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WorkspaceUser extends Model
{
    protected $fillable = ['workspace_id', 'user_id', 'role_id'];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
