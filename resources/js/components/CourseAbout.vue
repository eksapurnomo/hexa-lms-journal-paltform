<template>
    <div
        v-for="(description, index) in descriptions"
        :key="index"
        class="p-3 bg-white rounded-3 mb-3"
    >
        <h5 class="fw-bold">{{ description.heading }}</h5>
        <article v-html="description.body"></article>
    </div>
</template>

<script setup>
import { defineProps } from "vue";

const props = defineProps({
    descriptions: Array,
});
</script>
