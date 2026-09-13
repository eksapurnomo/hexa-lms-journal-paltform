<template>
    <section class="instructor-section bg-light">
        <section class="container">
            <div class="row">
                <div class="col-12 mt-5 mt-lg-0 col-lg-4">
                    <InstructorAbout :instructor="instructor" />
                </div>
                <div class="col-12 my-3 my-lg-0 col-lg-8">
                    <div class="bg-white rounded-3 p-3">
                        <strong class="d-block mb-3">{{ $t('About the Instructor') }}</strong>
                        <div class="mb-3">
                            <span class="mb-4 me-2">
                                {{ displayedAbout }}
                            </span>
                            <a v-if="
                                !isFullAbout &&
                                displayedAbout.length > 50
                            " @click.prevent="loadMore" class="text-primary load-more">View More</a>
                            <a v-if="
                                isFullAbout &&
                                displayedAbout.length > 50
                            " @click.prevent="loadLess" class="text-primary load-more">View Less</a>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <strong class="d-block">{{ $t('Courses') }}</strong>
                                <small>{{ $t('Showing') }} {{ sortedCourses.length }} {{ $t('courses') }}</small>
                            </div>
                            <div class="dropdown">
                                <button class="btn px-3 py-2 border" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    {{ $t('Sort by') }}: {{ currentSort }}
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" @click.prevent="
                                            sortCourses('high-to-low')
                                            ">{{ $t('Course Fee') }}: {{ $t('High to Low') }}</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" @click.prevent="
                                            sortCourses('low-to-high')
                                            ">{{ $t('Course Fee') }}: {{ $t('Low to High') }}</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" @click.prevent="
                                            sortCourses('popular')
                                            ">{{ $t('Popular Courses') }}</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" @click.prevent="
                                            sortCourses('newest')
                                            ">{{ $t('New Courses') }}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="instructor-courses row row-cols-1 row-cols-md-2 row-cols-lg-2">
                            <div v-for="course in sortedCourses" :key="course.id" class="mb-4">
                                <CourseCard :course="course" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
</template>

<style lang="scss">
.instructor-section {
    padding: 80px 0;
    flex-grow: 1;
    display: flex;
    justify-content: center;
    align-items: center;
}

.instructor-courses {
    max-height: 550px;
    overflow-y: auto;
}

.load-more {
    cursor: pointer;
    font-size: 0.75rem;
    font-weight: 500;
}


@media (max-width: 992px) {
    .instructor-section {
        height: auto;
    }
}
</style>

<script setup>
import { ref, computed } from "vue";
import InstructorAbout from "../components/InstructorAbout.vue";
import CourseCard from "../components/CourseCard.vue";
import { useRoute } from "vue-router";

const route = useRoute();
let instructor = ref({});
let courses = ref({});
let sortedCourses = ref([]);
let isAboutCollapsed = ref(false);
const currentSort = ref("Default");
let isFullAbout = ref(false);

// Fetch instructor profile
axios
    .get(`/instructor/show/${route.params.id}`, {
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
        },
    })
    .then((response) => {
        instructor.value = response.data.data.instructor;
        courses.value = response.data.data.courses;
        sortedCourses.value = [...courses.value];
    })
    .catch((error) => {
        console.error(error);
    });

const sortCourses = (criteria) => {
    if (criteria === "high-to-low") {
        sortedCourses.value = [...courses.value].sort(
            (a, b) => b.price - a.price
        );
        currentSort.value = "Course Fee: High to Low";
    } else if (criteria === "low-to-high") {
        sortedCourses.value = [...courses.value].sort(
            (a, b) => a.price - b.price
        );
        currentSort.value = "Course Fee: Low to High";
    } else if (criteria === "popular") {
        // Implement your logic for sorting by popularity
        sortedCourses.value = [...courses.value].sort(
            (a, b) => b.view_count - a.view_count
        );
        currentSort.value = "Popular Courses";
    } else if (criteria === "newest") {
        // Sort by created_at in descending order (newest first)
        sortedCourses.value = [...courses.value].sort(
            (a, b) => new Date(b.published_at) - new Date(a.published_at)
        );
        currentSort.value = "Newest Courses";
    }
};


const displayedAbout = computed(() => {
    if (!instructor.value || !instructor.value.about) return "";
    if (isFullAbout.value) {
        return instructor.value.about;
    } else {
        return instructor.value.about.substring(0, 200) + (instructor.value.about.length > 500 ? " ... " : "");
    }
});

// Load more function
let loadMore = () => {
    isFullAbout.value = true;
};
let loadLess = () => {
    isFullAbout.value = false;
};


</script>
