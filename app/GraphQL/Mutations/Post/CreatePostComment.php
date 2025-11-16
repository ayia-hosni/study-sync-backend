<?php

namespace App\GraphQL\Mutations\Post;

use App\Models\PostComment;
use Illuminate\Support\Facades\Auth;

class CreatePostComment
{
    /**
     * Handle the GraphQL mutation to create a post comment.
     */
    public function __invoke($_, array $args)
    {
        $user = Auth::user();
        $input = $args['input'];

        $comment = PostComment::create([
            'post_id'      => $input['post_id'],
            'parent_id'    => $input['parent_id'] ?? null,
            'commenter_id' => $user->id,
            'content'      => $input['content'],
            'media_urls'   => $input['media_urls'] ?? [],
        ]);

        return $comment;
    }
}
