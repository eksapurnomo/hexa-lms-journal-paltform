<template>
    <div :class="colorClass">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
            d="M22.0001 15V9.00001C22.0001 4 20 2 15 2H9.00001C4 2 2 4 2 9.00001V15C2 20 4 22 9.00001 22H15C20 22 22.0001 20 22.0001 15Z"
            stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M2.51953 7.10938H21.4795" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M8.51953 2.10938V6.96937" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M15.4805 2.10938V6.51938" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />
        <path opacity="0.4"
            d="M9.75 14.4501V13.2501C9.75 11.7101 10.84 11.0801 12.17 11.8501L13.21 12.4501L14.25 13.0501C15.58 13.8201 15.58 15.0801 14.25 15.8502L13.21 16.4502L12.17 17.0502C10.84 17.8201 9.75 17.1901 9.75 15.6501V14.4501Z"
            stroke="currentColor" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    </div>
</template>


<script setup>

const props = defineProps({
    colorClass: {
        type: String,
        default: '#24262D'
    },
});
</script>
