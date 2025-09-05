<script setup lang="ts">
import { appConfig } from '@/config/app';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { useCallStore } from '@/stores/call';
import type { BreadcrumbItemType } from '@/types';
import { onMounted } from 'vue';
const callStore = useCallStore();
interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
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
