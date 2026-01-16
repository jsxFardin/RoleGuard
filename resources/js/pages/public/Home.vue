<template>
    <PublicLayout>
        <Head title="Home - MBSC" />

     

        <!-- Statistics Section -->
        <section v-if="statistics.length > 0" class="py-16">
            <div class="container mx-auto px-4">
                <div class="grid gap-8 md:grid-cols-3">
                    <div
                        v-for="stat in statistics"
                        :key="stat.id"
                        class="text-center"
                    >
                        <div class="mb-2 text-4xl font-bold text-primary">{{ stat.value }}</div>
                        <div class="text-muted-foreground">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="bg-muted/50 py-16">
            <div class="container mx-auto px-4">
                <div class="mb-12 text-center">
                    <h2 class="mb-4 text-3xl font-bold">Our Top Services</h2>
                    <p class="text-muted-foreground">
                        Comprehensive business solutions tailored to your needs
                    </p>
                </div>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <ServiceCard
                        v-for="service in services"
                        :key="service.id"
                        :service="service"
                    />
                </div>
               
            </div>
        </section>

    </PublicLayout>
</template>

<script setup lang="ts">
import { Head} from '@inertiajs/vue3';
import PublicLayout from '@/layouts/public/PublicLayout.vue';

interface Props {
    services: Array<{
        id: number;
        slug: string;
        title: string;
        description: string | null;
        icon: string | null;
        children?: Array<{ id: number; title: string }>;
    }>;
    statistics: Array<{
        id: number;
        label: string;
        value: string;
    }>;
    testimonials: Array<{
        id: number;
        client_name: string;
        testimonial: string;
        rating: number;
    }>;
    recentPosts: Array<{
        id: number;
        slug: string;
        title: string;
        excerpt: string | null;
        featured_image: string | null;
        published_at: string;
    }>;
}

defineProps<Props>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>
