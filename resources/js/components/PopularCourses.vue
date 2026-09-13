<template>
    <div
        class="popular-categories d-flex flex-wrap gap-3 justify-content-center mb-5"
    >
        <button
            @click="categoryId = null"
            :class="
                'btn bg-white me-3 py-2 px-3' +
                (categoryId == null ? ' active' : '')
            "
        >
            {{ $t('All') }}
        </button>
        <button
            @click="
                categoryId = category.id;
                pageNumber = 1;
            "
            v-for="category in featuredCategories"
            :key="category.id"
            :class="
                'btn bg-white me-3 py-2 px-3' +
                (category.id == categoryId ? ' active' : '')
            "
        >
            {{ category.title }}
        </button>
    </div>
    <div class="row">
        <div v-for="course in popularCourses" :key="course.id" class="mb-4 col-12 col-md-6 col-lg-4 col-xl-3">
            <CourseCard :course="course" />
        </div>
    </div>

    <div v-if="popularCourses.length == 0" class="text-center my-5">
        <i
            class="ri-emotion-unhappy-line text-muted d-block display-1 mb-3"
        ></i>
        <h3>{{ $t('No courses found') }}.</h3>
    </div>
    <div v-if="totalCourses > popularCourses.length" class="text-center">
        <button
            @click="loadMore"
            class="btn btn-outline-primary bg-white text-primary rounded-pill px-5 fw-bold mt-4"
        >
            {{ $t('Load More') }}
        </button>
    </div>
</template>

<style lang="scss" scoped>
.popular-categories {
    .btn {
        &:hover {
            color: #9e4aed;
            border-color: #9e4aed;
        }
    }

    .btn.active {
        color: #9e4aed;
        border-color: #9e4aed;
    }
}
</style>

<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import CourseCard from "./CourseCard.vue";
const categoryId = ref(null);
const popularCourses = ref([]);
const featuredCategories = ref([]);
const totalCourses = ref(0);

import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();

let itemsPerPage = ref(8);

let loadMore = () => {
    itemsPerPage.value += 4;
    fetchPopularCourses();
};

// Fetch popular courses
const fetchPopularCourses = async (pageNumber = 1, push = false) => {
    try {
        const response = await axios.get(`/course/list`, {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: `Bearer ${authStore.authToken}`,
            },
            params: {
                items_per_page: itemsPerPage.value,
                page_number: pageNumber.value,
                category_id: categoryId.value,
                sort: "view_count",
                sortDirection: "desc",
            },
        });

        if (push) {
            popularCourses.value.push(...response.data.data.courses);
        } else {
            popularCourses.value = response.data.data.courses;
        }
        totalCourses.value = response.data.data.total_courses;
    } catch (error) {
        console.error("Error fetching popular courses:", error);
    }
};

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
                items_per_page: 6,
                page_number: 1,
            },
        });
        featuredCategories.value = response.data.data.categories;
    } catch (error) {
        console.error("Error fetching featured categories:", error);
    }
};

onMounted(() => {
    fetchPopularCourses();
    fetchFeaturedCategories();
});

watch(
    () => categoryId.value,
    () => {
        fetchPopularCourses();
    }
);
</script>
