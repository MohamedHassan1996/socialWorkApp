<?php

namespace App\Models\Workspace;

use App\Models\User;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'content',
        'workspace_id'
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'user_posts', 'post_id', 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments()
    {
        return $this->hasMany(PostAttachment::class);
    }

    public function getTotalCommentsAttribute()
    {
        return $this->comments()->count();
    }

    public function getTotalAttachmentsAttribute()
    {
        return $this->attachments()->count();
    }

}
