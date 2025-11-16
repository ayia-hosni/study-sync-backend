<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PlugnmeetService
{
    private $apiKey;
    private $secret;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('plugnmeet.api_key', 'plugnmeet');
        $this->secret = config('plugnmeet.secret', 'zumyyYWqv7KR2kUqvYdq4z4sXg7XTBD2ljT6');
        $this->baseUrl = config('plugnmeet.base_url', 'http://localhost:8085');
    }

    public function createRoom(array $data)
    {
        $body = json_encode($data);

        // Generate HMAC-SHA256 signature
        $signature = hash_hmac('sha256', $body, $this->secret);

        // Make request
        $response = Http::withHeaders([
            'Content-Type'   => 'application/json',
            'API-KEY'        => $this->apiKey,
            'HASH-SIGNATURE' => $signature,
        ])->post($this->baseUrl . '/auth/room/create', $data);

        return $response->json();
    }


    public function getJoinToken(string $roomId, string $userName, string $role = 'participant'): array
    {
        $isAdmin = $role === 'moderator'; // moderators have admin rights

        $body = [
            "room_id" => $roomId,
            "user_info" => [
                "name" => $userName,
                "user_id" => uniqid("user_"),
                "is_admin" => $isAdmin,
                "is_hidden" => false,
                "user_metadata" => [
                    "preferred_lang" => "en-US"
                ]
            ]
        ];

        $json = json_encode($body);
        $signature = hash_hmac('sha256', $json, config('plugnmeet.secret'));

        $response = Http::withHeaders([
            'Content-Type'   => 'application/json',
            'API-KEY'        => config('plugnmeet.api_key'),
            'HASH-SIGNATURE' => $signature,
        ])->post(config('plugnmeet.base_url') . '/auth/room/getJoinToken', $body);

        return $response->json();
    }
}
