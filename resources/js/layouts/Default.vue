<template>
    <div class="wrapper">
        <Header />
        <main class="flex-grow-1 d-flex flex-column">
            <slot />
        </main>
        <Footer />
    </div>
</template>

<script setup>
import Header from "../components/Header.vue";
import Footer from "../components/Footer.vue";
</script>

<style scoped>
.wrapper {
    display: flex;
    min-height: 100vh;
    flex-direction: column;
    justify-content: space-between;
}
</style>
