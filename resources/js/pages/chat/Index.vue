<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface ChatMessage {
    id: number;
    body: string;
    created_at: string;
    user: User;
}

interface Conversation {
    id: number;
    title: string;
    is_group: boolean;
    last_message_at: string | null;
    unread_count: number;
    users: User[];
    latest_message: ChatMessage | null;
}

interface Props {
    users: User[];
    conversations: Conversation[];
}

interface MessageSentPayload {
    message: ChatMessage;
}

interface MessageRenderItem {
    type: 'separator' | 'message';
    key: string;
    label?: string;
    message?: ChatMessage;
}

interface ConversationPaginationState {
    has_more: boolean;
    oldest_message_id: number | null;
    is_loading_older: boolean;
}

const props = defineProps<Props>();
const page = usePage<any>();
const authUserId = Number(page.props.auth?.user?.id ?? 0);

const conversations = ref<Conversation[]>([...props.conversations]);
const activeConversationId = ref<number | null>(props.conversations[0]?.id ?? null);
const messagesByConversation = ref<Record<number, ChatMessage[]>>({});
const sidebarSearchQuery = ref('');
const messageBody = ref('');
const isSending = ref(false);
const isLoadingMessages = ref(false);
const subscribedConversationIds = ref<number[]>([]);
const messagesContainerRef = ref<HTMLElement | null>(null);
const topSentinelRef = ref<HTMLElement | null>(null);
const topSentinelObserver = ref<IntersectionObserver | null>(null);
const unseenIncomingCount = ref(0);
const paginationByConversation = ref<Record<number, ConversationPaginationState>>({});

const activeConversation = computed(() =>
    conversations.value.find((conversation) => conversation.id === activeConversationId.value) ?? null,
);

const activeMessages = computed(() => {
    if (!activeConversationId.value) return [];

    return messagesByConversation.value[activeConversationId.value] ?? [];
});

const filteredUsers = computed(() => {
    const query = sidebarSearchQuery.value.trim().toLowerCase();
    if (!query) {
        return props.users;
    }

    return props.users.filter((user) => {
        return user.name.toLowerCase().includes(query) || user.email.toLowerCase().includes(query);
    });
});

const filteredConversations = computed(() => {
    const query = sidebarSearchQuery.value.trim().toLowerCase();
    if (!query) {
        return conversations.value;
    }

    return conversations.value.filter((conversation) => {
        const text = [
            conversation.title,
            conversation.latest_message?.body ?? '',
            ...conversation.users.map((user) => user.name),
        ]
            .join(' ')
            .toLowerCase();

        return text.includes(query);
    });
});

const activePagination = computed(() => {
    if (!activeConversationId.value) {
        return null;
    }

    return paginationByConversation.value[activeConversationId.value] ?? null;
});

const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
const xsrfToken = decodeURIComponent(
    document.cookie
        .split('; ')
        .find((cookie) => cookie.startsWith('XSRF-TOKEN='))
        ?.split('=')[1] ?? '',
);

const headers = () => ({
    Accept: 'application/json',
    'Content-Type': 'application/json',
    ...(token ? { 'X-CSRF-TOKEN': token } : {}),
    ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
    ...(window.Echo?.socketId() ? { 'X-Socket-Id': window.Echo.socketId() } : {}),
    'X-Requested-With': 'XMLHttpRequest',
});

const formatTimestamp = (dateString: string | null): string => {
    if (!dateString) return '';

    return new Date(dateString).toLocaleString();
};

const formatConversationTime = (dateString: string | null): string => {
    if (!dateString) return '';

    return new Date(dateString).toLocaleTimeString([], {
        hour: 'numeric',
        minute: '2-digit',
    });
};

const isOwnMessage = (message: ChatMessage): boolean => {
    return message.user.id === authUserId;
};

const messageDateKey = (dateString: string): string => {
    const date = new Date(dateString);

    return `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`;
};

