<template>
    <div class="row align-items-center border-bottom pb-3 mb-3">
        <div class="col-3 p-4 border-end">
            <h3 class="fw-bold">{{ averageRating }}</h3>
            <div class="mb-2">
                <i v-for="star in 5" :key="star" class="bi" :class="{
                    'bi-star-fill text-warning me-1': star <= Math.floor(averageRating),
                    'bi-star-half text-warning me-1': star === Math.ceil(averageRating) && averageRating % 1 !== 0,
                    'bi-star me-1': star > Math.ceil(averageRating)
                }"></i>
            </div>
            <span class="text-muted">
                {{ reviewCount }} {{ $t('reviews')}}
            </span>
        </div>
        <div class="col-9 px-5">
            <div class="row align-items-center" v-for="(rating, index) in [5, 4, 3, 2, 1]" :key="index">
                <div class="col-1">
                    <span>{{ rating }}</span>
                </div>
                <div class="col-11">
                    <div class="progress" :aria-valuenow="getRatingPercentage(rating)" aria-valuemin="0"
                        aria-valuemax="100" style="height: 10px;">
                        <div class="progress-bar bg-warning" :style="{ width: getRatingPercentage(rating) + '%' }">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, computed } from 'vue';

const props = defineProps({
    courseData: Object
});

const averageRating = computed(() => {
    const totalRating = props?.courseData?.reviews?.reduce((sum, review) => sum + review.rating, 0);
    return (totalRating / props?.courseData?.reviews?.length).toFixed(1);
});

const reviewCount = computed(() => props?.courseData?.reviews?.length);

const getRatingPercentage = (ratingLevel) => {
    const totalReviews = props?.courseData?.reviews?.length;
    const count = props?.courseData?.reviews?.filter(review => review.rating === ratingLevel)?.length;
    return totalReviews > 0 ? (count / totalReviews) * 100 : 0;
};
</script>
