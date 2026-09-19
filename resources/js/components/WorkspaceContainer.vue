<template>
    <div :class="['workspace-container', containerClass]">
        <slot></slot>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'standard', // 'compact', 'standard', 'wide'
        validator: (value) => ['compact', 'standard', 'wide'].includes(value)
    }
});

const containerClass = computed(() => {
    switch (props.type) {
        case 'compact':
            return 'container-compact mx-auto';
        case 'standard':
            return 'container-standard mx-auto';
        case 'wide':
            return 'container-wide w-100';
        default:
            return 'container-standard mx-auto';
    }
});
</script>

<style scoped>
.workspace-container {
    width: 100%;
}
.container-compact {
    max-width: 800px;
}
.container-standard {
    max-width: 1280px;
}
.container-wide {
    max-width: 100%;
}
</style>
