<template>
    <div v-if="masterStore?.masterData?.mode == 'local'" class="powerBy">Powered by RazinSoft &copy;{{ new
        Date().getFullYear() }}</div>
    <div v-if="masterStore?.masterData?.mode == 'local'" class="version-badge">v2.0.0</div>
    <section v-if="!loadder" class="bg-light d-flex align-items-center justify-content-center flex-grow-1">
        <section class="login-wizard bg-white col-12 col-lg-8 theme-shadow p-4 h-100">
            <div class="row">
                <div class="col-12 col-lg-6 px-5 py-3">
                    <div class="text-center logo-img">
                        <router-link to="/"><img :src="masterStore?.masterData?.logo" class="object-fit-cover"
                                alt="Login" /></router-link>
                    </div>
                    <div class="d-flex h-75 pb-3">
                        <div class="my-auto w-100">
                            <h3 class="fw-bold mb-3">{{ $t('Login') }}</h3>
                            <span class="text-muted">{{ $t('Boost your skill always and forever') }}.</span>

                            <form class="my-4" @submit.prevent="loginUser">
                                <div class="mb-4">
                                    <input type="email" v-model="email" class="form-control"
                                        :placeholder="$t('Email Address')" />
                                    <p v-if="errors.email" class="text-danger fw-bold mt-2">
                                        {{ errors.email[0] }}
                                    </p>
                                </div>
                                <div class="mb-3 position-relative">
                                    <input :type="showPassword ? 'text' : 'password'" v-model="password"
                                        class="form-control" :placeholder="$t('Password')" />
                                    <p v-if="errors.password" class="text-danger fw-bold mt-2">
                                        {{ errors.password[0] }}
                                    </p>
                                    <div class="eye-icon" @click="showPassword = !showPassword">
                                        <FontAwesomeIcon :icon="showPassword ? faEye : faEyeSlash" />
                                    </div>
                                </div>
                                <router-link to="/reset_password" class="small d-block text-decoration-none mb-4">
                                    {{ $t('Forgot your password') }}?</router-link>
                                <button type="submit" class="btn btn-primary w-100 rounded-pill">
                                    {{ loginBtnText }}
                                </button>
                            </form>

                            <span>{{ $t("Don't have an account") }}?
                                <router-link to="/register">
                                    {{ $t('Sign Up') }}
                                </router-link>
                            </span>


                            <div v-if="masterStore?.masterData?.mode == 'local'"
                                class="border p-3 d-flex align-items-center justify-content-between rounded-4 my-3">
                                <div>
                                    <strong>{{ $t('Email') }}:</strong> user@readylms.com <br>
                                    <strong>{{ $t('Password') }}:</strong> secret@123
                                </div>
                                <button @click="copyDemoCredentials('user@readylms.com', 'secret@123')"
                                    class="btn btn-sm btn-outline-primary small float-end">{{ $t('Copy') }}</button>
                            </div>
                            <!-- <div class="mt-5 border-top py-5">
                                <span>Are you a Teacher?</span>
                                <button class="btn btn-outline-primary text-dark rounded-pill py-2 px-3 ms-3">Join as a
                                    Teacher</button>
                            </div> -->
                        </div>
                    </div>
                </div>
                <div class="col-6 d-none d-lg-block my-auto">
                    <img :src="'/assets/images/website/image.png'" class="side-image object-fit-cover w-100" />
                </div>
            </div>
        </section>
    </section>

    <div class="loading" v-else>
        <svg class="pl" width="240" height="240" viewBox="0 0 240 240">
            <circle class="pl__ring pl__ring--a" cx="120" cy="120" r="105" fill="none" stroke="#000" stroke-width="20"
                stroke-dasharray="0 660" stroke-dashoffset="-330" stroke-linecap="round"></circle>
            <circle class="pl__ring pl__ring--b" cx="120" cy="120" r="35" fill="none" stroke="#000" stroke-width="20"
                stroke-dasharray="0 220" stroke-dashoffset="-110" stroke-linecap="round"></circle>
            <circle class="pl__ring pl__ring--c" cx="85" cy="120" r="70" fill="none" stroke="#000" stroke-width="20"
                stroke-dasharray="0 440" stroke-linecap="round"></circle>
            <circle class="pl__ring pl__ring--d" cx="155" cy="120" r="70" fill="none" stroke="#000" stroke-width="20"
                stroke-dasharray="0 440" stroke-linecap="round"></circle>
        </svg>
    </div>
