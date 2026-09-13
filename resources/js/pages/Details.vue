<template>
    <section class="details-container bg-light">
        <section class="container py-5 padding-top">
            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="rounded-3 p-4 bg-white">
                        <CourseMetadata :course="courseData.course" />

                        <!-- Navigation -->
                        <ul class="nav nav-pills d-flex justify-content-between align-items-center mb-3 border-bottom"
                            id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="pills-about-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-about" type="button" role="tab" aria-controls="pills-about"
                                    aria-selected="true">
                                    {{ $t('About') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-lessons-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-lessons" type="button" role="tab"
                                    aria-controls="pills-lessons" aria-selected="false">
                                    {{ $t('Lessons') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-free-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-free" type="button" role="tab" aria-controls="pills-free"
                                    aria-selected="false">
                                    {{ $t('Free Trial') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-reviews-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-reviews" type="button" role="tab"
                                    aria-controls="pills-reviews" aria-selected="false">
                                    {{ $t('Reviews') }}
                                </button>
                            </li>
                        </ul>

                        <!-- Tab content -->
                        <div class="bg-light p-3 rounded-3">
                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="pills-about" role="tabpanel"
                                    aria-labelledby="pills-about-tab" tabindex="0">
                                    <CourseAbout :descriptions="courseData.description" />
                                </div>
                                <div class="tab-pane fade" id="pills-lessons" role="tabpanel"
                                    aria-labelledby="pills-lessons-tab" tabindex="0">
                                    <CourseLessons :chapters="courseData?.chapters" :courseId="courseData?.course?.id"
                                        :course="courseData?.course" />
                                </div>
                                <div class="tab-pane fade" id="pills-free" role="tabpanel"
                                    aria-labelledby="pills-free-tab" tabindex="0">
                                    <CourseFree :chapters="courseData?.chapters" :courseId="courseData?.course?.id" />
                                </div>
                                <div class="tab-pane fade" id="pills-reviews" role="tabpanel"
                                    aria-labelledby="pills-reviews-tab" tabindex="0">
                                    <CourseReviews :courseData="courseData" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="rounded-3 p-4 bg-white">
                        <CoursePreview :course="courseData.course" :chapters="courseData.chapters" />
                    </div>
                </div>
            </div>
        </section>
    </section>
</template>

<style lang="scss" scoped>
.details-container {
    background-image: url("/assets/images/website/details-bg.png");
    background-repeat: no-repeat;
    background-size: contain;
}

.padding-top {
    padding-top: 120px !important;
}

.nav {
    .nav-item {
        .nav-link {
            color: #666;
            padding: 15px 25px;
        }

        .nav-link.active {
            background-color: transparent;
            color: #9e4aed;
            border-bottom: 2px solid #9e4aed;
            border-radius: 0;
        }
    }
}
</style>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";
import { useAuthStore } from "@/stores/auth";
import CourseAbout from "../components/CourseAbout.vue";
import CourseLessons from "../components/CourseLessons.vue";
import CourseFree from "../components/CourseFree.vue";
import CourseMetadata from "../components/CourseMetadata.vue";
import CoursePreview from "../components/CoursePreview.vue";
import CourseReviews from "../components/CourseReviews.vue";

const authStore = useAuthStore();
const route = useRoute();
const course_id = route.params.id;

const courseData = ref({});

// Fetch course data
const fetchCourseData = async () => {
    try {
        const response = await axios.get(`/course/show/${course_id}`, {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: `Bearer ${authStore.authToken}`,
            },
        });
        courseData.value = response.data.data;

    } catch (error) {
        console.error("Error fetching course data:", error);
    }
};

onMounted(async () => {
    await fetchCourseData();
    window.scrollTo(0, 0);
});
</script>
