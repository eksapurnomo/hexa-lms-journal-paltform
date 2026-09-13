<template>
    <div class="wrapper">
        <div class="main flex-grow-1 d-flex flex-column">
            <slot />
        </div>
    </div>
</template>

<style scoped>
.wrapper {
    display: flex;
    min-height: 100vh;
    flex-direction: column;
    justify-content: space-between;
}
</style>