const formatMessageDateLabel = (dateString: string): string => {
    const target = new Date(dateString);
    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);

    const isToday = target.toDateString() === today.toDateString();
    if (isToday) {
        return 'Today';
    }

    const isYesterday = target.toDateString() === yesterday.toDateString();
    if (isYesterday) {
        return 'Yesterday';
    }

    return target.toLocaleDateString([], {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const renderedMessages = computed<MessageRenderItem[]>(() => {
    const items: MessageRenderItem[] = [];
    let lastDateKey = '';

    for (const message of activeMessages.value) {
        const currentDateKey = messageDateKey(message.created_at);

        if (currentDateKey !== lastDateKey) {
            items.push({
                type: 'separator',
                key: `sep-${currentDateKey}`,
                label: formatMessageDateLabel(message.created_at),
            });

            lastDateKey = currentDateKey;
        }

        items.push({
            type: 'message',
            key: `msg-${message.id}`,
            message,
        });
    }

    return items;
});

const getPaginationState = (conversationId: number): ConversationPaginationState => {
    if (!paginationByConversation.value[conversationId]) {
        paginationByConversation.value[conversationId] = {
            has_more: false,
            oldest_message_id: null,
            is_loading_older: false,
        };
    }

    return paginationByConversation.value[conversationId];
};

const isNearBottom = (): boolean => {
    if (!messagesContainerRef.value) {
        return true;
    }

    const { scrollTop, clientHeight, scrollHeight } = messagesContainerRef.value;

    return scrollHeight - (scrollTop + clientHeight) < 100;
};

const scrollMessagesToBottom = async () => {
    await nextTick();

    if (!messagesContainerRef.value) {
        return;
    }

    messagesContainerRef.value.scrollTop = messagesContainerRef.value.scrollHeight;
    unseenIncomingCount.value = 0;
};

const sortConversations = () => {
    conversations.value.sort((a, b) => {
        const aTime = a.last_message_at ?? a.latest_message?.created_at ?? '';
        const bTime = b.last_message_at ?? b.latest_message?.created_at ?? '';

        return bTime.localeCompare(aTime);
    });
};

const loadMessages = async (conversationId: number) => {
    isLoadingMessages.value = true;

    try {
        const response = await fetch(`/chat/conversations/${conversationId}/messages`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Failed to fetch messages.');
        }

        const data = await response.json();
        messagesByConversation.value[conversationId] = data.messages ?? [];
        paginationByConversation.value[conversationId] = {
            has_more: Boolean(data.has_more),
            oldest_message_id: data.oldest_message_id ?? null,
            is_loading_older: false,
        };
        unseenIncomingCount.value = 0;

        const conversation = conversations.value.find((item) => item.id === conversationId);
        if (conversation) {
            conversation.unread_count = 0;
        }
    } finally {
        isLoadingMessages.value = false;
    }
};

const loadOlderMessages = async () => {
    const conversationId = activeConversationId.value;

    if (!conversationId) {
        return;
    }

    const pagination = getPaginationState(conversationId);
    if (!pagination.has_more || pagination.is_loading_older || !pagination.oldest_message_id) {
        return;
    }

    pagination.is_loading_older = true;
    const previousScrollHeight = messagesContainerRef.value?.scrollHeight ?? 0;
    const previousScrollTop = messagesContainerRef.value?.scrollTop ?? 0;

    try {
        const response = await fetch(
            `/chat/conversations/${conversationId}/messages?before_id=${pagination.oldest_message_id}`,
            {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            },
        );

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        const olderMessages: ChatMessage[] = data.messages ?? [];
        const existing = messagesByConversation.value[conversationId] ?? [];
        messagesByConversation.value[conversationId] = [...olderMessages, ...existing];

        pagination.has_more = Boolean(data.has_more);
        pagination.oldest_message_id = data.oldest_message_id ?? null;

        await nextTick();

        if (messagesContainerRef.value) {
            const newScrollHeight = messagesContainerRef.value.scrollHeight;
            messagesContainerRef.value.scrollTop = previousScrollTop + (newScrollHeight - previousScrollHeight);
        }
    } finally {
        pagination.is_loading_older = false;
    }
};

const setupTopSentinelObserver = async () => {
    await nextTick();

    if (topSentinelObserver.value) {
        topSentinelObserver.value.disconnect();
        topSentinelObserver.value = null;
    }

    if (!messagesContainerRef.value || !topSentinelRef.value) {
        return;
    }

    topSentinelObserver.value = new IntersectionObserver(
        (entries) => {
            const entry = entries[0];
            if (entry?.isIntersecting) {
                void loadOlderMessages();
            }
        },
        {
            root: messagesContainerRef.value,
            rootMargin: '120px 0px 0px 0px',
            threshold: 0.01,
        },
    );

    topSentinelObserver.value.observe(topSentinelRef.value);
};

const updateConversationPreview = (conversationId: number, message: ChatMessage) => {
    const conversation = conversations.value.find((item) => item.id === conversationId);

    if (!conversation) {
        return;
    }

    conversation.latest_message = message;
    conversation.last_message_at = message.created_at;
    sortConversations();
};

const subscribeToConversation = (conversationId: number) => {
    if (!window.Echo || subscribedConversationIds.value.includes(conversationId)) {
        return;
    }

    const channelName = `conversation.${conversationId}`;
    subscribedConversationIds.value.push(conversationId);

    window.Echo.private(channelName).listen('.message.sent', (payload: MessageSentPayload) => {
        const incomingMessage = payload.message;
        const conversationMessages = messagesByConversation.value[conversationId] ?? [];
        const alreadyExists = conversationMessages.some((message) => message.id === incomingMessage.id);

        if (!alreadyExists) {
            messagesByConversation.value[conversationId] = [...conversationMessages, incomingMessage];
            updateConversationPreview(conversationId, incomingMessage);

            const conversation = conversations.value.find((item) => item.id === conversationId);
            if (
                conversation &&
                incomingMessage.user.id !== authUserId &&
                conversationId !== activeConversationId.value
            ) {
                conversation.unread_count += 1;
            }

            if (conversationId === activeConversationId.value) {
                if (isNearBottom() || incomingMessage.user.id === authUserId) {
                    void scrollMessagesToBottom();
                } else if (incomingMessage.user.id !== authUserId) {
                    unseenIncomingCount.value += 1;
                }
            }
        }
    });
};

const selectConversation = async (conversationId: number) => {
    activeConversationId.value = conversationId;
    await loadMessages(conversationId);
    await scrollMessagesToBottom();
};

const startConversation = async (userId: number) => {
    const response = await fetch('/chat/conversations', {
        method: 'POST',
        headers: headers(),
        credentials: 'same-origin',
        body: JSON.stringify({ user_id: userId }),
    });

    if (!response.ok) {
        return;
    }

    const data = await response.json();
    const conversation: Conversation | undefined = data.conversation;

    if (!conversation) {
        return;
    }

    const existingIndex = conversations.value.findIndex((item) => item.id === conversation.id);

    if (existingIndex === -1) {
        conversations.value.unshift(conversation);
    } else {
        conversations.value[existingIndex] = conversation;
    }

    await selectConversation(conversation.id);
};

const sendMessage = async () => {
    const conversationId = activeConversationId.value;
    const body = messageBody.value.trim();

    if (!conversationId || !body || isSending.value) {
        return;
    }

    isSending.value = true;

    try {
        const response = await fetch(`/chat/conversations/${conversationId}/messages`, {
            method: 'POST',
            headers: headers(),
            credentials: 'same-origin',
            body: JSON.stringify({ body }),
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        const message: ChatMessage | undefined = data.message;

        if (!message) {
            return;
        }

        const conversationMessages = messagesByConversation.value[conversationId] ?? [];
        messagesByConversation.value[conversationId] = [...conversationMessages, message];
        updateConversationPreview(conversationId, message);
        messageBody.value = '';
        await scrollMessagesToBottom();
    } finally {
        isSending.value = false;
    }
};

const handleComposerKeydown = (event: KeyboardEvent) => {
    if (event.key !== 'Enter' || event.shiftKey) {
        return;
    }

    event.preventDefault();
    void sendMessage();
};

watch(
    () => conversations.value.map((conversation) => conversation.id).join(','),
    () => {
        for (const conversation of conversations.value) {
            subscribeToConversation(conversation.id);
        }
    },
    { immediate: true },
);

watch(
    () => activeConversationId.value,
    () => {
        void setupTopSentinelObserver();
    },
    { immediate: true },
);

const handleMessagesScroll = () => {
    if (!messagesContainerRef.value) {
        return;
    }

    if (messagesContainerRef.value.scrollTop < 80) {
        void loadOlderMessages();
    }

    if (isNearBottom()) {
        unseenIncomingCount.value = 0;
    }
};

watch(
    () => activeConversationId.value,
    (conversationId) => {
        if (conversationId === null) {
            return;
        }

        const conversation = conversations.value.find((item) => item.id === conversationId);
        if (conversation) {
            conversation.unread_count = 0;
        }
    },
);

onMounted(async () => {
    if (activeConversationId.value) {
        await loadMessages(activeConversationId.value);
        await scrollMessagesToBottom();
    }

    await setupTopSentinelObserver();
});

onUnmounted(() => {
    if (topSentinelObserver.value) {
        topSentinelObserver.value.disconnect();
        topSentinelObserver.value = null;
    }

    if (window.Echo) {
        for (const conversationId of subscribedConversationIds.value) {
            window.Echo.leave(`conversation.${conversationId}`);
        }
    }
});
</script>

<template>
    <AppLayout>
        <Head title="Chat" />

        <div class="h-[calc(100vh-9rem)] min-h-[620px] overflow-hidden rounded-xl border bg-card">
            <div class="grid h-full gap-0 md:grid-cols-[320px_1fr]">
                <aside class="flex h-full min-h-0 flex-col border-r bg-muted/20">
                    <div class="border-b px-4 py-3">
                        <h2 class="text-lg font-semibold">Users</h2>
                    </div>

                    <div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-4">
                        <div>
                            <input
                                v-model="sidebarSearchQuery"
                                type="text"
                                class="h-10 w-full rounded-lg border bg-background px-3 text-sm outline-none ring-primary/20 focus:ring-2"
                                placeholder="Search users or chats..."
                            >
                        </div>

                        <div class="space-y-2">
                            <button
                                v-for="user in filteredUsers"
                                :key="user.id"
                                class="w-full rounded-lg border bg-background px-3 py-2 text-left transition-colors hover:bg-muted"
                                @click="startConversation(user.id)"
                            >
                                <div class="font-medium">{{ user.name }}</div>
                                <div class="text-xs text-muted-foreground">{{ user.email }}</div>
                            </button>
                        </div>

                        <div class="space-y-2">
                            <h3 class="px-1 text-lg font-semibold">Conversations</h3>
                            <button
                                v-for="conversation in filteredConversations"
                                :key="conversation.id"
                                class="w-full rounded-lg border bg-background px-3 py-2 text-left transition-colors hover:bg-muted"
                                :class="{ 'border-primary bg-primary/5': conversation.id === activeConversationId }"
                                @click="selectConversation(conversation.id)"
                            >
                                <div class="mb-1 flex items-start justify-between gap-2">
                                    <div class="truncate pr-2 font-medium">{{ conversation.title }}</div>
                                    <div class="flex shrink-0 items-center gap-1.5">
                                        <span
                                            v-if="conversation.latest_message?.created_at"
                                            class="text-[11px] text-muted-foreground"
                                        >
                                            {{ formatConversationTime(conversation.latest_message.created_at) }}
                                        </span>
                                        <span
                                            v-if="conversation.unread_count > 0"
                                            class="inline-flex min-w-5 items-center justify-center rounded-full bg-primary px-1.5 py-0.5 text-[10px] font-semibold text-primary-foreground"
                                        >
                                            {{ conversation.unread_count }}
                                        </span>
                                    </div>
                                </div>
                                <div class="truncate text-xs text-muted-foreground">
                                    {{ conversation.latest_message?.body ?? 'No messages yet' }}
                                </div>
                            </button>
                        </div>
                    </div>
                </aside>

                <section class="flex h-full min-h-0 flex-col">
                    <div v-if="activeConversation" class="flex h-full min-h-0 flex-col">
                        <div class="border-b px-5 py-4">
                            <h2 class="text-lg font-semibold">{{ activeConversation.title }}</h2>
                            <p class="text-xs text-muted-foreground">
                                {{ activeConversation.users.length }} participant{{ activeConversation.users.length > 1 ? 's' : '' }}
                            </p>
                        </div>

                        <div
                            ref="messagesContainerRef"
                            class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-muted/10 p-4"
                            @scroll="handleMessagesScroll"
                        >
                            <div ref="topSentinelRef" class="h-px w-full" />

                            <div v-if="activePagination?.is_loading_older" class="flex justify-center">
                                <span class="rounded-md border bg-background px-3 py-1 text-xs text-muted-foreground">
                                    Loading older messages...
                                </span>
                            </div>

                            <p v-if="isLoadingMessages" class="text-sm text-muted-foreground">Loading messages...</p>

                            <p v-else-if="activeMessages.length === 0" class="text-sm text-muted-foreground">
                                No messages yet. Start the conversation.
                            </p>

                            <div
                                v-for="item in renderedMessages"
                                :key="item.key"
                            >
                                <div
                                    v-if="item.type === 'separator'"
                                    class="my-2 flex items-center gap-3 text-[11px] text-muted-foreground"
                                >
                                    <div class="h-px flex-1 bg-border" />
                                    <span class="rounded-full bg-background px-2 py-0.5">{{ item.label }}</span>
                                    <div class="h-px flex-1 bg-border" />
                                </div>

                                <div
                                    v-else-if="item.message"
                                    class="flex"
                                    :class="isOwnMessage(item.message) ? 'justify-end' : 'justify-start'"
                                >
                                    <div
                                        class="max-w-[78%] rounded-2xl border px-3 py-2 shadow-sm"
                                        :class="isOwnMessage(item.message) ? 'border-primary/20 bg-primary/10' : 'bg-background'"
                                    >
                                        <div class="mb-1 flex items-center justify-between gap-3">
                                            <span class="text-xs font-semibold" :class="isOwnMessage(item.message) ? 'text-primary' : 'text-foreground'">
                                                {{ item.message.user.name }}
                                            </span>
                                            <span class="text-[11px] text-muted-foreground">
                                                {{ formatTimestamp(item.message.created_at) }}
                                            </span>
                                        </div>
                                        <p class="whitespace-pre-wrap break-words text-sm">{{ item.message.body }}</p>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="activeMessages.length > 0 && activePagination && !activePagination.has_more && !activePagination.is_loading_older"
                                class="mt-2 flex justify-center"
                            >
                                <span class="text-xs text-muted-foreground">No more messages</span>
                            </div>
                        </div>

                        <div class="border-t bg-background p-4">
                            <div v-if="unseenIncomingCount > 0" class="mb-2 flex justify-center">
                                <button
                                    type="button"
                                    class="rounded-full bg-primary px-3 py-1 text-xs font-medium text-primary-foreground shadow"
                                    @click="scrollMessagesToBottom"
                                >
                                    {{ unseenIncomingCount }} new message{{ unseenIncomingCount > 1 ? 's' : '' }}
                                </button>
                            </div>
                            <form class="flex items-end gap-2" @submit.prevent="sendMessage">
                                <textarea
                                    v-model="messageBody"
                                    rows="1"
                                    class="max-h-32 min-h-10 w-full resize-y rounded-lg border bg-background px-3 py-2 text-sm outline-none ring-primary/20 focus:ring-2"
                                    placeholder="Type your message..."
                                    @keydown="handleComposerKeydown"
                                />
                                <button
                                    type="submit"
                                    class="h-10 rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 disabled:opacity-70"
                                    :disabled="isSending"
                                >
                                    {{ isSending ? 'Sending...' : 'Send' }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <div v-else class="flex h-full items-center justify-center text-sm text-muted-foreground">
                        Select a user to start a conversation.
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
