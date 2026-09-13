<template>
    <swiper :modules="[Navigation, Pagination, Autoplay]" :slides-per-view="4.2" :space-between="20"
        :breakpoints="swiperOptions.breakpoints" navigation pagination autoplay loop>
        <swiper-slide v-for="(course, index) in courses" :key="index" class="mb-5 pb-3">
            <CourseCard :course="course" />
        </swiper-slide>
    </swiper>
</template>

<script setup>
import { Swiper, SwiperSlide } from "swiper/vue";
import { Navigation, Pagination, Autoplay } from "swiper/modules";
import { useAuthStore } from "@/stores/auth";
import { onMounted, ref } from "vue";
import CourseCard from "./CourseCard.vue";

const swiperOptions = {
    breakpoints: {
        320: { slidesPerView: 1.2, spaceBetween: 10 }, // Extra-small devices
        576: { slidesPerView: 2.5, spaceBetween: 15 }, // Small devices
        768: { slidesPerView: 2.5, spaceBetween: 15 }, // Medium devices
        992: { slidesPerView: 3, spaceBetween: 20 }, // Large devices
        1200: { slidesPerView: 4.2, spaceBetween: 20 }, // Extra-large devices
    },
};

const authStore = useAuthStore();

const courses = ref([]);

onMounted(() => {
    axios
        .get(`/course/list`, {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + authStore.authToken,
            },
            params: {
                items_per_page: 10,
                page_number: 1,
                sort: "published_at",
                sortDirection: "desc",
            },
        })
        .then((res) => {
            courses.value = res.data.data.courses;
        });
});
</script>
