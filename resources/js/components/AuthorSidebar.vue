<template>
    <div class="px-3 py-4" style="height: 100%;">
        <div class="text-center border-bottom pb-3 mb-3">
            <img :src="authStore.userData?.profile_picture" height="150px" width="150px"
                class="rounded-circle object-fit-cover border border-3 border-primary mb-3" />
            <h2 class="fs-4">{{ authStore.userData?.name }}</h2>
            <span class="badge bg-secondary text-uppercase mt-2">Author Workspace</span>
        </div>
        <AuthorMenu />
    </div>
</template>

<script setup>
import { useAuthStore } from "@/stores/auth";
import AuthorMenu from "./AuthorMenu.vue";
const authStore = useAuthStore();
</script>
