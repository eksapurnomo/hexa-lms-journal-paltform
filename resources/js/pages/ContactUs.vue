<template>
    <!-- Registration 4 - Bootstrap Brain Component -->
    <section class="p-3 p-md-4 p-xl-5">
        <div class="container">
            <div class="card border-light-subtle shadow-sm">
                <div class="row g-0">
                    <div class="col-12 col-md-6">
                        <img
                            class="rounded-start w-100 h-100 object-fit-cover"
                            loading="lazy"
                            src="/public/assets/website/contact.jpg"
                            alt="lms logo Logo"
                        />
                    </div>
                    <div class="col-12 col-md-6 my-auto">
                        <div class="card-body p-3 p-md-4 p-xl-5">
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-5">
                                        <h2 class="h3">{{ $t('Connect with us') }}</h2>
                                    </div>
                                </div>
                            </div>
                            <form action="#!">
                                <div class="row gy-3 gy-md-4 overflow-hidden">
                                    <div class="col-12">
                                        <label
                                            for="firstName"
                                            class="form-label"
                                            >{{ $t('Full Name') }}
                                            <span class="text-danger"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            type="text"
                                            class="form-control"
                                            name="firstName"
                                            id="firstName"
                                            :placeholder="$t('First Name')"
                                            v-model="form.name"
                                            required
                                        />
                                    </div>
                                    <div class="col-12">
                                        <label for="email" class="form-label"
                                            >{{ $t('Email') }}
                                            <span class="text-danger"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            type="email"
                                            class="form-control"
                                            name="email"
                                            id="email"
                                            placeholder="name@example.com"
                                            v-model="form.email"
                                            required
                                        />
                                    </div>
                                    <div class="col-12">
                                        <label for="subject" class="form-label"
                                            >{{ $t('Subject') }}
                                            <span class="text-danger"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            type="text"
                                            class="form-control"
                                            name="subject"
                                            id="subject"
                                            :placeholder="$t('Subject')"
                                            v-model="form.subject"
                                            required
                                        />
                                    </div>
                                    <div class="col-12">
                                        <label for="message" class="form-label"
                                            >{{ $t('Message') }}
                                            <span class="text-danger"
                                                >*</span
                                            ></label
                                        >
                                        <textarea
                                            rows="4"
                                            id="message"
                                            class="form-control"
                                            v-model="form.message"
                                        ></textarea>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button
                                                class="btn bsb-btn-xl btn-primary"
                                                type="button"
                                                @click="submitForm"
                                            >
                                                {{ $t('connect now') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="row">
                                <div class="col-12">
                                    <p class="mt-5 mb-4">
                                        {{ $t('Are you trying to connect with us on social media') }}?
                                    </p>
                                    <div
                                        class="d-flex gap-3 flex-column flex-xl-row flex-wrap justify-content-start"
                                    >
                                        <a
                                            v-for="social in masterStore
                                                ?.masterData
                                                ?.footer_social_icons"
                                            :key="social"
                                            :href="social.url"
                                            target="_blank"
                                            class="btn bsb-btn-xl btn-outline-primary"
                                        >
                                            <i :class="social?.icon"></i>
                                            <span class="ms-2 fs-6">{{
                                                social?.title
                                            }}</span>
                                        </a>
                                    </div>
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
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";
import { useMasterStore } from "@/stores/master";
import axios from "axios";

const masterStore = useMasterStore();

let form = ref({
    name: "",
    email: "",
    subject: "",
    message: "",
});

const resetForm = () => {
    form.value = {
        name: "",
        email: "",
        subject: "",
        message: "",
    };
};

const submitForm = async () => {

    try {
        const response = await axios.post(`/contact/submit`, form.value, {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
            },
        });

        if (response.status == 201) {
            resetForm();
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: $t("Your inquiry has been submitted"),
                showConfirmButton: false,
                timer: 1500,
                customClass: {
                    title: "swal-title",
                },
            });
        }
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: `Error submitting form:, ${error.response.data.message}`,
            showConfirmButton: false,
            timer: 3500,
            customClass: {
                title: "swal-title",
            },
        });
        console.error("Error submitting form:", error);
    }
};
</script>

<style>
.bg-purple {
    background-color: #5c4ac7; /* Dark purple background */
}

body {
    background-color: #f4f4f4; /* Light gray background */
}

.btn-light {
    background-color: #ffffff;
    color: #5c4ac7; /* Match the dark purple theme */
}
.swal-title {
    font-size: 1rem; /* Adjust font size as needed */
    font-weight: bold; /* Optional: Make it bold */
}

@media (max-width: 768px) {
    .bg-purple {
        padding: 20px;
    }
}
</style>
