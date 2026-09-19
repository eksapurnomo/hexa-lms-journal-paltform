<template>
    <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
        
        <h6 class="px-3 mt-4 mb-2 text-muted text-uppercase" style="font-size: 0.75rem;">Main</h6>
        
        <router-link to="/author" class="nav-link" exact-active-class="active">
            <i class="bi bi-columns-gap me-3"></i>Overview
        </router-link>
        
        <router-link to="/author/submissions" class="nav-link" :class="{ 'active': $route.path.startsWith('/author/submissions') && $route.path !== '/author/submissions/create' }">
            <i class="bi bi-file-earmark-text me-3"></i>Submissions
        </router-link>

        <router-link to="/author/submissions/create" class="nav-link" active-class="active">
            <i class="bi bi-file-earmark-plus me-3"></i>New Submission
        </router-link>

        <router-link to="/author/journals" class="nav-link" active-class="active">
            <i class="bi bi-journal-bookmark me-3"></i>Journals
        </router-link>

        <h6 class="px-3 mt-4 mb-2 text-muted text-uppercase" style="font-size: 0.75rem;">Account</h6>
        
        <router-link to="/author/profile" class="nav-link" active-class="active">
            <i class="bi bi-person me-3"></i>Profile & Security
        </router-link>

        <h6 class="px-3 mt-4 mb-2 text-muted text-uppercase" style="font-size: 0.75rem;">Navigation</h6>

        <router-link to="/dashboard" class="nav-link text-muted">
            <i class="bi bi-arrow-left-circle me-3"></i>Back to Dashboard
        </router-link>
        
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
    }

    .nav-link.active {
        color: white;
        background-color: var(--bs-primary);
    }
}
</style>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import Swal from 'sweetalert2';

const { t } = useI18n();
const router = useRouter();
const authStore = useAuthStore();

const logout = () => {
    Swal.fire({
        title: t('Are you sure?'),
        text: t("You will be logged out of this session!"),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: t('Yes, sign out!')
    }).then(async (result) => {
        if (result.isConfirmed) {
            await authStore.logout();
            router.push('/login');
        }
    })
}
</script>
