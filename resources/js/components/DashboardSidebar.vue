<template>
    <aside class="px-3 py-5" style="height: 100%;">
        <div class="text-center border-bottom pb-3 mb-3">
            <img :src="authStore.userData?.profile_picture" height="150px" width="150px"
                class="rounded-circle object-fit-cover border border-3 border-primary mb-3" />
            <h2 class="fs-4">{{ authStore.userData?.name }}</h2>
        </div>
        <DashboardMenu />
    </aside>
</template>

<script setup>
import { useAuthStore } from "@/stores/auth";
import DashboardMenu from "./DashboardMenu.vue";
const authStore = useAuthStore();
</script>
