<template>
    <section class="mb-5 p-3 rounded" style="background: #e2e8f0">
        <div class="text-center mt-3" style="cursor: pointer">
            <p class="text-decoration-none text-primary">
                <i class="bi bi-highlighter fs-5"></i> {{ $t('Learn with Ease: Step-by-Step Lessons') }}
            </p>
        </div>
        <div class="accordion" id="contentAccordion">
            <div v-if="Array.isArray(chapters)">
                <div v-for="(chapter, index) in (chapters || []).slice(
                    0,
                    chaptersToShow
                )" :key="index" class="border border-2 bg-white rounded mb-2" :class="{
                    'border-primary': activeIndex === index,
                    collapsed: activeIndex !== index,
                }">
                    <h2 class="m-0">
                        <button class="accordion-button bg-white border rounded" type="button"
                            :class="{ collapsed: index !== 0 }" :data-bs-toggle="'collapse'"
                            :data-bs-target="'#chapter' + index" aria-expanded="index === 0"
                            aria-controls="'chapter' + index" @click="toggleAccordion(index)">
                            <div class="accordion-content w-100">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <small class="me-2 text-muted">Class {{ index + 1 }}</small>
                                    <small v-if="chapter.duration > 0" class="me-2 text-muted chapter-duration">
                                        {{
                                            formatDuration(chapter?.duration ?? 0)
                                        }}
                                    </small>
                                </div>
                                <span class="chapter-title">{{
                                    chapter.title
                                }}</span>
                            </div>
                        </button>
                    </h2>
                    <div :id="'chapter' + index" class="accordion-collapse collapse my-1" :class="{ show: index === 0 }"
                        data-bs-parent="#contentAccordion">
                        <div class="accordion-body border-start border-end border-light p-0 mx-1">
                            <div v-for="(content, contentIndex) in chapter.contents" :key="content.id">
                                <!-- Content Link -->
                                <router-link v-if="
                                    course?.is_enrolled || content.is_free
                                " :to="authStore.userData
                                    ? `/play/${courseId}?content_id=${content.id}`
                                    : ''
                                    " @click.prevent="handleWaring" @click="markContentComplete(content.id)" :class="{
                                        'd-block px-3 py-2 rounded-3 border-light text-decoration-none text-dark content-link': true,
                                        active:
                                            content.id ==
                                            route.query.content_id ||
                                            (route.path !==
                                                '/details/[course_id]' &&
                                                !route.query.content_id &&
                                                contentIndex === 0),
                                        completed: content.completed,
                                        view: content.is_viewed,
                                    }">
                                    <div class="d-flex align-items-center" @click="handleView(content.id)">

                                        <Video v-if="
                                            content.type === 'video' &&
                                            content.media_id &&
                                            isPlayingVideo &&
                                            currentId == content.id
                                        " @click="togglePlay(content.id)"
                                            :colorClass="content.is_viewed ? 'text-primary' : 'text-muted'"
                                            class="mg-fluid me-3 svgEdit" />

                                        <Video v-if="
                                            content.type === 'video' &&
                                            content.media_id &&
                                            (!isPlayingVideo ||
                                                currentId != content.id)
                                        " @click="togglePlay(content.id)"
                                            :colorClass="content.is_viewed ? 'text-primary' : 'text-muted'"
                                            class="mg-fluid me-3 svgEdit" />

                                        <Video v-if="
                                            content.type === 'video' &&
                                            !content.media_id"
                                            :colorClass="content.is_viewed ? 'text-primary' : 'text-muted'"
                                            class="mg-fluid me-3 svgEdit" />

                                        <Audio v-if="
                                            content.type === 'audio' &&
                                            isPlayingAudio &&
                                            currentId == content.id" @click="togglePlayAudio(content.id)"
                                            class="img-fluid me-3 svgEdit" />

                                        <Audio v-if="
                                            content.type === 'audio' &&
                                            (!isPlayingAudio ||
                                                currentId != content.id)" @click="togglePlayAudio(content.id)"
                                            class="img-fluid me-3 svgEdit" />

                                        <Document v-if="content.type === 'document'"
                                            :colorClass="content.is_viewed ? 'text-primary' : 'text-muted'"
                                            class="img-fluid me-3 svgEdit" />


                                        <Image v-if="content.type === 'image'"
                                            :colorClass="content.is_viewed ? 'text-primary' : 'text-muted'"
                                            class="img-fluid me-3 svgEdit" />



                                        <span :class="content.is_viewed
                                            ? 'text-primary title-font'
                                            : 'text-muted title-font'
                                            ">{{ content.title }}</span>

                                        <div class="ms-auto d-flex justify-content-between align-items-center gap-3">
                                            <small v-if="content.duration > 0" class="text-muted"
                                                style="font-size: 0.625em">{{
                                                    formatDuration(content.duration ?? 0)
                                                }}
                                            </small>
                                            <small class="text-muted" style="font-size: 0.625em">
                                                <FontAwesomeIcon :icon="content.is_viewed
                                                    ? ''
                                                    : faCircle
                                                    " :class="content.is_viewed
                                                        ? ''
                                                        : 'text-primary'
                                                        " />
                                            </small>
                                        </div>
                                    </div>
                                </router-link>
                                <!-- Enroll Warning -->
                                <div v-else @click="enrollWarning"
                                    class="content d-block px-3 py-2 border-bottom border-light text-decoration-none text-dark">
                                    <div class="d-flex align-items-center">
                                        <Video v-if="content.type === 'video'" class="img-fluid me-3 svgEdit" />
                                        <Audio v-if="content.type === 'audio'" class="img-fluid me-3 svgEdit" />
                                        <Document v-if="content.type === 'document'"
                                            class="img-fluid me-3 svgEdit" />
                                        <Image v-if="content.type === 'image'" class="img-fluid me-3 svgEdit" />
                                        <span class="text-muted title-font">
                                            {{ content.title }}
                                        </span>
                                        <small class="text-muted ms-auto"><i class="bi bi-lock-fill"></i></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-3" style="cursor: pointer">
            <a @click.prevent="showMoreChapters" :class="chapters?.length === 0
                ? 'text-danger text-decoration-none'
                : 'text-decoration-none'
                ">
                {{
                    chapters?.length === 0
                        ? $t("No Content Available")
                        : chapters?.length > 4
                            ? $t("See More")
                            : ""
                }}
            </a>
        </div>

        <!-- exam & quiz start section -->

        <div v-if="$route.path.includes('/play')">
            <div class="text-center mt-3" style="cursor: pointer">
                <p class="text-decoration-none text-primary">
                    <i class="bi bi-patch-question-fill fs-5"></i> {{ $t('Exam & Quiz') }}
                </p>
            </div>

            <div class="accordion" id="accordionPanelsStayOpenExample">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true"
                            aria-controls="panelsStayOpen-collapseOne">
                            {{ $t('Test Your Knowladge') }}
                        </button>
                    </h2>
                    <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show">
                        <div class="accordion-body">
                            <div class="row">
                                <div class="col-6">
                                    <p>{{ $t('Exams') }}
                                        <FontAwesomeIcon class="text-primary" :icon="faGraduationCap" />
                                    </p>
                                    <div v-if="exams?.length > 0">
                                        <div v-for="(exam, index) in exams" :key="index">
                                            <button v-if="course?.is_enrolled"
                                                class="btn btn-primary w-100 text-white my-2"
                                                @click="startExam(exam.id)">{{ $t('Start') }}
                                                {{ $t('Exam') }} #{{ index + 1 }}</button>
                                        </div>
                                    </div>
                                    <p v-else class="text-muted">{{ $t('No exams available at the moment') }}.</p>
                                </div>
                                <div class="col-6">
                                    <p>{{ $t('Quizzes') }}
                                        <FontAwesomeIcon class="text-warning" :icon="faLightbulb" />
                                    </p>
                                    <div v-if="quizes?.length > 0">
                                        <div v-for="(quiz, index) in quizes" :key="index">
                                            <button v-if="course?.is_enrolled" @click="startQuiz(quiz.id)"
                                                class="btn btn-warning w-100 text-white my-2">{{ $t('Start') }}
                                                {{ $t('Quiz') }} #{{ index + 1 }}</button>
                                        </div>
                                    </div>
                                    <p v-else class="text-muted">{{ $t('No quizzes available at the moment') }}.</p>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>

        </div>

    </section>
