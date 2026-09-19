<template>
    <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
        <!-- LMS Menus -->
        <button v-if="isDashboard" class="nav-link active" id="v-pills-dashboard-tab" data-bs-toggle="pill"
            data-bs-target="#v-pills-dashboard" type="button" role="tab" aria-controls="v-pills-dashboard"
            aria-selected="true"><i class="bi bi-columns-gap me-3"></i>{{ $t('Dashboard') }}</button>
        <router-link v-else to="/dashboard" class="nav-link">
            <i class="bi bi-columns-gap me-3"></i>{{ $t('Dashboard') }}
        </router-link>

        <template v-if="isDashboard">
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
        </template>

        <!-- Journal & Submission -->
        <template v-if="authStore.dashboardContext?.submission?.has_submissions">
            <h6 class="px-3 mt-4 mb-2 text-muted text-uppercase" style="font-size: 0.75rem;">Journal & Submission</h6>
            <button v-if="isDashboard" class="nav-link" id="v-pills-submissions-tab" data-bs-toggle="pill" data-bs-target="#v-pills-submissions"
                type="button" role="tab" aria-controls="v-pills-submissions" aria-selected="false">
                <i class="bi bi-file-earmark-text me-3"></i>My Submissions
            </button>
            <router-link v-else to="/dashboard" class="nav-link text-muted" title="Return to Dashboard to view">
                <i class="bi bi-file-earmark-text me-3"></i>My Submissions
            </router-link>
        </template>

        <!-- Journal Membership -->
        <template v-if="authStore.dashboardContext?.memberships?.length > 0">
            <h6 class="px-3 mt-4 mb-2 text-muted text-uppercase" style="font-size: 0.75rem;">Journal Membership</h6>
            <template v-if="isDashboard">
                <button v-for="membership in authStore.dashboardContext.memberships" :key="'mem-'+membership.journal.id" 
                    class="nav-link" :id="'v-pills-membership-' + membership.journal.id + '-tab'" 
                    data-bs-toggle="pill" :data-bs-target="'#v-pills-membership-' + membership.journal.id"
                    type="button" role="tab" :aria-controls="'v-pills-membership-' + membership.journal.id" aria-selected="false">
                    <i class="bi bi-journal-bookmark me-3"></i>{{ membership.journal.title }} — <span class="text-capitalize">{{ membership.role }}</span>
                </button>
            </template>
            <template v-else>
                <router-link v-for="membership in authStore.dashboardContext.memberships" :key="'mem-link-'+membership.journal.id" 
                    to="/dashboard" class="nav-link text-muted" title="Return to Dashboard to view">
                    <i class="bi bi-journal-bookmark me-3"></i>{{ membership.journal.title }} — <span class="text-capitalize">{{ membership.role }}</span>
                </router-link>
            </template>
        </template>

        <!-- Journal Management -->
        <template v-if="authStore.dashboardContext?.managed_journals?.length > 0">
            <h6 class="px-3 mt-4 mb-2 text-muted text-uppercase" style="font-size: 0.75rem;">Journal Management</h6>
            <router-link v-for="mgmt in authStore.dashboardContext.managed_journals" :key="'mgmt-'+mgmt.journal.id" 
                :to="{ name: 'dashboard_journal_workspace', params: { slug: mgmt.journal.slug } }" class="nav-link">
                <i class="bi bi-briefcase me-3"></i>{{ mgmt.journal.title }}
            </router-link>
        </template>

        <button @click="logout()" class="nav-link text-danger mt-4 border-top pt-3">
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
import { computed } from 'vue';
import Swal from 'sweetalert2'
import { useAuthStore } from '@/stores/auth'
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const isDashboard = computed(() => route.path === '/dashboard')

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
