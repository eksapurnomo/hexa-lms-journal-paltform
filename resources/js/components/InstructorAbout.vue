<template>
    <div class="bg-white rounded-3 theme-shadow">
        <div class="border-bottom p-3">
            <small class="d-block text-start text-lg-end text-muted mb-3 mb-lg-0">{{ $t('Member Since') }}: {{
                instructor.joining_date }}</small>
            <img :src="instructor.profile_picture" class="rounded-circle object-fit-cover me-3 mb-3" height="100px"
                width="100px" />
            <h1 class="fw-bold fs-3">{{ instructor.name }}</h1>
            <span>{{ instructor.title
                }}<i class="bi bi-patch-check-fill text-blue ms-2"></i></span>

            <div class="bg-light p-3 rounded-3 mt-3">
                <small class="d-block mb-2">{{ $t('Rating & Reviews') }}</small>
                <i class="bi bi-star-fill text-warning me-2 fs-2"></i>
                <span class="fw-bold fs-2 me-2">{{
                    instructor.average_rating
                    }}</span>
                <span class="text-muted fs-4">({{ instructor.reviews_count }})</span>
            </div>
        </div>
        <div class="row p-3">
            <div class="col-4 border-end px-2 px-lg-4 py-2">
                <i class="ri-book-open-fill fs-4 text-blue p-2 bg-light rounded-circle"></i>
                <small class="d-block mt-3">{{ $t('Courses') }}</small>
                <span class="fw-bold fs-4">{{ instructor.course_count }}</span>
            </div>
            <div class="col-4 border-end px-2 px-lg-4 py-2">
                <i class="ri-contacts-book-fill fs-4 text-warning p-2 bg-light rounded-circle"></i>
                <small class="d-block mt-3">{{ $t('Enrollments') }}</small>
                <span class="fw-bold fs-4">{{ instructor.student_count }}</span>
            </div>
            <div class="col-4 px-2 px-lg-4 py-2">
                <i class="ri-group-2-fill fs-4 text-primary p-2 bg-light rounded-circle"></i>
                <small class="d-block mt-3">{{ $t('Students') }}</small>
                <span class="fw-bold fs-4">{{ instructor.student_count }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps } from "vue";

let props = defineProps({
    instructor: Object,
});
</script>