</template>

<style scoped lang="scss">
.accordion-button {
    border: 2px solid transparent;
    /* Transparent border initially */
    transition: border-color 0.3s;
    /* Smooth transition for border color */
}

.accordion-button:not(.collapsed) {
    border-color: var(--bs-primary);
    /* Change to primary color when active */
}

.content {
    cursor: pointer;
}

.content-link.active {
    background-color: #f3e9fe;
}

.content-link.view {
    color: #f3e9fe;
    text-decoration: line-through;
}

.progress-bar-container {
    background-color: #f5f5f5;
    border-radius: 5px;
    height: 10px;
    margin-bottom: 10px;
}

.progress-bar {
    background-color: #4caf50;
    height: 100%;
    border-radius: 5px;
}

.chapter-duration {
    position: absolute;
    right: 12px;
    top: 12px;
}

.chapter-title {
    cursor: pointer;
    font-size: 1rem;
}

.title-font {
    font-size: 0.75rem;
    font-weight: 500;
}

.svgEdit {
    padding: 4px;
    border-radius: 12px;
    background: #f7f2f2;
}
</style>

<script setup>
import { defineProps, ref, computed, onMounted } from "vue";
import Swal from "sweetalert2";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useExamStore } from "../stores/exam";
import { useQuizStore } from "../stores/quiz";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faCircle, faGraduationCap, faLightbulb } from "@fortawesome/free-solid-svg-icons";
import Video from "@/icons/video.vue";
import Image from "@/icons/image.vue";
import Document from "@/icons/document.vue";
import Audio from "@/icons/audio.vue";


