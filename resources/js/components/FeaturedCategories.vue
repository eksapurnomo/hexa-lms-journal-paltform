<template>
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h3 class="fs-2 fw-bold">{{ $t('Our Top Categories') }}</h3>
        <router-link class="text-primary text-decoration-none fw-bold">{{ $t('View All') }} <font-awesome-icon
                :icon="faArrowRightLong" /></router-link>
    </div>
    <swiper :modules="[Navigation, Pagination, Autoplay]" :slides-per-view="5.5" :space-between="20"
        :breakpoints="swiperOptions.breakpoints" navigation pagination autoplay loop class="category-slider">
        <swiper-slide v-for="(category, index) in featuredCategories" :key="index" class="mb-3">
            <router-link :to="'/courses?category_id=' + category.id"
                class="d-block text-decoration-none text-dark mb-5">
                <div class="category-box p-4">
                    <div class="cat-img">
                        <img :src="category.image" :alt="category.title" class="w-100 h-100 mb-3" />
                    </div>
                    <div class="fix-height">
                        <h5 class="fs-5 fw-bold fs-6">
                            {{ category.title.slice(0, 40) }}
                        </h5>
                    </div>
                    <span class="d-flex justify-content-between">
                        <small class="text-muted">{{ category.course_count }} {{ $t('Courses') }}</small>
                        <i class="bi bi-chevron-right"></i>
                    </span>
                </div>
            </router-link>
        </swiper-slide>
    </swiper>
</template>

<style lang="scss" scoped>
.cat-img {
    width: 80px;
    height: 80px;
}

.fix-height {
    height: 80px;
    display: flex;
    align-items: center;
}

.category-box {
    border-radius: 15px;
    border: 1px solid #eee;
    transition: all 0.2s ease-out;
    height: 230px;

    &:hover {
        border-color: #9e4aed;
        border-top-width: 4px;
        box-shadow: 0px 16px 64px 0px #0000000d;
    }
}
</style>

<script setup>
import { Swiper, SwiperSlide } from "swiper/vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faArrowRightLong } from "@fortawesome/free-solid-svg-icons";
import { Navigation, Pagination, Autoplay } from "swiper/modules";
import { onMounted, ref } from "vue";
import axios from "axios";

const swiperOptions = {
    breakpoints: {
        320: { slidesPerView: 1.5, spaceBetween: 10 }, // Extra-small devices
        576: { slidesPerView: 2.5, spaceBetween: 15 }, // Small devices
        768: { slidesPerView: 3.5, spaceBetween: 15 }, // Medium devices
        992: { slidesPerView: 4.5, spaceBetween: 20 }, // Large devices
        1200: { slidesPerView: 5.5, spaceBetween: 20 }, // Extra-large devices
    },
};

let featuredCategories = ref([]);

// Fetch featured categories
const fetchFeaturedCategories = async () => {
    try {
        const response = await axios.get(`/categories`, {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            params: {
                is_featured: true,
                items_per_page: 20,
                page_number: 1,
            },
        });
        featuredCategories.value = response.data.data.categories;
    } catch (error) {
        console.error("Error fetching categories:", error);
    }
};

// Call the function when the component is mounted
onMounted(() => {
    fetchFeaturedCategories();
});
</script>
