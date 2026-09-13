<template>
    <div class="bg-white rounded-3 p-3">
        <CourseRating :courseData="courseData" />
        <div>
            <div
                v-for="review in courseData?.reviews"
                :key="review.id"
                class="bg-light rounded-3 mb-3 px-4 py-2"
            >
                <div
                    class="d-md-flex justify-content-between align-items-start align-items-md-center"
                >
                    <div class="d-md-flex mt-3 pb-3">
                        <img
                            :src="review?.user?.profile_picture"
                            class="rounded-circle object-fit-cover me-3"
                            height="55px"
                            width="55px"
                        />
                        <div>
                            <span class="d-block">{{ review.user.name }}</span>
                            <i class="bi bi-star-fill text-warning me-2"></i
                            ><small class="fw-bold">{{
                                review.rating.toFixed(1)
                            }}</small>
                        </div>
                    </div>
                    <span class="text-muted">{{
                        new Date(review.created_at).toLocaleString("default", {
                            month: "long",
                            day: "numeric",
                            year: "numeric",
                        })
                    }}</span>
                </div>
                <p class="mt-2 mt-md-0">{{ review.comment }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import CourseRating from "./CourseRating.vue";
import { defineProps } from "vue";

const props = defineProps({
    courseData: Object,
});
</script>
