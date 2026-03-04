<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConversationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ]);

        $authUser = $request->user();
        $otherUserId = (int) $validated['user_id'];

        if ($otherUserId === (int) $authUser->id) {
            return response()->json([
                'message' => 'You cannot start a conversation with yourself.',
            ], 422);
        }

        $conversation = Conversation::query()
            ->where('is_group', false)
            ->whereHas('users', fn ($query) => $query->where('users.id', $authUser->id))
            ->whereHas('users', fn ($query) => $query->where('users.id', $otherUserId))
            ->withCount('users')
            ->having('users_count', 2)
            ->first();

        if (! $conversation) {
            $conversation = Conversation::query()->create([
                'is_group' => false,
            ]);

            $conversation->users()->sync([$authUser->id, $otherUserId]);
        }

        $conversation->load('users:id,name,email');
        $otherUser = $conversation->users->firstWhere('id', '!=', $authUser->id);

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'title' => $otherUser?->name ?: 'Conversation',
                'is_group' => $conversation->is_group,
                'last_message_at' => $conversation->last_message_at?->toISOString(),
                'unread_count' => 0,
                'users' => $conversation->users->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ])->values(),
                'latest_message' => null,
            ],
        ]);
    }
}
