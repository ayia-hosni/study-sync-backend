<?php

namespace App\Http\Controllers;

use App\Application\DTOs\PlugNmeet\CreateRoomDTO;
use App\Application\DTOs\PlugNmeet\JoinDTO;
use App\Application\UseCases\PlugNmeet\CreateRoom as CreateRoomUC;
use App\Application\UseCases\PlugNmeet\GetJoinToken as GetJoinTokenUC;
use App\Services\PlugnmeetService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PlugNmeetController extends Controller
{
    public function create(PlugnmeetService $plug)
    {
        $data = [
            "room_id" => "room01",
            "name" => "Test Room",
            "max_participants" => 10,
            "recording" => false,
            "metadata" => [
                "room_title" => "My Test Room",
                "room_features" => [
                    "allow_screen_share" => true,
                    "allow_chat" => true,
                    "allow_file_upload" => true
                ]
            ]
        ];

        $res = $plug->createRoom($data);

        return response()->json($res);
    }

    public function join(Request $request, PlugnmeetService $plug)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
            'user_name'  => 'required|string',
            'role'       => 'nullable|string|in:moderator,participant',
        ]);

        $sessionId = $validated['session_id'];
        $roomId = 'session_' . $sessionId; // unique room per study session
        $userName = $validated['user_name'];
        $role = $validated['role'] ?? 'participant';

        // Try getting a join token
        $joinToken = $plug->getJoinToken($roomId, $userName, $role);

        // If room doesn't exist, create it first
        if (!$joinToken['status'] && str_contains(strtolower($joinToken['msg'] ?? ''), 'not active')) {
            $roomData = [
                "room_id" => $roomId,
                "name" => "Study Session {$sessionId}",
                "max_participants" => 20,
                "recording" => false,
                "metadata" => [
                    "room_title" => "Study Room {$sessionId}",
                    "room_features" => [
                        "allow_screen_share" => true,
                        "allow_chat" => true,
                        "allow_file_upload" => true,
                    ],
                ],
            ];

            $create = $plug->createRoom($roomData);

            if (!$create['status']) {
                return response()->json($create);
            }

            // Retry join token after creating room
            $joinToken = $plug->getJoinToken($roomId, $userName, $role);
        }

        if ($joinToken['status']) {
            return response()->json([
                'join_url' => config('plugnmeet.base_url') . '/?access_token=' . $joinToken['token'],
            ]);
        }

        return response()->json($joinToken);
    }
}
