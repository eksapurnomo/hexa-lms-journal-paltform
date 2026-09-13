<template>
    <section class="my-2">
        <span class="d-block mb-5">{{ $t('Home') }}/{{ $t('My Certificates') }}</span>
        <h3 class="mb-4">{{ $t('My Certificates') }}</h3>

        <div class="row">
            <div v-for="certificate in certificates" class="col-sm-6 col-lg-3 mb-4" :key="certificate.id">
                <div v-if="certificate.is_completed">
                    <div class="certificate-info text-white align-items-center justify-content-center d-flex rounded-top"
                        :style="'background-image: url(' +
                            '/assets/images/website/certificate-available.png' +
                            ');'
                            ">
                        <div class="text-center px-3">
                            <i class="bi bi-cloud-download-fill text-white display-5 d-block mb-3"></i>
                            <p class="mb-2">
                                {{ $t('Your course has been completed and your certificate is now ready') }}.
                            </p>
                            <a href="#" @click="downloadCertificate(certificate.id)" class="text-white">
                                {{ $t('Tap here to Download') }}
                            </a>
                        </div>
                    </div>
                    <div class="certificate-metadata bg-light rounded-bottom px-3 pt-2 pb-3">
                        <router-link :to="'/instructor/' + certificate.instructor.id"
                            class="small text-decoration-none d-block mb-1">{{ certificate.instructor.name
                            }}</router-link>
                        <router-link :to="'/details/' + certificate.id" class="fs-5 text-hover text-decoration-none">{{
                            shortTitle(certificate.title) }}</router-link>
                    </div>
                </div>
                <div v-else>
                    <div class="certificate-info text-white align-items-center justify-content-center d-flex rounded-top"
                        :style="'background-image: url(' +
                            '/assets/images/website/certificate-unavailable.png' +
                            ');'
                            ">
                        <div class="text-center px-3">
                            <i class="bi bi-lock-fill text-white display-5 d-block mb-3"></i>
                            <p class="mb-0">
                                {{ $t('The certificate will be unlocked when your course has been finished.') }}
                            </p>
                        </div>
                    </div>
                    <div class="certificate-metadata bg-light rounded-bottom px-3 pt-2 pb-3">
                        <router-link :to="'/instructor/' + certificate.instructor.id"
                            class="small text-decoration-none d-block mb-1">{{ certificate.instructor.name
                            }}</router-link>
                        <router-link :to="'/details/' + certificate.id" class="fs-5 text-hover text-decoration-none">{{
                            shortTitle(certificate.title) }}</router-link>
                    </div>
                </div>
            </div>

            <div v-if="certificates.length === 0" class="col-12">
                <div class="text-center text-muted">
                    <i class="bi bi-emoji-frown display-5"></i>
                    <p class="mb-0 mt-3">{{ $t('No Certificate Available') }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<style lang="scss" scoped>
.certificate-info {
    height: 200px;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.certificate-metadata {
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 160px;
}
</style>

<script setup>
import { useAuthStore } from "@/stores/auth";
import { ref } from "vue";
const authStore = useAuthStore();

let certificates = ref({});

axios
    .get("/certificate/list", {
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + authStore.authToken,
        },
    })
    .then((res) => {
        certificates.value = res.data.data.certificate_courses;
    });

function downloadCertificate(certificateId) {
    axios
        .get(`/certificate/show/${certificateId}`, {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + authStore.authToken,
            },
        })
        .then((res) => {
            let baseURL = location.host;
            window.open(res.data.data.url, "_blank");
        });
}

function shortTitle(title) {
    return title.length > 50 ? title.slice(0, 50) + "..." : title;
}
</script>
