<template>
    <strong class="d-block mb-1">{{ $t('Select Ratings') }}</strong>

    <div id="full-stars-example-two">
        <div class="rating">
            <label v-for="star in 5" :key="star" class="rating__label" @click="applyRatingFilter(star)">
                <span class="rating__icon fa fa-star" :class="{ 'active': star <= selectedRating }">
                    <FontAwesomeIcon :icon="solidStar" />
                </span>
            </label>
        </div>
    </div>
</template>

<style lang="scss">
#full-stars-example-two {

    .rating {
        display: flex;
        flex-direction: row;
        margin: 10px 0;
    }

    .rating__label {
        cursor: pointer;
        margin: 0 4px;
    }

    .rating__icon {
        color: #ccc;
        transition: color 0.3s;
        font-size: 25px;
    }

    .rating__icon.active {
        color: orange;
    }
}
</style>


<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faStar as solidStar } from '@fortawesome/free-solid-svg-icons';

const router = useRouter();
const instructors = ref([]);
const totalInstructors = ref(0);
const emit = defineEmits(['RatingFilter']);
const selectedRating = ref(0)

function applyRatingFilter(rating) {
    router.push('/courses');
  selectedRating.value = rating
  emit('RatingFilter', selectedRating.value)
}

// Fetch instructors
function fetchInstructors() {
    axios
        .get(`/instructor/list`, {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        })
        .then((res) => {
            instructors.value = res.data.data.instructors;
            totalInstructors.value = res.data.data.total_items;
        })
        .catch((error) => {
            console.error('Error fetching instructors:', error);
        });
}

onMounted(() => {
    fetchInstructors();
})
</script>
