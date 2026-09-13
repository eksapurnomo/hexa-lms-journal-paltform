<template>
    <section class="bg-light vh-100 d-flex align-items-center justify-content-center">
        <section class="login-wizard bg-white col-12 col-lg-8 theme-shadow p-4">
            <div class="row">
                <div class="col-12 col-lg-6 px-5 py-4">
                    <img :src="'/assets/images/logo-new.png'" class="object-fit-cover" alt="Login" height="50px" />
                    <div class="d-flex h-100">
                        <div class="my-auto w-100">
                            <h3 class="fw-bold mb-3">{{ $t('Enter Code') }}</h3>
                            <span class="text-muted">{{ $t('We sent an OTP code to your email address') }} -
                                <strong>{{ email }}</strong></span>

                            <form @submit.prevent="verifyOtp" class="my-4">
                                <div class="otp-fields d-flex mb-4">
                                    <input type="number" class="form-control text-center py-3 me-3" maxlength="1"
                                        v-for="(digit, index) in otp" :key="index" ref="otpInput" v-model="otp[index]"
                                        @input="focusNext($event, index)" placeholder="_" />
                                </div>
                                <span v-if="resendTimeLeft !== '00'" class="d-block mb-3">{{ $t('Resend OTP in') }}
                                    <span class="text-primary">00:{{ resendTimeLeft }}</span></span>
                                <a v-else @click="resendOtp" class="text-primary d-block">{{ $t('Resend OTP') }}</a>
                                <button type="submit" class="btn btn-primary w-100 rounded-pill mt-3">
                                    {{ $t('Confirm OTP') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="d-none d-lg-block col-6">
                    <img :src="'/assets/images/login-bg.png'" class="side-image object-fit-cover w-100" />
                </div>
            </div>
        </section>
    </section>
</template>
<script setup>
import axios from "axios";
import { ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import Swal from "sweetalert2";

const route = useRoute();
const router = useRouter();
const otp = ref(["", "", "", ""]);
const otpInput = ref([]);
const email = ref(route.query.email);
const authStore = useAuthStore();
const resendTimeLeft = ref(59);

setInterval(() => {
    if (resendTimeLeft.value > 0) {
        resendTimeLeft.value--;
        resendTimeLeft.value < 10
            ? (resendTimeLeft.value = "0" + resendTimeLeft.value)
            : resendTimeLeft.value;
    }
}, 1000);

const verifyOtp = async () => {
    try {
        const response = await axios.post(`/reset-password/validate`, {
            otp: otp.value.join(""),
            email: email.value,
        });

        authStore.setAuthData(
            response.data.data.token,
            response.data.data.user
        );

        Swal.fire({
            icon: "success",
            title: $t("OTP Verified"),
            text: $t("You can now change your password"),
            showConfirmButton: false,
            timer: 1500,
        });

        router.push("/new_password");
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Oops...",
            text:
                error.response?.data?.message ||
                $t("Something went wrong. Please try again"),
        });
    }
};

const resendOtp = async () => {
    try {
        axios.post(
            `/reset-password`,
            {
                email: email.value,
            },
            {
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
            }
        );

        Swal.fire({
            icon: "success",
            title: "Success",
            text: $t("Password reset link sent to your email address"),
            showConfirmButton: false,
            timer: 1500,
        });

        resendTimeLeft.value = 59;
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Oops...",
            text:
                error.response?.data?.message ||
                $t("Something went wrong. Please try again"),
        });
    }
};

const focusNext = (event, index) => {
    const input = event.target;
    if (input.value.length === 1) {
        otp.value.splice(index, 1, input.value);

        if (index < otp.value.length - 1) {
            otpInput.value[index + 1].focus();
        }
    }
};
</script>

<style lang="scss" scoped>
.login-wizard {
    border-radius: 2rem;

    .side-image {
        border-top-right-radius: 2rem;
        border-bottom-right-radius: 2rem;
    }

    .otp-fields {

        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }
    }
}
</style>
