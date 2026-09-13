<template>
    <section class="pt-3 pt-lg-0 pb-3 hero">
        <img :src="'/assets/images/website/hero-fancy-circle.svg'" class="position-fixed" />
        <section class="container">
            <div class="row pb-5">
                <div class="col-lg-7 text-center text-lg-start my-auto mb-5 mb-lg-auto">
                    <span class="bg-white px-3 py-2 fw-bold rounded-pill">{{ $t('Hi Learners') }}</span>
                    <h3 class="display-5 fw-bold my-3 my-md-4 hero-title">
                        {{ $t('Discover Your Perfect Learning Path') }}
                    </h3>
                    <span class="d-block mb-4 mx-auto mx-lg-0">
                        {{ $t('Hand-picked Instructor and expertly crafted courses designed for the modern students and entrepreneurs') }}.
                    </span>
                    <div class="col-xl-10">
                        <form @submit.prevent="performSearch" class="input-group" role="search">
                            <input v-model="searchInputQuery" class="form-control search-input border-0 search-input"
                                type="search" :placeholder="$t('Search Course')" />
                            <div class="bg-white search-btn-wrapper">
                                <button type="submit" class="btn btn-primary border-3 border-white py-2">
                                    <i class="ri-search-2-line fs-4"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-5 pt-0 pt-md-0 mt-0 mt-md-0">
                    <div class="position-relative">
                        <div class="student-summary text-white p-4" :class="{
                            'animate-student': isStudentSummaryAnimated,
                        }">
                            <div class="d-flex mb-2">
                                <img :src="'/assets/images/website/student_1.png'"
                                    class="rounded-circle student-img object-fit-cover" width="30px" height="30px" />
                                <img :src="'/assets/images/website/student_2.png'"
                                    class="rounded-circle student-img object-fit-cover secondary" width="30px"
                                    height="30px" />
                                <span class="student-count bg-white text-dark rounded-circle secondary">{{
                                    masterStore?.masterData?.total_enrollments
                                    }}+</span>
                            </div>
                            <h3 class="fw-bold">
                                {{
                                    masterStore?.masterData?.total_enrollments
                                }}+
                            </h3>
                            <span>{{ $t('Enrolled Students') }}</span>
                        </div>

                        <div class="instructor-summary p-4" :class="{
                            'animate-instructor':
                                isInstructorSummaryAnimated,
                        }">
                            <i class="bi bi-star-fill text-warning fs-3"></i>
                            <span><span class="d-block"><strong>{{
                                masterStore?.masterData
                                    ?.total_instructors
                                        }}+</strong>
                                    {{ $t('Top Rated') }}</span>
                                {{ $t('Instructors') }}</span>
                        </div>

                        <div class="course-summary text-white p-4" :class="{
                            'animate-course': isCourseSummaryAnimated,
                        }">
                            <h3 class="fw-bold fs-2">
                                {{ masterStore?.masterData?.total_courses }}+
                            </h3>
                            <span>{{ $t('Premium Courses') }}</span>
                        </div>

                        <img :src="'/assets/images/website/reaction-illustration.png'" class="reaction-illustration"
                            :class="{
                                'animate-reaction':
                                    isReactionIllustrationAnimated,
                            }" />
                        <img :src="'/assets/images/website/readylms-hero.webp'" loading="lazy" class="w-100"
                            :alt="$t('Hero Illustration')" />
                    </div>
                </div>
            </div>
        </section>
    </section>
</template>

