<?php

namespace App\GraphQL\Mutations\Post;

use App\Models\PostComment;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class DeletePostComment
{
    public function __invoke($_, array $args, GraphQLContext $context): PostComment
    {
        $comment = PostComment::findOrFail($args['id']);
        
        // Authorization is handled by @can directive
        $user = $context->user() ?? Auth::user();
        if ($user->id !== $comment->commenter_id) {
            throw new \Exception('Unauthorized to delete this comment');
        }
        
        // Return the comment before deleting
        $deletedComment = $comment->replicate();
        $deletedComment->id = $comment->id;
        
        $comment->delete();
        
        return $deletedComment;
    }
}
