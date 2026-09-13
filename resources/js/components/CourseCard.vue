<template>
    <div class="course-card h-100">
        <div class="card border-light theme-shadow overflow-hidden h-100">
            <router-link :to="'/details/' + course.id" class="course-thumbnail-wrapper">
                <img :src="course.thumbnail" class="course-thumbnail card-img-top object-fit-cover" alt="..." />
            </router-link>
            <div class="card-body pb-0">
                <div class="header-metadata">
                    <router-link :to="'/instructor/' + course.instructor.id"
                        class="text-decoration-none small d-block mb-1">{{ course.instructor.name }}
                    </router-link>
                    <router-link :to="`/details/${course.id}`" class="text-decoration-none d-block mb-2">
                        <p class="course-title fw-bold text-hover">
                            {{ shortText(course.title) }}
                        </p>
                    </router-link>
                </div>
                <div
                    class="card-text text-muted d-flex flex-column flex-xl-row gap-2 justify-content-between mb-3 mt-3 mt-md-0">
                    <div class="order-lg-2 order-xl-1" v-if="!course?.is_free && !course?.is_enrolled">
                        <div v-if="course?.price">
                            <strong v-if="masterStore?.masterData?.currency_position == 'Left'"
                                class="text-primary me-2">
                                {{ masterStore?.masterData?.currency_symbol }}{{ course.price }}
                            </strong>
                            <strong v-else class="text-primary me-2">
                                {{ course.price }}{{ masterStore?.masterData?.currency_symbol }}
                            </strong>
                            <span v-if="masterStore?.masterData?.currency_position == 'Left'"
                                class="text-muted text-decoration-line-through">
                                {{ masterStore?.masterData?.currency_symbol }}{{ course.regular_price }}
                            </span>
                            <span v-else class="text-muted text-decoration-line-through">
                                {{ course.regular_price }}{{ masterStore?.masterData?.currency_symbol }}
                            </span>
                        </div>
                        <div v-else>
                            <strong v-if="masterStore?.masterData?.currency_position == 'Left'"
                                class="text-primary me-2">
                                {{ masterStore?.masterData?.currency_symbol }}{{ course.regular_price }}
                            </strong>
                            <strong v-else class="text-muted text-decoration-line-through">
                                {{ course.regular_price }}{{ masterStore?.masterData?.currency_symbol }}
                            </strong>
                        </div>
                    </div>
                    <span v-else class="badge bg-primary order-lg-2 order-xl-1">
                        <strong>
                            {{ course?.is_enrolled ? $t("Enrolled") : $t("Free") }}
                        </strong>
                    </span>

                    <span class="order-md-1 order-xl-2">
                        <i class="bi bi-star-fill me-2 text-warning"></i>
                        <strong>{{ course.average_rating }}

                        </strong>
                        ({{ course.review_count }})
                    </span>
                </div>
            </div>
            <div class="card-footer bg-white border-light py-0">
                <div class="row">
                    <div class="col-12 col-sm-6 text-muted course-metadata py-2">
                        <small class="d-block">
                            {{ formatDuration(course?.total_duration) }}
                        </small>
                        <small class="d-block">
                            {{ course.student_count }} {{ $t("Enrolled") }}
                        </small>
                    </div>
                    <div class="col-12 col-sm-6 my-auto">
                        <router-link :to="`/details/${course.id}`"
                            class="small text-decoration-none text-dark d-flex justify-content-start justify-content-md-end align-items-center gap-2 py-3 py-md-0">
                            {{ $t('Details') }}
                            <i class="bi bi-chevron-right goto-details-icon rounded-circle px-1 text-muted"></i>
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.header-metadata {
    height: 80px;
    overflow: hidden;
}

.course-metadata {
    color: #64748B;
    font-family: Lexend;
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 20px;
    border-right: 1px solid #eaeaea;
}

.course-title {
    overflow: hidden;
    color: #0F172A;
    font-family: Lexend;
    font-size: 16px;
    font-style: normal;
    font-weight: 500;
    line-height: 24px;
}

.goto-details-icon {
    background-color: #eee;
}

.course-card {
    transition: all 0.4s ease-out;

    .course-thumbnail-wrapper {
        height: 180px;
        overflow: hidden;

        .course-thumbnail {
            object-fit: cover;
            width: 100%;
            height: 100%;
            transition: transform 0.2s ease-out;
        }
    }

    .border-light {
        border-color: #eee !important;
    }

    &:hover {
        .border-light {
            transition: all 0.5s ease-in;
            border-color: #ddd !important;
        }

        .course-thumbnail {
            transform: scale(1.1);
        }
    }
}

@media (max-width: 576px) {
    .card-body {
        padding: 0.5rem 0.5rem;
    }

    .card-footer {
        padding: 0.5rem 0.5rem;
    }

    .header-metadata {
        height: 75px;
    }

    .course-metadata {
        border-right: none;
        border-bottom: 1px solid #eaeaea;
        border-top: 1px solid #eaeaea;
    }
}
</style>

<script setup>
// import useMasterStore from ''
import { useMasterStore } from "@/stores/master";

const masterStore = useMasterStore();

// const masterData = masterStore.masterData

const props = defineProps({
    course: Object,
});

function shortText(text) {
    const width = window.innerWidth;
    const maxLength = width <= 576 ? 20 : 60;
    return text.length > maxLength ? text.slice(0, maxLength) + "..." : text;
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
