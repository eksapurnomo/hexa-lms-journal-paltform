<template>
    <section class="my-2">
        <span class="d-block mb-3 mb-lg-5">{{ $t('Home') }}/{{ $t('Dashboard') }}</span>
        <div class="row mb-4">
            <div v-if="lastActivityCourse" class="col-12 col-lg-6 mb-3 mb-lg-0">
                <h4 class="mb-4">{{ $t('Recent Lesson') }}</h4>
                <div class="mb-4 theme-shadow course-preview-wrapper">
                    <div class="course-preview" :style="'background-image: url(' +
                        lastActivityCourse?.thumbnail +
                        ');'
                        ">
                        <router-link :to="'/play/' + lastActivityCourse?.id"
                            class="play-btn d-flex rounded-circle bg-primary text-white px-2">
                            <i class="bi bi-play-fill"></i>
                        </router-link>
                    </div>
                    <div class="p-3">
                        <router-link :to="'/details/' + lastActivityCourse?.id" class="text-decoration-none text-hover">
                            <h5 class="card-title mb-3">
                                {{ lastActivityCourse?.title }}
                            </h5>
                        </router-link>
                        <router-link :to="'/play/' + lastActivityCourse.id"
                            class="text-decoration-none text-muted d-flex justify-content-between border rounded px-3 py-2">
                            <div>
                                <i class="bi bi-play-circle-fill text-danger me-2"></i>
                                <small>{{
                                    lastActivityCourse?.title?.length > 30
                                        ? lastActivityCourse?.title.slice(
                                            0,
                                            30
                                        ) + "..."
                                        : lastActivityCourse?.title
                                }}</small>
                            </div>
                            <small>{{
                                formatDuration(
                                    lastActivityCourse.total_duration
                                )
                            }}</small>
                        </router-link>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6 d-flex d-lg-block justify-content-center">
                <h4 class="mb-4">{{ $t('Activity Log') }}</h4>
                <div v-if="totalCourseCount > 0">
                    <VueApexCharts type="donut" :options="chartOptions" :series="series" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-lg-4 mb-4 mb-lg-0">
                <div class="theme-shadow rounded p-3 d-flex justify-content-between h-100">
                    <div class="my-auto">
                        <span class="d-block">{{ $t('My Courses') }}</span>
                        <strong class="fs-5">{{ totalCourseCount }}</strong>
                    </div>
                    <img :src="'assets/images/website/hero-popular.png'" height="60px" width="60px" class="my-auto" />
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4 mb-lg-0">
                <div class="theme-shadow rounded p-3 d-flex justify-content-between h-100">
                    <div class="my-auto">
                        <span class="d-block">{{ $t('Completed Courses') }}</span>
                        <strong class="fs-5">{{ completedCourseCount }}</strong>
                    </div>
                    <img :src="'assets/images/website/hero-popular.png'" height="60px" width="60px" class="my-auto" />
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4 mb-lg-0 mx-auto mx-lg-0">
                <div class="theme-shadow rounded p-3 d-flex justify-content-between h-100">
                    <div class="my-auto">
                        <span class="d-block">{{ $t('Certificate Achieved') }}</span>
                        <strong class="fs-5">{{ certificateAchieved }}</strong>
                    </div>
                    <img :src="'assets/images/website/hero-popular.png'" height="60px" width="60px" class="my-auto" />
                </div>
            </div>
        </div>
    </section>
</template>

<style lang="scss" scoped>
.course-preview {
    height: 300px;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 2rem;
    font-weight: bold;

    .play-btn {
        cursor: pointer;
        border: 5px solid #ffffffbd;
        border-radius: 50%;
    }
}

.course-preview-wrapper {
    border-radius: 1rem;
}
</style>

<script setup>
import VueApexCharts from "vue3-apexcharts";
import { useAuthStore } from "@/stores/auth";
import { ref } from "vue";

const authStore = useAuthStore();

let totalCourseCount = ref(0);
let completedCourseCount = ref(0);
let certificateAchieved = ref(0);
let lastActivityCourse = ref({});

let series = ref([]);

let chartOptions = ref({
    // series: series.value,
    chart: {
        type: "donut",
    },
    labels: ["Total Courses", "Completed Courses", "Certificates Achieved"],
    responsive: [
        {
            breakpoint: 480,
            options: {
                chart: {
                    width: 200,
                },
                legend: {
                    position: "bottom",
                },
            },
        },
    ],
});

axios
    .get("/enroll_summary", {
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + authStore.authToken,
        },
    })
    .then((res) => {
        (totalCourseCount.value = res.data.data.total_courses),
            (completedCourseCount.value = res.data.data.completed_courses),
            (certificateAchieved.value = res.data.data.certificate_achieved),
            (lastActivityCourse.value = res.data.data.last_activity_course);

        series.value = [
            totalCourseCount.value,
            completedCourseCount.value,
            certificateAchieved.value,
        ];
    });

// works on time formating

const formatDuration = (duration) => {
    if (duration >= 60) {
        const hours = Math.floor(duration / 60);
        const minutes = duration % 60;
        return `${hours} hour${hours > 1 ? "s" : ""}${minutes > 0 ? ` ${minutes} min` : ""
            }`;
    }
    return `${duration} min`;
};
</script>
