<?php

namespace App\GraphQL\Mutations\Post;

use App\Models\PostComment;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class UpdatePostComment
{
    public function __invoke($_, array $args, GraphQLContext $context): PostComment
    {
        $comment = PostComment::findOrFail($args['id']);
        
        // Authorization is handled by @can directive
        $user = $context->user() ?? Auth::user();
        if ($user->id !== $comment->commenter_id) {
            throw new \Exception('Unauthorized to update this comment');
        }
        
        $input = $args['input'];
        
        // Only update provided fields
        if (isset($input['content'])) {
            $comment->content = $input['content'];
        }
        
        if (isset($input['media_urls'])) {
            $comment->media_urls = $input['media_urls'];
        }
        
        $comment->save();
        
        return $comment->fresh();
    }
}
