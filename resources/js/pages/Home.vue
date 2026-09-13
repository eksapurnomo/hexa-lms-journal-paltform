<template>
    <Hero />
    <section class="pb-5">
        <div class="container py-0 py-lg-2">
            <About />
        </div>
    </section>

    <section class="py-2">
        <div class="container py-0 py-lg-5">
            <h3 class="fw-bold text-center fs-2 mb-4">
                {{ $t('Explore Our Popular Courses') }}
            </h3>
            <PopularCourses />
        </div>
    </section>

    <section class="py-2 border-bottom">
        <div class="container py-0 py-lg-5">
            <FeaturedCategories />
        </div>
    </section>

    <section class="py-2 border-bottom">
        <div class="container py-0 py-lg-5">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h3 class="fw-bold fs-2">{{ $t('Newest Courses') }}</h3>
                    <p>{{ $t('Recently published courses') }}</p>
                </div>
                <router-link to="/courses" class="text-primary text-decoration-none fw-bold">{{ $t('View All') }}
                    <font-awesome-icon :icon="faArrowRightLong" /></router-link>
            </div>
            <LatestCourseSlider />
        </div>
    </section>

    <section class="py-2">
        <div class="container py-0 py-lg-5">
            <div class="row">
                <div class="col-12">
                    <h3 class="fw-bold text-center fs-2 mb-5">
                        {{ $t('Top Rated Instructors') }}
                    </h3>
                </div>
            </div>
            <FeaturedInstructors />
        </div>
    </section>

    <section class="py-5 py-lg-2 bg-light-purple border-top border-bottom">
        <div class="container py-5">
            <div class="row">
                <div class="col-12">
                    <h3 class="fw-bold text-center fs-2 mb-5">
                        {{ $t('Hear from Our Students and Instructors') }}
                    </h3>
                </div>
            </div>
            <Testimonials />
        </div>
    </section>

    <section class="pt-5 pb-0">
        <div class="container py-0 py-lg-5">
            <FAQ />
        </div>
    </section>

    <section class="py-0 py-lg-2">
        <div class="container py-5">
            <BecomeInstructor />
        </div>
    </section>

    <section class="pt-0 pt-lg-5">
        <div class="container">
            <Newsletter />
        </div>
    </section>
</template>


<script setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faArrowRightLong } from "@fortawesome/free-solid-svg-icons";
import Hero from "../components/Hero.vue";
import About from "../components/About.vue";
import FeaturedCategories from "../components/FeaturedCategories.vue";
import FeaturedInstructors from "../components/FeaturedInstructors.vue";
import FAQ from "../components/FAQ.vue";
import Testimonials from "../components/Testimonials.vue";
import BecomeInstructor from "../components/BecomeInstructor.vue";
import Newsletter from "../components/Newsletter.vue";
import PopularCourses from "../components/PopularCourses.vue";
import LatestCourseSlider from "../components/LatestCourseSlider.vue";
</script>
