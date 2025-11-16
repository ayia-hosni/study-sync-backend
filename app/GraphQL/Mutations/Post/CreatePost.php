<?php

namespace App\GraphQL\Mutations\Post;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class CreatePost
{
    public function __invoke($_, array $args, GraphQLContext $context): Post
    {
        $user = $context->user() ?? Auth::user();
        if (!$user) {
            throw new UnauthorizedHttpException('Bearer', 'Unauthenticated.');
        }

        $input = $args['input'];

        $post = new Post();
        $post->author_id  = $user->id;
        $post->room_id    = $input['room_id'] ?? null;
        $post->content    = $input['content'];
        $post->visibility = $input['visibility'];
        $post->type       = $input['type'];

        // ---------------------------
        // Handle file uploads
        // ---------------------------
        $mediaUrls = [];

        if (!empty($input['media'])) {
            foreach ($input['media'] as $file) {
                // $file is an Illuminate\Http\UploadedFile
                $path = $file->store('posts', 'public');
                $mediaUrls[] = asset('storage/' . $path);
            }
        }

        $post->media_urls = $mediaUrls;

        $post->save();

        return $post->fresh();
    }
}
