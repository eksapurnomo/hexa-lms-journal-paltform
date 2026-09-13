<template>
    <!-- About 1 - Bootstrap Brain Component -->
    <section class="py-3 py-md-5">
        <div class="container">
            <div class="row gy-3 gy-md-4 gy-lg-0 align-items-lg-center">
                <div class="col-12 col-lg-6 col-xl-5">
                    <img
                        class="img-fluid rounded"
                        loading="lazy"
                        src="/public/assets/website/teacher.jpg"
                        alt="About 1"
                    />
                </div>
                <div class="col-12 col-lg-6 col-xl-7">
                    <div class="row justify-content-xl-center">
                        <div class="col-12 col-xl-11">
                            <h2 class="mb-3">{{ $t('Why Choose') }} {{ masterStore?.masterData?.name }}?</h2>
                            <p class="lead fs-4 text-secondary mb-3">
                                {{ $t('We help people to build incredible brands and superior products. Our perspective is to furnish outstanding captivating services') }}.
                            </p>
                            <p class="mb-5">
                                {{ $t('We are a fast-growing company, but we have never lost sight of our core values. We believe in collaboration, innovation, and customer satisfaction. We are always looking for new ways to improve our products and services') }}.
                            </p>
                            <div class="row gy-4 gy-md-0 gx-xxl-5X">
                                <div class="col-12 col-md-6">
                                    <div class="d-flex">
                                        <div class="me-4 text-primary">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="32"
                                                height="32"
                                                fill="currentColor"
                                                class="bi bi-gear-fill"
                                                viewBox="0 0 16 16"
                                            >
                                                <path
                                                    d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872l-.1-.34zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"
                                                />
                                            </svg>
                                        </div>
                                        <div>
                                            <h2 class="h4 mb-3">
                                                {{ $t('Trusted Company') }}
                                            </h2>
                                            <p class="text-secondary mb-0">
                                                {{ $t('We are crafting a digital method that subsists life across all mediums') }}.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="d-flex">
                                        <div class="me-4 text-primary">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="32"
                                                height="32"
                                                fill="currentColor"
                                                class="bi bi-fire"
                                                viewBox="0 0 16 16"
                                            >
                                                <path
                                                    d="M8 16c3.314 0 6-2 6-5.5 0-1.5-.5-4-2.5-6 .25 1.5-1.25 2-1.25 2C11 4 9 .5 6 0c.357 2 .5 4-2 6-1.25 1-2 2.729-2 4.5C2 14 4.686 16 8 16Zm0-1c-1.657 0-3-1-3-2.75 0-.75.25-2 1.25-3C6.125 10 7 10.5 7 10.5c-.375-1.25.5-3.25 2-3.5-.179 1-.25 2 1 3 .625.5 1 1.364 1 2.25C11 14 9.657 15 8 15Z"
                                                />
                                            </svg>
                                        </div>
                                        <div>
                                            <h2 class="h4 mb-3">
                                                {{ $t('eLearning Platform') }}
                                            </h2>
                                            <p class="text-secondary mb-0">
                                                {{ $t('We believe in innovation by merging primary with elaborate ideas') }}.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="py-3 py-md-5">
        <div class="container">
            <div class="row gy-3 gy-md-4 gy-lg-0 align-items-lg-center">
                <div class="col-12 col-lg-6">
                    <div class="row justify-content-xl-center">
                        <div class="col-12 col-xl-10">
                            <h2 class="mb-3">{{ $t('Our Success') }}</h2>
                            <p class="lead fs-4 mb-3 mb-xl-5">
                                {{ $t('With years of experience and deep industry knowledge, we have a proven track record of success and are constantly pushing ourselves to stay ahead of the curve') }}.
                            </p>
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3 text-primary">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="28"
                                        height="28"
                                        fill="currentColor"
                                        class="bi bi-check-circle-fill"
                                        viewBox="0 0 16 16"
                                    >
                                        <path
                                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p class="fs-5 m-0">
                                        {{ $t('Our evolution procedure is super intelligent') }}.
                                    </p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3 text-primary">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="28"
                                        height="28"
                                        fill="currentColor"
                                        class="bi bi-check-circle-fill"
                                        viewBox="0 0 16 16"
                                    >
                                        <path
                                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p class="fs-5 m-0">
                                        {{ $t('We deliver services beyond expectations') }}.
                                    </p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-4 mb-xl-5">
                                <div class="me-3 text-primary">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="28"
                                        height="28"
                                        fill="currentColor"
                                        class="bi bi-check-circle-fill"
                                        viewBox="0 0 16 16"
                                    >
                                        <path
                                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p class="fs-5 m-0">
                                        {{ $t("Let's hire us to reach your objectives") }}.
                                    </p>
                                </div>
                            </div>
                            <router-link
                                to="/contact-us"
                                class="btn bsb-btn-xl btn-outline-primary rounded-pill"
                            >
                                {{ $t('Connect Now') }}
                            </router-link>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <img
                        class="img-fluid rounded"
                        loading="lazy"
                        src="/public/assets/website/about2.jpg"
                        alt="About 2"
                    />
                </div>
            </div>
        </div>
    </section>
    <section class="py-3 py-md-5 py-xl-8">
        <div class="container">
            <div class="row justify-content-md-center">
                <div class="col-12 col-md-10 col-lg-8 col-xl-7 col-xxl-6">
                    <h2 class="mb-4 display-5 text-center">{{ $t('Our Best Offers') }}</h2>
                    <p class="text-secondary mb-5 text-center lead fs-4">
                        {{ $t('We pride ourselves on delivering top-notch repair and maintenance solutions to cyclists of all levels') }}.
                    </p>
                    <hr class="w-50 mx-auto mb-5 mb-xl-9 border-dark-subtle" />
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row gy-4 gy-lg-0 align-items-lg-center">
                <div class="col-12">
                    <div
                        class="row justify-content-lg-end justify-content-xxl-around"
                    >
                        <div class="col-12 d-md-flex justify-content-center">
                            <div class="card bg-transparent border-0 mb-4">
                                <div class="card-body p-0">
                                    <h4 class="card-title mb-3">
                                        {{ $t("Our Services") }}
                                    </h4>
                                    <ul
                                        class="list-unstyled m-0 p-0 d-sm-flex flex-sm-wrap"
                                    >
                                        <li
                                            class="py-1 d-flex align-items-center gap-2 col-sm-6"
                                        >
                                            <span class="text-primary">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    fill="currentColor"
                                                    class="bi bi-check-all"
                                                    viewBox="0 0 16 16"
                                                >
                                                    <path
                                                        d="M8.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L2.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093L8.95 4.992zm-.92 5.14.92.92a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 1 0-1.091-1.028L9.477 9.417l-.485-.486z"
                                                    />
                                                </svg>
                                            </span>
                                            <span>{{ $t('Tune-Up Services') }}</span>
                                        </li>
                                        <li
                                            class="py-1 d-flex align-items-center gap-2 col-sm-6"
                                        >
                                            <span class="text-primary">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    fill="currentColor"
                                                    class="bi bi-check-all"
                                                    viewBox="0 0 16 16"
                                                >
                                                    <path
                                                        d="M8.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L2.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093L8.95 4.992zm-.92 5.14.92.92a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 1 0-1.091-1.028L9.477 9.417l-.485-.486z"
                                                    />
                                                </svg>
                                            </span>
                                            <span>{{ $t('Wheel Services') }}</span>
                                        </li>
                                        <li
                                            class="py-1 d-flex align-items-center gap-2 col-sm-6"
                                        >
                                            <span class="text-primary">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    fill="currentColor"
                                                    class="bi bi-check-all"
                                                    viewBox="0 0 16 16"
                                                >
                                                    <path
                                                        d="M8.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L2.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093L8.95 4.992zm-.92 5.14.92.92a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 1 0-1.091-1.028L9.477 9.417l-.485-.486z"
                                                    />
                                                </svg>
                                            </span>
                                            <span>{{ $t('Brake Services') }}</span>
                                        </li>
                                        <li
                                            class="py-1 d-flex align-items-center gap-2 col-sm-6"
                                        >
                                            <span class="text-primary">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    fill="currentColor"
                                                    class="bi bi-check-all"
                                                    viewBox="0 0 16 16"
                                                >
                                                    <path
                                                        d="M8.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L2.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093L8.95 4.992zm-.92 5.14.92.92a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 1 0-1.091-1.028L9.477 9.417l-.485-.486z"
                                                    />
                                                </svg>
                                            </span>
                                            <span>{{ $t('Drivetrain Services') }}</span>
                                        </li>
                                        <li
                                            class="py-1 d-flex align-items-center gap-2 col-sm-6"
                                        >
                                            <span class="text-primary">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    fill="currentColor"
                                                    class="bi bi-check-all"
                                                    viewBox="0 0 16 16"
                                                >
                                                    <path
                                                        d="M8.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L2.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093L8.95 4.992zm-.92 5.14.92.92a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 1 0-1.091-1.028L9.477 9.417l-.485-.486z"
                                                    />
                                                </svg>
                                            </span>
                                            <span>{{ $t('Suspension Services') }}</span>
                                        </li>
                                        <li
                                            class="py-1 d-flex align-items-center gap-2 col-sm-6"
                                        >
                                            <span class="text-primary">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    fill="currentColor"
                                                    class="bi bi-check-all"
                                                    viewBox="0 0 16 16"
                                                >
                                                    <path
                                                        d="M8.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L2.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093L8.95 4.992zm-.92 5.14.92.92a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 1 0-1.091-1.028L9.477 9.417l-.485-.486z"
                                                    />
                                                </svg>
                                            </span>
                                            <span>{{ $t('Frame Services') }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div
                                class="card bg-transparent border-0 mb-4 mb-xxl-5"
                            >
                                <div class="card-body p-0">
                                    <h4 class="card-title mb-3">
                                        {{ $t('Accessory Installation') }}
                                    </h4>
                                    <ul
                                        class="list-unstyled m-0 p-0 d-sm-flex flex-sm-wrap"
                                    >
                                        <li
                                            class="py-1 d-flex align-items-center gap-2 col-sm-6"
                                        >
                                            <span class="text-primary">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    fill="currentColor"
                                                    class="bi bi-check-all"
                                                    viewBox="0 0 16 16"
                                                >
                                                    <path
                                                        d="M8.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L2.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093L8.95 4.992zm-.92 5.14.92.92a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 1 0-1.091-1.028L9.477 9.417l-.485-.486z"
                                                    />
                                                </svg>
                                            </span>
                                            <span>
                                                {{ $t('Handlebar Tape Installation') }}
                                            </span>
                                        </li>
                                        <li
                                            class="py-1 d-flex align-items-center gap-2 col-sm-6"
                                        >
                                            <span class="text-primary">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    fill="currentColor"
                                                    class="bi bi-check-all"
                                                    viewBox="0 0 16 16"
                                                >
                                                    <path
                                                        d="M8.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L2.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093L8.95 4.992zm-.92 5.14.92.92a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 1 0-1.091-1.028L9.477 9.417l-.485-.486z"
                                                    />
                                                </svg>
                                            </span>
                                            <span>{{ $t('Pedal Installation') }}</span>
                                        </li>
                                        <li
                                            class="py-1 d-flex align-items-center gap-2 col-sm-6"
                                        >
                                            <span class="text-primary">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    fill="currentColor"
                                                    class="bi bi-check-all"
                                                    viewBox="0 0 16 16"
                                                >
                                                    <path
                                                        d="M8.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L2.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093L8.95 4.992zm-.92 5.14.92.92a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 1 0-1.091-1.028L9.477 9.417l-.485-.486z"
                                                    />
                                                </svg>
                                            </span>
                                            <span>{{ $t('Fender Installation') }}</span>
                                        </li>
                                        <li
                                            class="py-1 d-flex align-items-center gap-2 col-sm-6"
                                        >
                                            <span class="text-primary">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    fill="currentColor"
                                                    class="bi bi-check-all"
                                                    viewBox="0 0 16 16"
                                                >
                                                    <path
                                                        d="M8.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L2.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093L8.95 4.992zm-.92 5.14.92.92a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 1 0-1.091-1.028L9.477 9.417l-.485-.486z"
                                                    />
                                                </svg>
                                            </span>
                                            <span>
                                                {{ $t('Rack and Pannier Installation') }}
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import {useMasterStore} from "@/stores/master";
const masterStore = useMasterStore();


</script>
