<template>
    <section>
        <div v-if="!showVideo" class="course-preview mb-4" :style="'background-image: url(' + course?.thumbnail + ');'">
            <span v-if="course?.video" @click="playVideo()" class="play-btn d-flex rounded-circle bg-primary px-2">
                <i class="bi bi-play-fill"></i>
            </span>
        </div>
        <video v-if="showVideo" :src="course?.video" width="100%" height="100%" controls autoplay></video>
        <div class="bg-light p-3 rounded-3 mb-4">
            <div class="d-inline-block mb-3 me-2">
                <span class="bg-white px-2 py-1 text-muted rounded">{{ $t('Lifetime Course Access') }}</span>
            </div>

            <div class="d-inline-block mb-3 me-2">
                <span class="bg-white px-2 py-1 text-muted rounded">{{ course?.video_count }} {{ $t('Video Lectures')
                }}</span>
            </div>

            <div class="d-inline-block mb-3 me-2">
                <span class="bg-white px-2 py-1 text-muted rounded">{{ course?.audio_count }} {{ $t('Audio Content')
                }}</span>
            </div>

            <div class="d-inline-block mb-3 me-2">
                <span class="bg-white px-2 py-1 text-muted rounded">{{ course?.note_count }} {{ $t('Notes') }}</span>
            </div>

            <div class="d-inline-block mb-3 me-2">
                <span class="bg-white px-2 py-1 text-muted rounded">{{ course?.free_video }} {{ $t('Free Videos')
                }}</span>
            </div>

            <div class="d-inline-block mb-3 me-2">
                <span class="bg-white px-2 py-1 text-muted rounded">{{ course?.free_content }} {{ $t('Free Content')
                }}</span>
            </div>

            <div v-if="course?.certificate_available" class="d-inline-block mb-3 me-2">
                <span class="bg-white px-2 py-1 text-muted rounded">{{ $t('Certificate Available') }}</span>
            </div>
        </div>

        <div v-if="!course?.is_free && !course?.is_enrolled" class="enroll-action border border-primary rounded-3 pt-3">
            <div class="text-center mb-4">
                <span class="d-block">{{ $t('Course Fee') }}</span>
                <div v-if="course?.price && course?.regular_price">
                    <strong v-if="masterStore?.masterData?.currency_position == 'Left'" class="fs-2 me-2">{{
                        masterStore?.masterData?.currency_symbol
                        }}{{ course?.price }}</strong>
                    <strong v-else class="fs-2 me-2">{{ course?.price
                        }}{{ masterStore?.masterData?.currency_symbol }}</strong>
                    <span v-if="masterStore?.masterData?.currency_position == 'Left'"
                        class="fs-3 text-muted text-decoration-line-through">{{ masterStore?.masterData?.currency_symbol
                        }}{{ course?.regular_price }}</span>
                    <span v-else class="fs-3 text-muted text-decoration-line-through">{{ course?.regular_price
                        }}{{ masterStore?.masterData?.currency_symbol }}</span>
                </div>
                <div v-else-if="!course?.price && course?.regular_price">
                    <strong v-if="masterStore?.masterData?.currency_position == 'Left'" class="fs-2 me-2">{{
                        masterStore?.masterData?.currency_symbol
                        }}{{ course?.regular_price }}</strong>
                    <strong v-else class="fs-2 me-2">{{ course?.regular_price
                        }}{{ masterStore?.masterData?.currency_symbol }}</strong>
                </div>
            </div>

            <router-link :to="'/checkout/' + course?.id" @click.prevent="handleRoute"
                class="enroll-btn btn btn-primary py-3 w-100">Enroll Now<i
                    class="bi bi-arrow-right ms-2"></i></router-link>
        </div>
        <router-link v-else
            :to="!course?.is_enrolled ? '/checkout/' + course?.id : authStore.userData ? '/play/' + course?.id : ''"
            @click.prevent="handleWaring" class="btn btn-primary w-100">
            {{ course?.is_enrolled ? $t('Play Now') : $t('Enroll Now') }}
        </router-link>
    </section>
</template>

<style lang="scss" scoped>
.course-preview {
    height: 225px;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    border-radius: 1rem;
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

.student-count {
    background-color: #f0ffef;
}

.lesson-count {
    background-color: #fff6f5;
}

.resource-count {
    background-color: #f3f9ff;
}

.enroll-action {
    background-color: #faf6fe;

    .enroll-btn {
        border-top-left-radius: 0;
        border-top-right-radius: 0;
        border-bottom-left-radius: 1rem;
        border-bottom-right-radius: 1rem;
    }
}
</style>

<script setup>
import { ref, computed } from "vue";
import Swal from "sweetalert2";
import { useAuthStore } from "@/stores/auth";
import { useMasterStore } from "@/stores/master";

const authStore = useAuthStore();
const masterStore = useMasterStore();

let props = defineProps({
    course: Object,
    chapters: Array,
});

const showVideo = ref(false);

function playVideo() {
    showVideo.value = true;
}

const handleRoute = () => {
    localStorage.setItem("handle_course_id", props.course.id);
}

let handleWaring = () => {
    if (!authStore.userData) {
        Swal.fire({
            icon: "error",
            title: "Sorry...",
            text: "You need to log in to access this page.",
            confirmButtonText: "Go to Login",
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to the login page when the user clicks the button
                window.location.href = "/login"; // Replace "/login" with your actual login URL
            }
        });
    }
};
</script>
