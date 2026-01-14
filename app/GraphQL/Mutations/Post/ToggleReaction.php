<?php

namespace App\GraphQL\Mutations\Post;

use App\Models\Post;
use App\Models\PostReaction;
use Illuminate\Support\Facades\Auth;

class ToggleReaction
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        $user = Auth::user();
        $postId = $args['post_id'];
        $emoji = $args['emoji'];

        $post = Post::findOrFail($postId);

        // check if already reacted with ANY emoji (or specific one? Plan said generic/flexible)
        // For now, let's assume one reaction per user per post for simplicity in "Like" logic,
        // OR allow changing emojis.
        // User request "react on posts... table... create_post_reactions_table"
        // Table has unique(['post_id', 'user_id', 'emoji']); 
        // IF we want simple "Like" toggle, we should toggle the specific emoji.
        
        $existingReaction = PostReaction::where('post_id', $postId)
            ->where('user_id', $user->id)
            ->where('emoji', $emoji)
            ->first();

        if ($existingReaction) {
            $existingReaction->delete();
        } else {
            PostReaction::create([
                'post_id' => $postId,
                'user_id' => $user->id,
                'emoji' => $emoji,
                'reacted_at' => now(),
            ]);
        }

        return $post->fresh();
    }
}
