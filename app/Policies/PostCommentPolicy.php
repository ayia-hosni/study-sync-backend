<?php

namespace App\Policies;

use App\Models\PostComment;
use App\Models\User;

class PostCommentPolicy
{
    /**
     * Determine if the user can update the comment.
     */
    public function update(User $user, PostComment $comment): bool
    {
        return $user->id === $comment->commenter_id;
    }

    /**
     * Determine if the user can delete the comment.
     */
    public function delete(User $user, PostComment $comment): bool
    {
        return $user->id === $comment->commenter_id;
    }
}
