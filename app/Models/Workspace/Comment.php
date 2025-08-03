<?php

namespace App\Models\Workspace;

use App\Models\User;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use CreatedUpdatedBy;
    protected $fillable = ['content', 'post_id'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
