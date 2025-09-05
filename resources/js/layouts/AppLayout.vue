<script setup lang="ts">
import { appConfig } from '@/config/app';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { useCallStore } from '@/stores/call';
import type { BreadcrumbItemType } from '@/types';
import { OutboundCallEvent } from '@/types/events';
import { usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { onMounted } from 'vue';
const callStore = useCallStore();
interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

const user = usePage().props.auth.user;

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

useEcho(`calls.${user.id}`, '.outbound.call', (e: OutboundCallEvent) => {
    callStore.handleOutboundCallInitiated(e);
    console.log('Outbound call event received', e.conference);
});

onMounted(async () => {
    await callStore.init(appConfig.callStoreRoutes);
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
