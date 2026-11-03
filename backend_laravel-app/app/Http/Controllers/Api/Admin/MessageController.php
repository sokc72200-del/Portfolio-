<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $messages = Message::query()
            ->when($request->boolean('unread'), fn ($query) => $query->unread())
            ->latest()
            ->paginate(15);

        return response()->json([
            'messages' => $messages,
            'unread_count' => Message::unread()->count(),
        ]);
    }

    public function show(Message $message): JsonResponse
    {
        $message->markAsRead();

        return response()->json($message);
    }

    public function markAsRead(Message $message): JsonResponse
    {
        $message->markAsRead();

        return response()->json($message);
    }

    public function destroy(Message $message): JsonResponse
    {
        $message->delete();

        return response()->json(null, 204);
    }
}
