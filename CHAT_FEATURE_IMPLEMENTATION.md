# Real-Time Chat Implementation (Laravel + Vue + Reverb)

This project now includes a one-to-one real-time chat implementation.

## What Was Implemented

1. **Database**
   - Added `conversations` table:
     - `is_group`, `name`, `last_message_at`, timestamps
   - Added `messages` table:
     - `conversation_id`, `user_id`, `body`, `read_at`, timestamps
   - Added `conversation_user` pivot table:
     - `conversation_id`, `user_id`, timestamps, unique pair

2. **Models and Relations**
   - `App\Models\Conversation`
     - `users()` many-to-many
     - `messages()` one-to-many
     - `latestMessage()` latest message relation
   - `App\Models\Message`
     - `conversation()` belongs-to
     - `user()` belongs-to
   - `App\Models\User`
     - `conversations()` many-to-many
     - `messages()` one-to-many

3. **Chat Backend (HTTP + JSON)**
   - `ChatController` (Inertia page data)
   - `ConversationController@store` (create/find DM conversation)
   - `MessageController@index` (load latest messages)
   - `MessageController@store` (save message + broadcast)

4. **Broadcasting + Reverb**
   - Installed Reverb/Pusher PHP dependencies and Echo frontend deps.
   - Added broadcasting config and channels route loading.
   - Added channel auth:
     - `conversation.{conversationId}` only for participants.
   - Added event:
     - `App\Events\MessageSent` broadcasting on private conversation channel.

5. **Frontend**
   - Added `resources/js/pages/chat/Index.vue`
     - user list
     - conversation list
     - message list
     - send message form
     - Echo listener for `.message.sent`
   - Added chat link in sidebar.
   - Added i18n keys for nav chat label.
   - Added CSRF meta tag in Blade and Echo setup in `resources/js/app.ts`.

6. **Environment**
   - Updated `.env.example` with Reverb keys/host/port and Vite Reverb variables.

## Files Added/Updated

- Added:
  - `app/Events/MessageSent.php`
  - `app/Http/Controllers/Chat/ChatController.php`
  - `app/Http/Controllers/Chat/ConversationController.php`
  - `app/Http/Controllers/Chat/MessageController.php`
  - `app/Models/Conversation.php`
  - `app/Models/Message.php`
  - `config/broadcasting.php`
  - `config/reverb.php`
  - `routes/channels.php`
  - `resources/js/pages/chat/Index.vue`
  - `database/migrations/2026_03_02_211643_create_conversations_table.php`
  - `database/migrations/2026_03_02_211643_create_messages_table.php`
  - `database/migrations/2026_03_02_211644_create_conversation_user_table.php`

- Updated:
  - `app/Models/User.php`
  - `bootstrap/app.php`
  - `routes/public.php`
  - `resources/js/app.ts`
  - `resources/js/types/globals.d.ts`
  - `resources/js/components/AppSidebar.vue`
  - `resources/js/i18n/locales/en.json`
  - `resources/js/i18n/locales/bn.json`
  - `resources/views/app.blade.php`
  - `.env.example`
  - `composer.json`, `composer.lock`
  - `package.json`, `package-lock.json`

## How To Run

1. Set `.env` values (Reverb section) if needed:
   - `BROADCAST_CONNECTION=reverb`
   - `REVERB_APP_ID=...`
   - `REVERB_APP_KEY=...`
   - `REVERB_APP_SECRET=...`
   - `REVERB_HOST=localhost`
   - `REVERB_PORT=8080`
   - `REVERB_SCHEME=http`
   - `VITE_REVERB_*` values

2. Run migration:
   - `php artisan migrate`

3. Run app + frontend:
   - `php artisan serve`
   - `npm run dev`

4. Run Reverb server:
   - `php artisan reverb:start`

5. Open two different user sessions and go to:
   - `/chat`

## Current Scope

- Implemented: **direct (1-to-1) chat** with real-time delivery.
- Not yet implemented:
  - group chat creation/management UI
  - message read receipts
  - typing indicators
  - attachments/files
  - pagination/infinite scroll for long history

## Suggested Next Improvements

1. Add read receipts (`read_at` update endpoint + UI badges).
2. Add typing events using Whisper/presence channels.
3. Add message pagination (older messages load on scroll-up).
4. Add permissions/rules if chat should be limited by role.
