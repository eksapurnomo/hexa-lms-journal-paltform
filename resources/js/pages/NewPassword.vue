<template>
    <section class="bg-light vh-100 d-flex align-items-center justify-content-center">
        <section class="login-wizard bg-white col-8 theme-shadow">
            <div class="row">
                <div class="col-6 px-5 py-4">
                    <img :src="'/assets/images/logo-new.png'" class="object-fit-cover" alt="Login" height="50px" />
                    <div class="d-flex h-100">
                        <div class="my-auto w-100">
                            <h3 class="fw-bold mb-3">{{ $t('Set Password') }}</h3>
                            <span class="text-muted">{{ $t('Create a new and strong password that you can remember')
                                }}.</span>

                            <form @submit.prevent="updateProfile" class="my-4">
                                <div class="mb-2">
                                    <input type="password" v-model="password" class="form-control mb-4"
                                        :placeholder="$t('New Password')">
                                    <input type="password" v-model="password_confirmation" class="form-control"
                                        :placeholder="$t('Confirm Password')">
                                </div>
                                <button type="submit" class="btn btn-primary w-100 rounded-pill mt-3">
                                    {{ $t('Save Password') }}</button>
                            </form>

                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <img :src="'/assets/images/login-bg.png'" class="side-image object-fit-cover w-100">
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
import { useAuthStore } from '@/stores/auth'
import { ref } from 'vue'
import Swal from 'sweetalert2'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const baseUrl = import.meta.env.VITE_APP_URL
const authStore = useAuthStore()

const password = ref('')
const password_confirmation = ref('')

// Handle form submission
const updateProfile = async () => {
    try {
        const response = await axios.patch(`/update-password`, {
            password: password.value,
            password_confirmation: password_confirmation.value
        }, {
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + authStore.authToken
            }
        })

        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Password updated successfully',
            showConfirmButton: false,
            timer: 1500
        })

        router.push('/')
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response.data.message,
            showConfirmButton: false,
            timer: 1500
        })
    }
}
</script>