</template>

<style lang="scss" scoped>
.eye-icon {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    cursor: pointer;
}

.login-wizard {
    border-radius: 2rem;

    .side-image {
        border-top-right-radius: 2rem;
        border-bottom-right-radius: 2rem;
    }
}

.logo-img {
    img {
        max-height: 90px;
        object-fit: cover;
    }
}

.loading {
    height: 100vh;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: hsl(220deg 29% 90% / 50%);
}

.pl {
    width: 6em;
    height: 6em;
}

.pl__ring {
    animation: ringA 2s linear infinite;
}

.pl__ring--a {
    stroke: #f42f25;
}

.pl__ring--b {
    animation-name: ringB;
    stroke: #f49725;
}

.pl__ring--c {
    animation-name: ringC;
    stroke: #255ff4;
}

.pl__ring--d {
    animation-name: ringD;
    stroke: #f42582;
}

/* Animations */
@keyframes ringA {

    from,
    4% {
        stroke-dasharray: 0 660;
        stroke-width: 20;
        stroke-dashoffset: -330;
    }

    12% {
        stroke-dasharray: 60 600;
        stroke-width: 30;
        stroke-dashoffset: -335;
    }

    32% {
        stroke-dasharray: 60 600;
        stroke-width: 30;
        stroke-dashoffset: -595;
    }

    40%,
    54% {
        stroke-dasharray: 0 660;
        stroke-width: 20;
        stroke-dashoffset: -660;
    }

    62% {
        stroke-dasharray: 60 600;
        stroke-width: 30;
        stroke-dashoffset: -665;
    }

    82% {
        stroke-dasharray: 60 600;
        stroke-width: 30;
        stroke-dashoffset: -925;
    }

    90%,
    to {
        stroke-dasharray: 0 660;
        stroke-width: 20;
        stroke-dashoffset: -990;
    }
}

@keyframes ringB {

    from,
    12% {
        stroke-dasharray: 0 220;
        stroke-width: 20;
        stroke-dashoffset: -110;
    }

    20% {
        stroke-dasharray: 20 200;
        stroke-width: 30;
        stroke-dashoffset: -115;
    }

    40% {
        stroke-dasharray: 20 200;
        stroke-width: 30;
        stroke-dashoffset: -195;
    }

    48%,
    62% {
        stroke-dasharray: 0 220;
        stroke-width: 20;
        stroke-dashoffset: -220;
    }

    70% {
        stroke-dasharray: 20 200;
        stroke-width: 30;
        stroke-dashoffset: -225;
    }

    90% {
        stroke-dasharray: 20 200;
        stroke-width: 30;
        stroke-dashoffset: -305;
    }

    98%,
    to {
        stroke-dasharray: 0 220;
        stroke-width: 20;
        stroke-dashoffset: -330;
    }
}

@keyframes ringC {
    from {
        stroke-dasharray: 0 440;
        stroke-width: 20;
        stroke-dashoffset: 0;
    }

    8% {
        stroke-dasharray: 40 400;
        stroke-width: 30;
        stroke-dashoffset: -5;
    }

    28% {
        stroke-dasharray: 40 400;
        stroke-width: 30;
        stroke-dashoffset: -175;
    }

    36%,
    58% {
        stroke-dasharray: 0 440;
        stroke-width: 20;
        stroke-dashoffset: -220;
    }

    66% {
        stroke-dasharray: 40 400;
        stroke-width: 30;
        stroke-dashoffset: -225;
    }

    86% {
        stroke-dasharray: 40 400;
        stroke-width: 30;
        stroke-dashoffset: -395;
    }

    94%,
    to {
        stroke-dasharray: 0 440;
        stroke-width: 20;
        stroke-dashoffset: -440;
    }
}

