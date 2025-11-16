<?php

namespace App\GraphQL\Queries\Post;

use App\Models\PostComment;
use Illuminate\Pagination\LengthAwarePaginator;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use GraphQL\Type\Definition\ResolveInfo;

final class Comments
{
    /**
     * @param  null  $root
     * @param  array{}  $args
     * @param  GraphQLContext  $context
     * @param  ResolveInfo  $resolveInfo
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): LengthAwarePaginator
    {
        // You can filter comments by post_id, or just paginate all
        $query = PostComment::query();

        if (isset($args['post_id'])) {
            $query->where('post_id', $args['post_id']);
        }

        // Use default pagination params from Lighthouse
        return $query->paginate(
            $args['first'] ?? 10,
            ['*'],
            'page',
            $args['page'] ?? 1
        );
    }
}
