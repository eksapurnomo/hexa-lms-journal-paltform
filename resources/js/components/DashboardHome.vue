<template>
    <section class="mb-4">
        <h4 class="mb-4 fw-bold">{{ $t('Dashboard') }}</h4>
        
        <!-- Current Work Section -->
        <h6 class="text-muted fw-bold text-uppercase mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ $t('Current Work') }}</h6>
        <div class="row g-3 mb-5">
            <!-- LMS Current Work -->
            <div v-if="lastActivityCourse" class="col-12 col-xl-6">
                <div class="theme-shadow rounded bg-white p-3 d-flex align-items-center h-100 position-relative hover-lift transition-all border border-light">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded overflow-hidden bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 60px;">
                            <img v-if="lastActivityCourse?.thumbnail" :src="lastActivityCourse?.thumbnail" class="img-fluid" style="object-fit: cover; width: 100%; height: 100%;" />
                            <i v-else class="bi bi-play-circle fs-3 text-muted"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <span class="badge bg-primary-subtle text-primary mb-1 fw-medium" style="font-size: 0.7rem;">{{ $t('Continue Learning') }}</span>
                        <h6 class="mb-0 text-truncate text-dark fw-bold">
                            {{ lastActivityCourse?.title }}
                        </h6>
                    </div>
                    <div class="flex-shrink-0 ms-3">
                        <router-link :to="'/play/' + lastActivityCourse?.id" class="btn btn-primary rounded-circle shadow-sm" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-play-fill fs-5"></i>
                        </router-link>
                    </div>
                </div>
            </div>

            <!-- Author Submissions -->
            <div v-if="authStore.dashboardContext?.submission?.has_submissions" class="col-12 col-xl-6">
                <div class="theme-shadow rounded bg-white p-3 d-flex align-items-center h-100 position-relative hover-lift transition-all border border-light">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-file-earmark-text text-info fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <span class="badge bg-info-subtle text-info mb-1 fw-medium" style="font-size: 0.7rem;">{{ $t('Author Workspace') }}</span>
                        <h6 class="mb-0 text-truncate text-dark fw-bold">
                            {{ authStore.dashboardContext.submission.count }} Active {{ authStore.dashboardContext.submission.count === 1 ? 'Manuscript' : 'Manuscripts' }}
                        </h6>
                    </div>
                    <div class="flex-shrink-0 ms-3">
                        <router-link to="/author" class="btn btn-outline-info rounded-circle" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-arrow-right fs-5"></i>
                        </router-link>
                    </div>
                </div>
            </div>
            
            <!-- Journal Management -->
            <div v-if="authStore.dashboardContext?.managed_journals?.length > 0" class="col-12 col-xl-6">
                <div class="theme-shadow rounded bg-white p-3 d-flex align-items-center h-100 position-relative hover-lift transition-all border border-light">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-journal-check text-success fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <span class="badge bg-success-subtle text-success mb-1 fw-medium" style="font-size: 0.7rem;">{{ $t('Journal Management') }}</span>
                        <h6 class="mb-0 text-truncate text-dark fw-bold">
                            Managing {{ authStore.dashboardContext.managed_journals.length }} {{ authStore.dashboardContext.managed_journals.length === 1 ? 'Journal' : 'Journals' }}
                        </h6>
                    </div>
                    <div class="flex-shrink-0 ms-3">
                        <router-link to="/dashboard/journals" class="btn btn-outline-success rounded-circle" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-arrow-right fs-5"></i>
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stats Summary Section -->
        <h6 class="text-muted fw-bold text-uppercase mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ $t('Learning Summary') }}</h6>
        <div class="row g-3">
            <div class="col-6 col-md-4">
                <div class="theme-shadow rounded bg-white p-3 d-flex align-items-center border border-light">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 45px; height: 45px;">
                        <i class="bi bi-book text-muted fs-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-muted small fw-medium text-truncate">{{ $t('My Courses') }}</div>
                        <h4 class="mb-0 fw-bold">{{ totalCourseCount }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="theme-shadow rounded bg-white p-3 d-flex align-items-center border border-light">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 45px; height: 45px;">
                        <i class="bi bi-check-circle text-muted fs-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-muted small fw-medium text-truncate">{{ $t('Completed') }}</div>
                        <h4 class="mb-0 fw-bold">{{ completedCourseCount }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="theme-shadow rounded bg-white p-3 d-flex align-items-center border border-light">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 45px; height: 45px;">
                        <i class="bi bi-award text-muted fs-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-muted small fw-medium text-truncate">{{ $t('Certificates') }}</div>
                        <h4 class="mb-0 fw-bold">{{ certificateAchieved }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style lang="scss" scoped>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    &:hover {
        transform: translateY(-2px);
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;
    }
}
.transition-all {
    transition: all 0.2s ease;
}
.min-w-0 {
    min-width: 0;
}
</style>

<script setup>
import { useAuthStore } from "@/stores/auth";
import { ref, onMounted } from "vue";
import axios from "axios";

const authStore = useAuthStore();

let totalCourseCount = ref(0);
let completedCourseCount = ref(0);
let certificateAchieved = ref(0);
let lastActivityCourse = ref(null);

onMounted(() => {
    if (!authStore.dashboardContext || (!authStore.dashboardContext.submission && !authStore.dashboardContext.managed_journals)) {
        authStore.fetchDashboardContext();
    }

    axios
        .get("/enroll_summary", {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + authStore.authToken,
            },
        })
        .then((res) => {
            totalCourseCount.value = res.data.data.total_courses || 0;
            completedCourseCount.value = res.data.data.completed_courses || 0;
            certificateAchieved.value = res.data.data.certificate_achieved || 0;
            
            if (res.data.data.last_activity_course && Object.keys(res.data.data.last_activity_course).length > 0) {
                lastActivityCourse.value = res.data.data.last_activity_course;
            } else {
                lastActivityCourse.value = null;
            }
        }).catch(err => {
            console.error("Failed to load enroll summary", err);
        });
});

const formatDuration = (duration) => {
    if (!duration) return '0 min';
    if (duration >= 60) {
        const hours = Math.floor(duration / 60);
        const minutes = duration % 60;
        return `${hours} hour${hours > 1 ? "s" : ""}${minutes > 0 ? ` ${minutes} min` : ""}`;
    }
    return `${duration} min`;
};
</script>
