<template>
    <div
        class="call-to-action col-12 col-lg-12 mx-auto row align-items-center rounded-5 px-2 px-md-5 py-4 py-md-5 mb-5 mb-lg-auto">
        <div class="col-lg-6 text-center left-section">
            <img class="instructor-img" :src="'/assets/images/website/become-instructor.png'"
                :alt="$t('Become instructor')" />
            <div class="info">
                <p class="text-start text-muted"><span>{{ masterStore?.masterData?.total_featured_instructors }}+</span>
                    {{ $t('Top Rated') }}<br>{{ $t('Instructors') }}</p>
            </div>
            <div class="other-profiles">
                <div class="profiles">
                    <img v-for="instructor in masterStore?.masterData?.instructors" :src="instructor?.profile_picture"
                        :alt="instructor?.name">
                </div>
                <p class="text-start text-muted">{{ $t('Join over') }} <span>{{
                    masterStore?.masterData?.total_instructors
                        }}+</span> <br> {{ $t('Instructors') }}</p>
            </div>
        </div>
        <div class="col-lg-6">
            <h3 class="fs-1 fw-bold mt-4 mt-lg-0 mb-4">
                {{ $t('Become an Instructor and Teach Online') }}
            </h3>
            <p class="mb-5">
                {{ $t('Helping employees gain skills and providing career development often take a back seat to business priorities but workplace') }}.
            </p>
            <a href="/admin/register" target="_blank" class="btn btn-primary rounded-pill">{{ $t('Join Now') }}</a>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.instructor-img {
    width: 391px;
    height: 467px;
}

.call-to-action {
    box-shadow: 0px 16px 64px 0px #00000014;
}

.left-section {
    position: relative;
}

.left-section span {
    font-weight: 700;
}

.left-section .info {
    position: absolute;
    top: -40px;
    left: 20%;
    transform: rotate(-90deg) translate(-50%, -50%);
    font-size: 20px;
    font-weight: 500;
}

.left-section .other-profiles {
    position: absolute;
    right: 18%;
    bottom: -12px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: flex-start;


    .profiles {
        display: inline-block;
        gap: 10px;

        img {
            margin-right: -8px;
            border: 2px solid #fff;
            width: 25px;
            height: 25px;
            border-radius: 50%;
        }
    }
}

@media(max-width: 620px) {
    .left-section .info {
        display: none;
    }

    .left-section .other-profiles[data-v-eff72368] {
        display: none;
    }
}
</style>

<script setup>
import { ref } from "vue";
import { useMasterStore } from "@/stores/master";

const masterStore = useMasterStore();
</script>
