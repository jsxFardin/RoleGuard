<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $authUser = $request->user();

        $conversations = $authUser
            ->conversations()
            ->with([
                'users:id,name,email',
                'latestMessage.user:id,name',
            ])
            ->withCount([
                'messages as unread_count' => fn ($query) => $query
                    ->whereNull('read_at')
                    ->where('user_id', '!=', $authUser->id),
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        $users = User::query()
            ->where('id', '!=', $authUser->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('chat/Index', [
            'users' => $users,
            'conversations' => $conversations->map(function ($conversation) use ($authUser) {
                $otherUser = $conversation->users->firstWhere('id', '!=', $authUser->id);
                $title = $conversation->is_group
                    ? ($conversation->name ?: 'Group Chat')
                    : ($otherUser?->name ?: 'Conversation');

                return [
                    'id' => $conversation->id,
                    'title' => $title,
                    'is_group' => $conversation->is_group,
                    'last_message_at' => $conversation->last_message_at?->toISOString(),
                    'unread_count' => (int) $conversation->unread_count,
                    'users' => $conversation->users->map(fn ($user) => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ])->values(),
                    'latest_message' => $conversation->latestMessage ? [
                        'id' => $conversation->latestMessage->id,
                        'body' => $conversation->latestMessage->body,
                        'created_at' => $conversation->latestMessage->created_at?->toISOString(),
                        'user' => $conversation->latestMessage->user ? [
                            'id' => $conversation->latestMessage->user->id,
                            'name' => $conversation->latestMessage->user->name,
                        ] : null,
                    ] : null,
                ];
            })->values(),
        ]);
    }
}