const authStore = useAuthStore();
const examStore = useExamStore();
const quizStore = useQuizStore();
const route = useRoute();
const router = useRouter();
const activeIndex = ref(0);

let currentId = ref(route.params.content_id);
let chaptersToShow = ref(5);

let props = defineProps({
    chapters: {
        type: Array,
        default: () => [],
    },
    courseId: Number,
    isPlayingVideo: Boolean,
    isPlayingAudio: Boolean,
    course: Object,
    contentData: Object,
    exams: Array,
    quizes: Array,
});

const markContentComplete = (contentId) => {
    const content = findContentById(contentId);
    if (content) {
        content.is_viewed = true;
    }
};

// Helper function to find content by ID
function findContentById(contentId) {
    for (const chapter of props.chapters) {
        for (const content of chapter.contents) {
            if (content.id === contentId) return content;
        }
    }
    return null;
}

// Method to mark a content as completed

const showMoreChapters = () => {
    chaptersToShow.value += 1;
};

const emit = defineEmits([
    "playVideo",
    "pauseVideo",
    "pauseAudio",
    "playAudio",
]);

// Play/Pause Video
const togglePlay = (contentId) => {
    currentId.value = contentId;
    if (props.isPlayingVideo) {
        // Use props.isPlaying instead
        emit("pauseVideo", contentId);
    } else {
        emit("playVideo", contentId);
    }
};

const togglePlayAudio = (contentId) => {
    currentId.value = contentId;
    if (props.isPlayingAudio) {
        // Use props.isPlaying instead
        emit("pauseAudio", contentId);
    } else {
        emit("playAudio", contentId);
    }
};

function formatDuration(duration) {
    if (duration >= 60) {
        const hours = Math.floor(duration / 60);
        const minutes = duration % 60;
        return `${hours} hour${hours > 1 ? "s" : ""}${minutes > 0 ? ` ${minutes} min` : ""
            }`;
    }
    return `${duration} min`;
}

const handleWaring = () => {
    if (!authStore.userData) {
        Swal.fire({
            icon: "error",
            title: "Sorry...",
            text: "You need to log in to access this page.",
            confirmButtonText: "Go to Login",
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "/login";
            }
        });
    }
};

function enrollWarning() {
    Swal.fire({
        icon: "warning",
        title: "Enroll Required",
        text: "You need to enroll the course to view the content",
        showConfirmButton: true,
    });
}

// Method to toggle the accordion
const toggleAccordion = (index) => {
    activeIndex.value = activeIndex.value === index ? -1 : index;
};

const startExam = (examId) => {
    Swal.fire({
        title: "Are you sure?",
        text: "Warning! If you start the exam and submit it, you won't be able to re-take the exam later. Are you sure you want to proceed?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, Start the Exam!"
    }).then((result) => {
        if (result.isConfirmed) {
            examStore.isResetTimer = true;
            router.push(`/exam/${examId}`);
        }
    });
};
const startQuiz = (quizId) => {
    Swal.fire({
        title: "Are you sure?",
        text: "Warning! If you start the quiz and submit it, you won't be able to re-take the quiz later. Are you sure you want to proceed?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, Start the quiz!"
    }).then((result) => {
        if (result.isConfirmed) {
            quizStore.isResetTimer = true;
            router.push(`/quiz/${quizId}`);
        }
    });
};

</script>
