<template>
    <div class="min-h-screen bg-background">
        <header class="border-b border-border">
            <nav class="container mx-auto flex items-center justify-between px-4 py-4">
                <Link :href="home().url" class="text-xl font-bold">
                    MBSC
                </Link>
            </nav>
        </header>

        <main>
            <slot />
        </main>

        <footer class="mt-auto border-t border-border bg-muted/50">
            <div class="container mx-auto px-4 py-8">
                <div class="mt-8 border-t border-border pt-8 text-center text-sm text-muted-foreground">
                    <p>&copy; {{ new Date().getFullYear() }} MODERN BUSINESS SOLUTION & CONSULTANCY. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { home } from '@/routes';
</script>
