<?php

namespace App\Grpc\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Recommendation\BatchPostRequest;
use Recommendation\BatchPostResponse;
use Recommendation\PostRequest;
use Recommendation\PostResponse;
use Recommendation\UserProfileRequest;
use Recommendation\UserProfileResponse;
use Spiral\RoadRunner\GRPC\ContextInterface;

/**
 * gRPC Service Handler for Post Details
 *
 * This service provides post and user information to the
 * Spring Boot Recommendation Service via gRPC calls.
 */
class PostDetailServiceHandler
{
    /**
     * Get detailed information about a single post
     */
    public function GetPostInfo(ContextInterface $ctx, PostRequest $request): PostResponse
    {
        try {
            $postId = $request->getPostId();

            $post = Post::with('author')->find($postId);

            if (!$post) {
                Log::warning("gRPC: Post not found", ['post_id' => $postId]);
                return $this->emptyPostResponse();
            }

            $response = new PostResponse();
            $response->setId($post->id);
            $response->setTitle($post->title ?? '');
            $response->setContent($post->content ?? '');
            $response->setCategory($post->category ?? '');
            $response->setAuthorId($post->author_id ?? 0);
            $response->setAuthorName($post->author?->name ?? '');
            $response->setCreatedAt($post->created_at?->toIso8601String() ?? '');
            $response->setTags($this->extractTags($post));
            $response->setLikeCount($post->likes_count ?? 0);
            $response->setCommentCount($post->comments_count ?? 0);
            $response->setViewCount($post->views_count ?? 0);
            $response->setIsPublished($post->is_published ?? false);

            Log::debug("gRPC: Retrieved post info", ['post_id' => $postId]);

            return $response;

        } catch (\Exception $e) {
            Log::error("gRPC: Error fetching post info", [
                'post_id' => $request->getPostId(),
                'error' => $e->getMessage(),
            ]);
            return $this->emptyPostResponse();
        }
    }

    /**
     * Get details for multiple posts in batch
     */
    public function GetBatchPostInfo(ContextInterface $ctx, BatchPostRequest $request): BatchPostResponse
    {
        try {
            $postIds = iterator_to_array($request->getPostIds());

            $posts = Post::with('author')
                ->whereIn('id', $postIds)
                ->get();

            $response = new BatchPostResponse();
            $postResponses = [];

            foreach ($posts as $post) {
                $postResponse = new PostResponse();
                $postResponse->setId($post->id);
                $postResponse->setTitle($post->title ?? '');
                $postResponse->setContent($post->content ?? '');
                $postResponse->setCategory($post->category ?? '');
                $postResponse->setAuthorId($post->author_id ?? 0);
                $postResponse->setAuthorName($post->author?->name ?? '');
                $postResponse->setCreatedAt($post->created_at?->toIso8601String() ?? '');
                $postResponse->setTags($this->extractTags($post));
                $postResponse->setLikeCount($post->likes_count ?? 0);
                $postResponse->setCommentCount($post->comments_count ?? 0);
                $postResponse->setViewCount($post->views_count ?? 0);
                $postResponse->setIsPublished($post->is_published ?? false);

                $postResponses[] = $postResponse;
            }

            $response->setPosts($postResponses);

            Log::debug("gRPC: Retrieved batch post info", [
                'requested' => count($postIds),
                'found' => count($postResponses),
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::error("gRPC: Error fetching batch post info", [
                'error' => $e->getMessage(),
            ]);
            return new BatchPostResponse();
        }
    }

    /**
     * Get user profile for personalization
     */
    public function GetUserProfile(ContextInterface $ctx, UserProfileRequest $request): UserProfileResponse
    {
        try {
            $userId = $request->getUserId();

            $user = User::with(['interests', 'followedCategories', 'following'])
                ->find($userId);

            if (!$user) {
                Log::warning("gRPC: User not found", ['user_id' => $userId]);
                return $this->emptyUserProfileResponse();
            }

            $response = new UserProfileResponse();
            $response->setId($user->id);
            $response->setName($user->name ?? '');
            $response->setEmail($user->email ?? '');
            $response->setInterests($this->extractInterests($user));
            $response->setFollowedCategories($this->extractFollowedCategories($user));
            $response->setFollowedUserIds($this->extractFollowedUserIds($user));
            $response->setPreferredLanguage($user->preferred_language ?? 'en');
            $response->setTimezone($user->timezone ?? 'UTC');

            Log::debug("gRPC: Retrieved user profile", ['user_id' => $userId]);

            return $response;

        } catch (\Exception $e) {
            Log::error("gRPC: Error fetching user profile", [
                'user_id' => $request->getUserId(),
                'error' => $e->getMessage(),
            ]);
            return $this->emptyUserProfileResponse();
        }
    }

    /**
     * Extract tags from post
     */
    private function extractTags(Post $post): array
    {
        if (method_exists($post, 'tags') && $post->tags) {
            return $post->tags->pluck('name')->toArray();
        }

        if (isset($post->tags) && is_array($post->tags)) {
            return $post->tags;
        }

        return [];
    }

    /**
     * Extract user interests
     */
    private function extractInterests($user): array
    {
        if (method_exists($user, 'interests') && $user->interests) {
            return $user->interests->pluck('name')->toArray();
        }

        if (isset($user->interests) && is_array($user->interests)) {
            return $user->interests;
        }

        return [];
    }

    /**
     * Extract followed categories
     */
    private function extractFollowedCategories($user): array
    {
        if (method_exists($user, 'followedCategories') && $user->followedCategories) {
            return $user->followedCategories->pluck('name')->toArray();
        }

        return [];
    }

    /**
     * Extract followed user IDs
     */
    private function extractFollowedUserIds($user): array
    {
        if (method_exists($user, 'following') && $user->following) {
            return $user->following->pluck('id')->toArray();
        }

        return [];
    }

    /**
     * Create empty post response
     */
    private function emptyPostResponse(): PostResponse
    {
        $response = new PostResponse();
        $response->setId(0);
        $response->setTitle('');
        $response->setContent('');
        $response->setCategory('');
        $response->setAuthorId(0);
        $response->setAuthorName('');
        $response->setCreatedAt('');
        $response->setTags([]);
        $response->setLikeCount(0);
        $response->setCommentCount(0);
        $response->setViewCount(0);
        $response->setIsPublished(false);
        return $response;
    }

    /**
     * Create empty user profile response
     */
    private function emptyUserProfileResponse(): UserProfileResponse
    {
        $response = new UserProfileResponse();
        $response->setId(0);
        $response->setName('');
        $response->setEmail('');
        $response->setInterests([]);
        $response->setFollowedCategories([]);
        $response->setFollowedUserIds([]);
        $response->setPreferredLanguage('en');
        $response->setTimezone('UTC');
        return $response;
    }
}
