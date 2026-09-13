<template>
    <footer :class="{ 'homepage-footer': $route.path === '/' }" class="container-fluid text-white">
        <section class="container footer-container py-5">
            <div class="row">
                <div class="col-md-8 col-lg-4 mb-5 mb-lg-0">
                    <img class="mb-3 object-fit-contain" :src="masterStore?.masterData?.footer" width="150px"
                        height="60px" />
                    <p class="small text-light col-xl-8">
                        {{ masterStore?.masterData?.footer_description }}
                    </p>
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <a :href="`tel:${masterStore?.masterData?.footer_contact}`" class="text-decoration-none">
                                <i class="bi bi-telephone me-3"></i>
                                {{ masterStore?.masterData?.footer_contact }}
                            </a>
                        </li>
                        <li class="mb-3">
                            <a :href="masterStore?.masterData?.footer_email
                                ? `mailto:${masterStore?.masterData?.footer_email}`
                                : ''
                                " class="text-decoration-none">
                                <i class="bi bi-envelope me-3"></i>
                                {{ masterStore?.masterData?.footer_email }}
                            </a>
                        </li>
                    </ul>

                    <div class="d-flex mt-3 align-items-center gap-3">
                        <div v-for="social in masterStore?.masterData
                            ?.footer_social_icons" :key="social.name">
                            <a :href="social?.url" target="_blank"><i :class="social?.icon"></i></a>
                        </div>
                    </div>
                </div>
                <div
                    class="col-md-4 col-lg-2 mb-5 mb-lg-0 d-md-flex d-lg-block flex-column align-items-end justify-content-start">
                    <h4 class="text-primary fs-6 fw-bold mb-3">{{ $t('Useful Links') }}</h4>
                    <ul class="list-unstyled d-md-flex d-lg-block flex-column align-items-end justify-content-start">
                        <li class="mb-3">
                            <router-link to="/courses" class="text-decoration-none">{{$t('All Courses')}}</router-link>
                        </li>
                        <li class="mb-3">
                            <router-link to="/page/about_us" class="text-decoration-none">{{ $t('About Us') }}</router-link>
                        </li>
                        <li class="mb-3">
                            <router-link to="/faq" class="text-decoration-none">{{ $t('FAQ') }}</router-link>
                        </li>
                        <li class="mb-3">
                            <a href="/admin/register" class="text-decoration-none">{{ $t('Become a Teacher') }}</a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-4 col-lg-3 mb-5 mb-lg-0">
                    <h4 class="text-primary fs-6 fw-bold mb-3">
                        {{ $t('Help & Support') }}
                    </h4>
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <router-link to="/contact-us" class="text-decoration-none">{{$t('Contact Us')}}</router-link>
                        </li>
                        <li class="mb-3">
                            <router-link to="/page/terms_and_conditions" class="text-decoration-none">
                                {{ $t('Terms & Conditions') }}
                            </router-link>
                        </li>
                        <li class="mb-3">
                            <router-link to="/page/privacy_policy" class="text-decoration-none">
                                {{ $t('Privacy Policy') }}
                            </router-link>
                        </li>
                        <li class="mb-3">
                            <a href="/admin/login" target="_blank" class="text-decoration-none">
                                {{ $t('Login to Admin') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div
                    class="col-md-8 col-lg-3 col-xl-3 d-md-flex d-lg-block flex-column align-items-end justify-content-end">
                    <h4 class="text-primary fs-6 fw-bold mb-3">
                        {{ $t('Download Our App') }}
                    </h4>
                    <div class="d-flex">
                        <div class="bg-white rounded p-1 mb-3">
                            <img :src="masterStore?.masterData?.scaner ??
                                'https://quickchart.io/qr?text=' +
                                baseUrl +
                                '/download_app&margin=2&size=110'
                                " class="d-block" alt="Scan QR code" style="width: 150px; height: 120px" />
                            <small class="text-dark d-block text-center">{{ $t('Scan the QR code') }}</small>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <a :href="masterStore?.masterData?.footer_apple_link" target="_blank"><img
                                :src="'/assets/images/website/app-store.png'" alt="App Store" class="me-3" /></a>
                        <a :href="masterStore?.masterData?.footer_google_link" target="_blank"><img
                                :src="'/assets/images/website/play-store.png'" alt="Play Store" /></a>
                    </div>
                </div>
            </div>
        </section>
        <div class="text-center text-light py-4">
            <small>{{ masterStore?.masterData?.credit_text }}</small>
        </div>
    </footer>
</template>

<style lang="scss" scoped>
footer {
    background-color: #0f172a;

    .footer-container {
        border-bottom: 2px solid #1c2a47;
    }

    a {
        color: #fff;
        transition: color 0.2s ease-out;

        &:hover {
            color: #9e4aed;
        }
    }
}

.homepage-footer {
    clip-path: ellipse(90% 88% at 50% 100%);
    margin-top: -13rem;
    padding-top: 12rem;
}

@media (max-width: 1200px) {
    .homepage-footer {
        margin-top: -12rem;
    }
}


</style>

<script setup>
import { useMasterStore } from "@/stores/master";
import { ref } from "vue";

const masterStore = useMasterStore();
const masterData = ref(masterStore.masterData);

const baseUrl = import.meta.env.VITE_APP_URL;
</script>
