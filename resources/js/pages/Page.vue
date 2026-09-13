<template>
    <section class="container py-5">

        <article v-if="page">
            <h1 class="text-center mb-5 text-uppercase">{{ page?.title }}</h1>
            <div v-html="page?.content"></div>
        </article>
        <div v-else class="text-center">
            <div class="skeleton mb-3" style="height: 50px; width: 200px; margin: 0 auto;"></div>
            <div class="skeleton" style="height: 20px; width: 150px; margin: 0 auto;"></div>
        </div>
    </section>
</template>

<style scoped>
.skeleton {
    background-color: #e0e0e0;
    border-radius: 4px;
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% {
        background-position: -200px 0;
    }

    100% {
        background-position: 200px 0;
    }
}
</style>

<script setup>
import { useRoute } from "vue-router";
import { useMasterStore } from "@/stores/master";
import { onMounted, ref, watchEffect } from "vue";

const route = useRoute();
const masterStore = useMasterStore();
const page = ref(null);

watchEffect(() => {
    const slug = route.params.slug;
    const pages = masterStore.masterData?.pages;

    if (slug && Array.isArray(pages)) {
        page.value = pages.find((p) => p.slug === slug);
        window.scrollTo(0, 0);
    }
});
</script>
