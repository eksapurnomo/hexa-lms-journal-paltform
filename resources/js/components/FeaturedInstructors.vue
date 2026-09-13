<template>
    <swiper :modules="[Navigation, Pagination, Autoplay]" :space-between="15" :breakpoints="swiperOptions.breakpoints"
        navigation pagination autoplay loop :class="instructors.length < 4 ? 'instructorReviewSlider' : ''">
        <swiper-slide v-for="(instructor, index) in instructors" :key="index" class="mb-5 pb-3">
            <div class="instructor-card card border rounded-4 py-4">
                <div class="card-body text-center p-0">
                    <router-link :to="'/instructor/' + instructor.id" class="text-decoration-none text-dark">
                        <div class="text-center pb-3 d-flex flex-column align-items-center gap-3">
                            <div class="position-relative" style="width: 125px; height: 125px;">
                                <img class="rounded-circle object-fit-cover mb-3" :src="instructor.profile_picture"
                                    height="125px" width="125px" alt="Instructor" />
                                <span
                                    class="instructor-badge position-absolute top-75 badge rounded-circle bg-white text-warning theme-shadow p-2"><i
                                        class="bi bi-star-fill fs-4"></i></span>
                            </div>
                            <h2 class="fs-6 fw-bold m-0">
                                {{ instructor.name }}
                            </h2>
                            <small
                                class="height-meature d-flex justify-content-center align-items-center text-muted px-2">
                                {{ instructor.title }}
                            </small>
                        </div>

                        <div class="d-flex bg-light mb-3 py-2">
                            <div class="col text-end border-end pe-3">
                                <small class="d-inline d-md-block d-lg-inline">
                                    {{ instructor.course_count }} {{ $t('Courses') }}
                                </small>
                            </div>
                            <div class="col text-start ps-3">
                                <small>
                                    {{instructor.student_count }} {{ $t('Enrolled') }}
                                </small>
                            </div>
                        </div>

                        <span>
                            {{ $t('View Profile') }}
                            <i class="bi bi-chevron-right"></i>
                        </span>
                    </router-link>
                </div>
            </div>
        </swiper-slide>
    </swiper>
</template>

<style>
.instructorReviewSlider .swiper-wrapper {
    justify-content: center;
}

@media (max-width: 767px) {
    .instructorReviewSlider .swiper-wrapper {
        justify-content: flex-start;
    }
}
</style>

<style lang="scss" scoped>
.height-meature {
    width: 100%;
    height: 20px;
}

.instructor-card {
    margin: 0 auto;
    transition: all 0.2s ease-out;

    &:hover {
        box-shadow: 0px 16px 64px 0px #0000000d;
    }

    .instructor-badge {
        right: -10px;
        bottom: 10px;
    }
}
</style>

<script setup>
import { ref, onMounted } from "vue";
import { Swiper, SwiperSlide } from "swiper/vue";
import { Navigation, Pagination, Autoplay } from "swiper/modules";

const swiperOptions = {
    breakpoints: {
        320: { slidesPerView: 1, spaceBetween: 10 }, // Extra-small devices
        576: { slidesPerView: 2, spaceBetween: 15 }, // Small devices
        768: { slidesPerView: 2, spaceBetween: 15 }, // Medium devices
        992: { slidesPerView: 3, spaceBetween: 20 }, // Large devices
        1200: { slidesPerView: 4, spaceBetween: 20 }, // Extra-large devices
    },
};

const instructors = ref([]);

const fetchInstructors = async () => {
    try {
        const response = await axios.get(`/instructor/list`, {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            params: {
                items_per_page: 15,
                page_number: 1,
                is_featured: true,
            },
        });
        instructors.value = response.data.data.instructors;
    } catch (error) {
        console.error("Error fetching featured instructors:", error);
    }
};

onMounted(() => {
    fetchInstructors();
});

</script>

