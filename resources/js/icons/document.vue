<template>
    <div :class="colorClass">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path opacity="0.4" d="M8 12.1992H15" stroke="currentColor" stroke-miterlimit="10" stroke-linecap="round"
                stroke-linejoin="round" />
            <path opacity="0.4" d="M8 16.1992H12.38" stroke="currentColor" stroke-miterlimit="10" stroke-linecap="round"
                stroke-linejoin="round" />
            <path
                d="M10 6.00001H14C16.0001 6.00001 16.0001 5 16.0001 4C16.0001 2 15 2 14 2H10C9.00001 2 8 2 8 4C8 6.00001 9.00001 6.00001 10 6.00001Z"
                stroke="currentColor" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
            <path
                d="M16 4.01953C19.33 4.19953 21 5.42953 21 9.99953V15.9995C21 19.9996 20 21.9995 15 21.9995H9C4 21.9995 3 19.9996 3 15.9995V9.99953C3 5.43953 4.67 4.19953 8 4.01953"
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
