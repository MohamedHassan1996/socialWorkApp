<?php

namespace App\Services\Notification;

use App\Events\NotificationSent;
use App\Models\Notification\Notification;

class NotificationService
{
    public static function send($userId, $type, $title, $message, $data = [])
    {
        // Create notification in database
        $notification = Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);

        // Broadcast to user's channel
        broadcast(new NotificationSent($notification));

        return $notification;
    }

    public static function sendToMultiple($userIds, $type, $title, $message, $data = [])
    {
        foreach ($userIds as $userId) {
            self::send($userId, $type, $title, $message, $data);
        }
    }

    // Workspace notifications
    public static function workspaceInvite($userId, $workspace, $inviter)
    {
        return self::send(
            $userId,
            'workspace.invite',
            'Workspace Invitation',
            "{$inviter->name} invited you to join '{$workspace->name}'",
            [
                'workspace_id' => $workspace->id,
                'workspace_name' => $workspace->name,
                'inviter_name' => $inviter->name,
                'action_url' => "/workspaces/{$workspace->id}"
            ]
        );
    }

    public static function workspaceKicked($userId, $workspace, $kicker)
    {
        return self::send(
            $userId,
            'workspace.kicked',
            'Removed from Workspace',
            "You were removed from '{$workspace->name}' by {$kicker->name}",
            [
                'workspace_id' => $workspace->id,
                'workspace_name' => $workspace->name,
                'kicker_name' => $kicker->name
            ]
        );
    }

    public static function userJoinedWorkspace($memberIds, $workspace, $newUser)
    {
        $userIds = collect($memberIds)->reject($newUser->id);

        self::sendToMultiple(
            $userIds,
            'workspace.member_joined',
            'New Member',
            "{$newUser->name} joined '{$workspace->name}'",
            [
                'workspace_id' => $workspace->id,
                'workspace_name' => $workspace->name,
                'member_name' => $newUser->name,
                'action_url' => "/workspaces/{$workspace->id}"
            ]
        );
    }

    // Post notifications
    public static function postCreated($memberIds, $post, $author)
    {
        $userIds = collect($memberIds)->reject($author->id);

        self::sendToMultiple(
            $userIds,
            'post.created',
            'New Post',
            "{$author->name} created: {$post->title}",
            [
                'post_id' => $post->id,
                'post_title' => $post->title,
                'workspace_id' => $post->workspace_id,
                'author_name' => $author->name,
                'action_url' => "/workspaces/{$post->workspace_id}/posts/{$post->id}"
            ]
        );
    }

    public static function postCommented($post, $comment, $commenter)
    {
        // Notify post author
        if ($post->user_id !== $commenter->id) {
            self::send(
                $post->user_id,
                'post.commented',
                'New Comment',
                "{$commenter->name} commented on your post",
                [
                    'post_id' => $post->id,
                    'post_title' => $post->title,
                    'comment_id' => $comment->id,
                    'commenter_name' => $commenter->name,
                    'action_url' => "/workspaces/{$post->workspace_id}/posts/{$post->id}"
                ]
            );
        }
    }

    public static function postLiked($post, $liker)
    {
        if ($post->user_id !== $liker->id) {
            self::send(
                $post->user_id,
                'post.liked',
                'Post Liked',
                "{$liker->name} liked your post: {$post->title}",
                [
                    'post_id' => $post->id,
                    'post_title' => $post->title,
                    'liker_name' => $liker->name,
                    'action_url' => "/workspaces/{$post->workspace_id}/posts/{$post->id}"
                ]
            );
        }
    }
}