<style scoped lang="scss">
.hero {
    clip-path: ellipse(118% 90% at 50% 0%);
    overflow: hidden;
    background-color: #f3e9fe;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;

    .search-input {
        border-top-left-radius: 50px;
        border-bottom-left-radius: 50px;

        &::placeholder {
            color: #868686;
        }
    }

    .search-btn-wrapper {
        border-top-right-radius: 50px;
        border-bottom-right-radius: 50px;

        button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    }

    .animate-student {
        animation: studentAnimation 5s ease-in-out infinite;
    }

    .animate-instructor {
        animation: instructorAnimation 5s ease-in-out infinite;
    }

    .animate-course {
        animation: courseAnimation 5s ease-in-out infinite;
    }

    .animate-reaction {
        animation: reactionAnimation 5s ease-in-out infinite;
    }

    @keyframes studentAnimation {
        0% {
            transform: translateY(0);
        }

        40% {
            transform: translateY(-20px);
        }

        100% {
            transform: translateY(0);
        }
    }

    @keyframes instructorAnimation {
        0% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px) translateX(-15px);
        }

        100% {
            transform: translateY(0);
        }
    }

    @keyframes courseAnimation {
        0% {
            transform: translateY(0);
        }

        60% {
            transform: translateY(-10px);
        }

        100% {
            transform: translateY(0);
        }
    }

    @keyframes reactionAnimation {
        0% {
            transform: translateY(0);
        }

        25% {
            transform: translateX(-10px) translateY(-10px) scale(1.05);
        }

        50% {
            transform: translateX(-10px) translateY(-10px) scale(1.1);
        }

        100% {
            transform: translateY(0) scale(1);
        }
    }


    .student-summary {
        background: #6691fe;
        border-radius: 25px;
        border-bottom-right-radius: 0;
        position: absolute;
        top: 120px;
        left: 42px;
        transition: 0.5s ease-in-out;

        .student-img {
            border: 1px solid #6691fe;
        }

        .student-count {
            border: 1px solid #6691fe;
            padding: 5px 3px 5px 3px;
            font-size: 12px;
        }

        .secondary {
            margin-left: -5px;
        }
    }

    .instructor-summary {
        background: #fff;
        border-radius: 25px;
        position: absolute;
        top: 466px;
        left: 12px;
        transition: 0.5s ease-in-out;
    }

    .course-summary {
        background: #25c5ba;
        border-radius: 25px;
        border-top-left-radius: 0;
        position: absolute;
        top: 488px;
        right: -61px;
        transition: 0.5s ease-in-out;
    }

    .reaction-illustration {
        position: absolute;
        top: 133px;
        right: -17px;
        transition: 0.5s ease-in-out;
    }
}


.hero-title {
    width: 80%;
}

@media (max-width: 1600px) {
    .hero-title {
        width: 100%;
    }

    .student-summary {
        top: 30px !important;
        left: -15px !important;
    }

    .course-summary {
        top: 429px !important;
        right: 8px !important;
    }

    .instructor-summary {
        top: 331px !important;
        left: 30px !important;
    }
}

@media (max-width: 1024px) {
    .hero .student-summary {
        top: 30px !important;
        left: 15px !important;
    }

    .hero .course-summary {
        top: 700px !important;
    }

    .hero .instructor-summary {
        top: 420px !important;
        left: 30px !important;
    }
}

@media (max-width: 992px) {
    .hero-title {
        width: 100%;
    }
}



@media (max-width: 576px) {
    .student-summary {
       display: none;
    }

    .instructor-summary {
        top: 273px !important;
    }

    .course-summary {
        display: none;
    }

    .reaction-illustration {
        top: 25px !important;
        right: 10px !important;
    }

}
</style>

<script setup>
import { useRouter } from "vue-router";
import { ref } from "vue";
import { useMasterStore } from "@/stores/master";

const searchInputQuery = ref("");
const router = useRouter();
const masterStore = useMasterStore();

const isStudentSummaryAnimated = ref(true);
const isInstructorSummaryAnimated = ref(true);
const isCourseSummaryAnimated = ref(true);
const isReactionIllustrationAnimated = ref(true);

const performSearch = () => {
    if (searchInputQuery.value) {
        router.push(`/courses?search=${searchInputQuery.value}`);
    }
};
</script>
