<template>
    <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
        <button class="nav-link active" id="v-pills-dashboard-tab" data-bs-toggle="pill"
            data-bs-target="#v-pills-dashboard" type="button" role="tab" aria-controls="v-pills-dashboard"
            aria-selected="true"><i class="bi bi-columns-gap me-3"></i>{{ $t('Dashboard') }}</button>
        <button class="nav-link" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile"
            type="button" role="tab" aria-controls="v-pills-profile" aria-selected="false"><i
                class="bi bi-person me-3"></i>{{ $t('Profile') }}</button>
        <button class="nav-link" id="v-pills-courses-tab" data-bs-toggle="pill" data-bs-target="#v-pills-courses"
            type="button" role="tab" aria-controls="v-pills-courses" aria-selected="false"><i
                class="bi bi-book me-3"></i>{{ $t('My Courses') }}</button>
        <button class="nav-link" id="v-pills-certificate-tab" data-bs-toggle="pill"
            data-bs-target="#v-pills-certificate" type="button" role="tab" aria-controls="v-pills-certificate"
            aria-selected="false"><i class="bi bi-award me-3"></i>{{ $t('My Certificate') }}</button>
        <button class="nav-link" id="v-pills-payment-tab" data-bs-toggle="pill" data-bs-target="#v-pills-payment"
            type="button" role="tab" aria-controls="v-pills-payment" aria-selected="false"><i
                class="bi bi-cash-stack me-3"></i>{{ $t('Payment History') }}</button>
        <button @click="logout()" class="nav-link text-danger">
            <i class="bi bi-box-arrow-right me-3"></i>
            {{ $t('Sign out') }}
        </button>
    </div>
</template>

<style lang="scss" scoped>
.nav {
    .nav-link {
        color: #000;
        text-align: left;
        border-radius: .3rem;
        margin-bottom: .5rem;
        ;
    }

    .nav-link.active {
        color: white;
    }
}
</style>

<script setup>
import Swal from 'sweetalert2'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const router = useRouter()
const authStore = useAuthStore()

function logout() {
    Swal.fire({
        title: t("Are you sure?"),
        text: t("Do you want to log out?"),
        icon: t("warning"),
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, log out!"
    }).then((result) => {
        if (result.isConfirmed) {
            authStore.clearAuthData()
            Swal.fire({
                title: t("Logged Out!"),
                text: t("Log out successful."),
                showConfirmButton: false,
                icon: "success",
                timer: 1500
            });
            router.push('/');
        }
    });
}
</script>
