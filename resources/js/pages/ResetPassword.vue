<template>
    <section class="bg-light vh-100 d-flex align-items-center justify-content-center" style="height: 100vh">
        <section class="login-wizard bg-white col-12 col-lg-8 theme-shadow">
            <div class="row">
                <div class="col-12 col-lg-6 px-5 py-4">
                    <img :src="'/assets/images/logo-new.png'" class="object-fit-cover" alt="Login" height="50px" />
                    <div class="d-flex h-100">
                        <div class="my-auto w-100">
                            <h3 class="fw-bold mb-3">{{ $t('Recover Password') }}</h3>
                            <span class="text-muted">{{ $t('We will send you a OTP code to recover your password')
                                }}</span>

                            <form class="my-4" @submit.prevent="sendOtp">
                                <div class="mb-2">
                                    <input type="email" v-model="email" class="form-control"
                                        :placeholder="$t('Email address')" />
                                    <p v-if="validError" class="text-danger my-2">
                                        {{ validError }}
                                    </p>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 rounded-pill mt-3">
                                    {{ $t('Proceed Next') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="d-none d-lg-block col-lg-6">
                    <img :src="'/assets/images/login-bg.png'" class="side-image object-fit-cover w-100" />
                </div>
            </div>
        </section>
    </section>
</template>

<style lang="scss" scoped>
.login-wizard {
    border-radius: 2rem;

    .side-image {
        border-top-right-radius: 2rem;
        border-bottom-right-radius: 2rem;
    }
}
</style>

<script setup>
import { useRouter } from "vue-router";
import axios from "axios";
import Swal from "sweetalert2";
import { ref, watch } from "vue";

const router = useRouter();
const email = ref("");
const validError = ref("");

watch(() => {
    if (email.value) {
        validError.value = "";
    }
});

const sendOtp = async () => {
    const response = await axios.post(
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

    if (response?.data?.data === 500) {
        validError.value = response?.data?.message;
    } else {
        Swal.fire({
            icon: "success",
            title: "Success",
            text: "Password reset link sent to your email address",
            showConfirmButton: false,
            timer: 1500,
        });

        router.push(`/verify_otp?email=${email.value}`);
    }
};
</script>