@keyframes ringD {

    from,
    8% {
        stroke-dasharray: 0 440;
        stroke-width: 20;
        stroke-dashoffset: 0;
    }

    16% {
        stroke-dasharray: 40 400;
        stroke-width: 30;
        stroke-dashoffset: -5;
    }

    36% {
        stroke-dasharray: 40 400;
        stroke-width: 30;
        stroke-dashoffset: -175;
    }

    44%,
    50% {
        stroke-dasharray: 0 440;
        stroke-width: 20;
        stroke-dashoffset: -220;
    }

    58% {
        stroke-dasharray: 40 400;
        stroke-width: 30;
        stroke-dashoffset: -225;
    }

    78% {
        stroke-dasharray: 40 400;
        stroke-width: 30;
        stroke-dashoffset: -395;
    }

    86%,
    to {
        stroke-dasharray: 0 440;
        stroke-width: 20;
        stroke-dashoffset: -440;
    }
}

@media (max-width: 576px) {
    .logo-img img {
        width: 100%;
        height: auto;
    }
}

.version-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: #5864ff;
    color: #fff;
    font-size: 14px;
    font-weight: bold;
    padding: 5px 20px;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.powerBy {
    position: absolute;
    bottom: 10px;
    left: 10px;
    color: #5864ff;
    font-size: 14px;
    font-weight: bold;
    padding: 5px 20px;
    border-radius: 4px;
}
</style>

<script setup>
import axios from "axios";
import Swal from "sweetalert2";
import { onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useMasterStore } from "@/stores/master";
import { useI18n } from "vue-i18n";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faArrowLeft, faEye, faEyeSlash } from "@fortawesome/free-solid-svg-icons";

let errors = ref("");
const loadder = ref(false);

const router = useRouter();
const authStore = useAuthStore();
const masterStore = useMasterStore();

const email = ref("");
const password = ref("");
const loginBtnText = ref("Sign in");
const { t } = useI18n();
let showPassword = ref(false);

// Function to handle login
const loginUser = async () => {
    try {
        loadder.value = true;
        loginBtnText.value = "Signing in...";
        const response = await axios.post(`/login`, {
            email: email.value,
            password: password.value,
        });

        // Store user data and auth token in state
        authStore.setAuthData(
            response.data.data.token,
            response.data.data.user
        );

        Swal.fire({
            icon: "success",
            title: "Success",
            text: "Login successful",
            showConfirmButton: false,
            timer: 1500,
        });

        // Redirect to dashboard or other page
        if(localStorage.getItem("handle_course_id")){
            router.push("/checkout/"+localStorage.getItem("handle_course_id"));
            localStorage.removeItem("handle_course_id");
        }else{
            router.push("/dashboard");
        }

    } catch (error) {
        loadder.value = false;
        if (error?.response?.data.errors) {
            errors.value = error?.response?.data.errors;
        } else {
            errors.value = error?.response?.data.message;
        }

        loginBtnText.value = "Sign in";

        if (error?.response?.status === 403) {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text:
                    error.response?.data?.message ||
                    "Login failed. Please try again.",
            });
        }
    }
};


const copyDemoCredentials = (demoEmail, demoPassword) => {
    email.value = demoEmail
    password.value = demoPassword

    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });
    Toast.fire({
        icon: "success",
        title: t("Demo credentials copied Successfully!!")
    });
}


onMounted(async () => {
    if (!masterStore.data) {
        axios
            .get(`/master`, {
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
            })
            .then((response) => {
                masterStore.setMasterData(response.data.data.master);
            })
            .catch((error) => {
                console.error("Error fetching data:", error);
            });
    }
})

</script>
