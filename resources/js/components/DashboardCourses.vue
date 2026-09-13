<template>
    <section class="my-2">
        <span class="d-block mb-5">{{ $t('Home') }}/{{ $t('My Courses') }}</span>
        <h3 class="mb-4">{{ $t('My Courses') }}</h3>

        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home"
                    type="button" role="tab" aria-controls="pills-home" aria-selected="true">
                    {{ $t('In Progress') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile"
                    type="button" role="tab" aria-controls="pills-profile" aria-selected="false">
                    {{ $t('Completed') }}
                </button>
            </li>
        </ul>
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab"
                tabindex="0">
                <div class="row">
                    <div v-for="course in inProgressCourses" :key="course.id" class="col-lg-6 mb-4">
                        <div class="theme-shadow rounded d-flex p-3">
                            <img :src="course.thumbnail" alt="" height="125px" width="125px"
                                class="object-fit-cover me-3" />
                            <div class="w-100">
                                <router-link :to="'/instructor/' + course.instructor.id"
                                    class="text-decoration-none d-block">{{ course.instructor.name }}</router-link>
                                <router-link :to="'/details/' + course.id"
                                    class="text-decoration-none d-block text-hover fs-5 mb-1">{{
                                    shortTitle(course.title) }}</router-link>
                                <div class="mb-3">
                                    <small class="me-3">
                                        <i class="bi bi-clock text-success me-1"></i>
                                        {{
                                            formatDuration(
                                                course.total_duration
                                            )
                                        }}
                                    </small>
                                    <small class="me-3">
                                        <i class="bi bi-play-circle text-danger me-1"></i>
                                        {{ course.video_count }} {{ $t('Videos') }}
                                    </small>
                                </div>
                                <div class="d-flex">
                                    <div class="col-10 my-auto pe-3">
                                        <div class="progress" role="progressbar" aria-label="Basic example"
                                            :aria-valuenow="course.progress" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar" :style="'width:' +
                                                course.progress +
                                                '%'
                                                "></div>
                                        </div>
                                    </div>
                                    <div class="col-2 my-auto text-end">
                                        <span class="text-primary">{{
                                            course.progress
                                                ? course.progress
                                                : 0
                                        }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="inProgressCourses.length === 0" class="col-12">
                    <div class="text-center text-muted">
                        <i class="bi bi-emoji-frown display-5"></i>
                        <p class="mb-0 mt-3">{{ $t('No Course Available in Progress') }}</p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab"
                tabindex="0">
                <div class="row">
                    <div v-for="course in completedCourses" :key="course.id" class="col-lg-6 mb-4">
                        <div class="theme-shadow rounded d-flex p-3">
                            <img :src="course.thumbnail" alt="" height="125px" width="125px"
                                class="object-fit-cover me-3" />
                            <div class="w-100">
                                <router-link :to="'/profile/' + course.instructor.id"
                                    class="text-decoration-none d-block">{{ course.instructor.name }}</router-link>
                                <router-link :to="'/details/' + course.id"
                                    class="text-decoration-none d-block text-hover fs-5 mb-1">{{
                                    shortTitle(course.title) }}</router-link>
                                <div class="mb-3">
                                    <small class="me-3">
                                        <i class="bi bi-clock text-success me-1"></i>
                                        {{
                                            Math.floor(
                                                course.total_duration / 60
                                            )
                                        }}
                                        Hours
                                    </small>
                                    <small class="me-3">
                                        <i class="bi bi-play-circle text-danger me-1"></i>
                                        {{ course.video_count }} {{ $t('Videos') }}
                                    </small>
                                </div>
                                <div class="d-flex">
                                    <div class="col-10 my-auto pe-3">
                                        <div class="progress" role="progressbar" aria-label="Basic example"
                                            :aria-valuenow="course.progress" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar" :style="'width:' +
                                                course.progress +
                                                '%'
                                                "></div>
                                        </div>
                                    </div>
                                    <div class="col-2 my-auto text-end">
                                        <span class="text-primary">{{
                                            course.progress
                                                ? course.progress
                                                : 0
                                        }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="completedCourses.length === 0" class="col-12">
                    <div class="text-center text-muted">
                        <i class="bi bi-emoji-frown display-5"></i>
                        <p class="mb-0 mt-3">{{ $t('No Course Completed') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style lang="scss" scoped>
.nav {
    .nav-link {
        color: #000;
        padding: 0.5rem 0;
        margin-right: 2rem;
        border-radius: 0;
    }

    .nav-link.active {
        background-color: #fff;
        color: #0e809e;
        border-bottom: 2px solid #0e809e;
    }
}

.progress {
    height: 0.5rem;
}
</style>

<script setup>
import { useAuthStore } from "@/stores/auth";
import { ref } from "vue";
const authStore = useAuthStore();

let inProgressCourses = ref({});
let completedCourses = ref({});

axios
    .get("/enrolled_courses", {
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + authStore.authToken,
        },
    })
    .then((res) => {
        // inProgressCourses.value = res.data.data.courses.filter((course) => {
        //     return course.progress < 100;
        // });
        // completedCourses.value = res.data.data.courses.filter((course) => {
        //     return course.progress === 100;
        // });
        inProgressCourses.value = res.data.data.courses;
        completedCourses.value = res.data.data.courses;
    });

function shortTitle(title) {
    return title.length > 25 ? title.slice(0, 25) + "..." : title;
}

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
