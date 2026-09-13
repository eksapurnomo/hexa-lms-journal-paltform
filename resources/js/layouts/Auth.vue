<template>
    <DashboardHeader />
    <div class="wrapper">
        <div class="px-3 flex-grow-1 d-flex flex-column">
            <slot />
        </div>
    </div>
    <DashboardFooter />
</template>

<script setup>
import DashboardFooter from "../components/DashboardFooter.vue";
import DashboardHeader from "../components/DashboardHeader.vue";
</script>

<style scoped>
.wrapper {
    display: flex;
    min-height: 100vh;
    flex-direction: column;
    justify-content: space-between;
}
</style>
