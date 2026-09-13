<template>
    <section class="play-meta-background">
        <span class="container d-block text-light">{{ $t('Home') }} /<router-link :to="'/details/' + courseData?.course?.id"
                class="text-light">{{ $t('My Courses') }}</router-link>/{{ courseData?.course?.title }}</span>
    </section>
    <main class="container py-4 move-top">
        <div class="card">
            <div class="card-body">
                <section v-if="canView" class="row">
                    <section class="col-12 col-lg-7 px-2 px-lg-4">
                        <video class="rounded" v-if="
                            contentData.type == 'video' &&
                            contentData.media_id
                        " :src="contentData?.media" width="100%" ref="videoElement" controls @play="onPlayVideo"
                            @pause="onPauseVideo"></video>
                        <div v-if="
                            contentData.type == 'video' &&
                            contentData.media_link
                        ">
                            <div width="100%" v-html="contentData.media_link"></div>
                        </div>
                        <div v-if="contentData.type == 'audio'"
                            class="border border-primary text-center px-4 pb-4 pt-5 rounded mb-5">
                            <i class="bi bi-mic display-1 rounded-circle border px-3 text-primary"></i>
                            <p class="mt-3 fs-4 col-8 mx-auto my-4 pt-4">
                                {{ contentData?.title }}.{{
                                    contentData?.file_extension
                                }}
                            </p>
                            <audio :src="contentData.media" ref="audioElement" controls @play="onPlayAudio"
                                @pause="onPauseAudio" class="w-100"></audio>
                        </div>
                        <div v-if="contentData.type == 'image'">
                            <img :src="contentData?.media" width="100%" height="400"
                                class="mb-3 object-fit-cover rounded" />
                            <a :href="contentData?.media" download class="btn btn-sm btn-primary me-2">{{ $t('Download image') }}
                                <i class="bi bi-download ms-1"></i></a>

                            <a :href="contentData?.media" target="_blank" class="btn btn-sm btn-outline-primary">
                                {{ $t('View in full size') }}
                                <i class="bi bi-box-arrow-up-right ms-2"></i></a>
                        </div>
                        <div v-if="contentData.type == 'document'"
                            class="text-center border border-primary rounded py-5 mb-5">
                            <i class="bi bi-file-earmark-text display-1 rounded-circle border px-3 text-primary"></i>
                            <p class="mt-3 fs-4 col-8 mx-auto">
                                {{ contentData?.title }}.{{
                                    contentData?.file_extension
                                }}
                            </p>
                            <a :href="contentData?.media" download="" target="_blank"
                                class="btn btn-sm btn-primary">{{ $t('Download Document') }}
                                <i class="bi bi-download ms-1"></i></a>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <h4 class="course-title">
                                    {{ courseData?.course?.title }}
                                </h4>
                            </div>
                            <div class="col-12">
                                <p class="instructor-title text-uppercase text-muted">
                                    {{ $t('Instructor') }}
                                </p>
                                <div class="d-flex align-items-center gap-3">
                                    <div>
                                        <img class="instructor-img" :src="courseData?.course?.instructor
                                            ?.profile_picture
                                            " alt="instructor image" />
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h3 class="instructor-name">
                                            {{
                                                courseData?.course?.instructor
                                                    ?.name
                                            }}
                                        </h3>
                                        <p class="instructor-details text-muted">
                                            {{
                                                courseData?.course?.instructor
                                                    ?.title
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <section class="col-12 col-lg-5 border-start px-2 mt-5 mt-lg-0 px-lg-4">
                        <h3 class="mb-3 mb-lg-5 fw-bold fs-2">{{ $t('Playlist') }}</h3>
                        <CourseLessons :chapters="chapterData" :courseId="courseData?.course?.id"
                            :course="courseData?.course" :isPlayingVideo="isPlayingVideo" :contentData="contentData"
                            :exams="examData" :quizes="quizesData" :isPlayingAudio="isPlayingAudio"
                            @playVideo="onPlayVideo" @pauseVideo="onPauseVideo" @playAudio="onPlayAudio"
                            @pauseAudio="onPauseAudio" />
                    </section>
                </section>
                <section v-else>
                    <div class="text-center text-muted">
                        <i class="bi bi-emoji-frown display-5"></i>
                        <p class="mb-0 mt-3">{{ $t('Content not available') }}</p>
                    </div>
                </section>
            </div>
        </div>
    </main>
</template>

<style lang="scss" scoped>
.play-meta-background {
    background: url("/assets/images/website/details-bg.png") no-repeat center;
    background-size: cover;
    padding-top: 20px;
    padding-bottom: 200px;
}

.move-top {
    margin-top: -150px;
}

.course-preview {
    height: 400px;
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

.course-title {
    border-bottom: 1px solid #e2e8f0;
    font-size: 1.1rem;
    padding-top: 0.625rem;
    padding-bottom: 1rem;
}

.instructor-title {
    font-size: 12px;
}

.instructor-img {
    width: 55px;
    height: 55px;
    border-radius: 50%;
}

.instructor-name {
    font-size: 14px;
}

.instructor-details {
    font-size: 12px;
    margin: 0;
}
</style>

<script setup>
import CourseLessons from "../components/CourseLessons.vue";
import { onMounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const route = useRoute();
const courseId = ref(route.params.course_id);
const contentId = ref(route.query.content_id);

let canView = ref({});
let isViwed = ref(false);
let courseData = ref({});
let contentData = ref({});
let chapterData = ref({});
let examData = ref({});
let quizesData = ref({});

let isPlayingVideo = ref(false);
let isPlayingAudio = ref(false);
const videoElement = ref(null); // Reference to the video element
const audioElement = ref(null); // Reference to the audio element

const onPlayVideo = (id) => {
    isPlayingVideo.value = true;
    if (isPlayingVideo.value && videoElement.value) {
        videoElement.value.play();
    }
};

const onPauseVideo = (id) => {
    isPlayingVideo.value = false;
    if (!isPlayingVideo.value && videoElement.value) {
        videoElement.value.pause(); // If you have a reference to the video element
    }
};

const onPlayAudio = (id) => {
    isPlayingAudio.value = true;
    if (isPlayingAudio.value && audioElement.value) {
        audioElement.value.play();
    }
};

const onPauseAudio = (id) => {
    isPlayingAudio.value = false;
    if (!isPlayingAudio.value && audioElement.value) {
        audioElement.value.pause();
    }
};

watch(
    () => route.query.content_id,
    () => {
        contentId.value = route.query.content_id;
        fetchContent(contentId.value);
    }
);

onMounted(() => {
    fetchCourse();
});

// Fetch course
const fetchCourse = async () => {
    try {
        const response = await axios.get(`/course/show/${courseId.value}`, {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: `Bearer ${authStore.authToken}`,
            },
        });

        courseData.value = response.data.data;
        chapterData.value = response.data.data.chapters;
        examData.value = response.data.data.exams;
        quizesData.value = response.data.data.quizzes;

        if (contentId.value) {
            fetchContent(contentId.value);
        } else {
            contentId.value = response.data.data.chapters[0].contents[0].id;
            fetchContent(contentId.value);
        }
    } catch (error) {
        //
    }
};

const fetchContent = async (contentId) => {
    try {
        const response = await axios.post(`/view_content/${contentId}`, null, {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + useAuthStore().authToken,
            },
        });
        contentData.value = response.data.data.content;

    } catch (error) {
        canView.value = false;
    }
};
</script>
