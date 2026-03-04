<?php

namespace App\Http\Controllers\Chat;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request, Conversation $conversation): JsonResponse
    {
        $this->ensureParticipant($request, $conversation);
        $authUserId = (int) $request->user()->id;

        $conversation->messages()
            ->where('user_id', '!=', $authUserId)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        $messages = $conversation->messages()
            ->with('user:id,name,email')
            ->latest('id')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'messages' => $messages->map(fn ($message) => [
                'id' => $message->id,
                'body' => $message->body,
                'created_at' => $message->created_at?->toISOString(),
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                    'email' => $message->user->email,
                ],
            ])->values(),
        ]);
    }

    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        $this->ensureParticipant($request, $conversation);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $conversation->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        $message->load('user:id,name,email');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'created_at' => $message->created_at?->toISOString(),
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                    'email' => $message->user->email,
                ],
            ],
        ], 201);
    }

    private function ensureParticipant(Request $request, Conversation $conversation): void
    {
        $isParticipant = $conversation->users()
            ->where('users.id', $request->user()->id)
            ->exists();

        abort_unless($isParticipant, 403);
    }
}
