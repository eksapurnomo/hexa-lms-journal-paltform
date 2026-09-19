<template>
    <main class="px-3 h-100 d-flex flex-column flex-grow-1 bg-light">
        <section class="row flex-grow-1">
            <section class="col-4 border-end d-none d-xl-block col-xl-3 bg-white">
                <DashboardSidebar />
            </section>
            
            <section class="col-xl-9 p-5 bg-white shadow-sm">
                <button class="d-xl-none btn theme-shadow btn-outline-primary px-4 py-2 rounded-1 mt-3" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#dashboardMenu" aria-controls="dashboardMenu">
                    {{$t('Menu')}} <i class="bi bi-arrow-right-circle"></i>
                </button>

                <div class="offcanvas offcanvas-start" tabindex="-1" id="dashboardMenu">
                    <div class="offcanvas-header">
                        <button type="button" class="theme-shadow btn-outline-primary px-3 py-2 rounded-1 btn m-0"
                            data-bs-dismiss="offcanvas" aria-label="Close">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                    <div class="offcanvas-body">
                        <DashboardMenu />
                    </div>
                </div>

                <div v-if="loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div v-else-if="error" class="text-center py-5 text-danger">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                    <h5 class="mt-3">{{ error }}</h5>
                    <button class="btn btn-outline-secondary mt-3" @click="$router.push('/dashboard')">Back to Dashboard</button>
                </div>

                <div v-else class="journal-workspace">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                        <div>
                            <h3 class="mb-1 fw-bold text-dark">{{ context.journal.title }}</h3>
                        </div>
                    </div>

                    <ul class="nav nav-pills mb-4">
                        <li class="nav-item">
                            <router-link :to="`/dashboard/journals/${$route.params.slug}/overview`" class="nav-link text-dark">
                                <i class="bi bi-columns-gap me-2"></i> Overview
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link :to="`/dashboard/journals/${$route.params.slug}/submissions`" class="nav-link text-dark">
                                <i class="bi bi-file-earmark-text me-2"></i> Submissions
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link :to="`/dashboard/journals/${$route.params.slug}/members`" class="nav-link text-dark">
                                <i class="bi bi-people me-2"></i> Members
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link :to="`/dashboard/journals/${$route.params.slug}/editorial-process`" class="nav-link text-dark">
                                <i class="bi bi-diagram-3 me-2"></i> Editorial Process
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link :to="`/dashboard/journals/${$route.params.slug}/settings`" class="nav-link active">
                                <i class="bi bi-gear me-2"></i> Settings
                            </router-link>
                        </li>
                    </ul>

                    <div class="row g-4">
                        <div class="col-12 col-lg-8">
                            <h5 class="fw-bold mb-3">Settings</h5>
                            
                            <div class="card shadow-sm border-0">
                                <div class="card-body p-4">
                                    <form @submit.prevent="updateSettings">
                                        <div v-if="successMessage" class="alert alert-success">
                                            {{ successMessage }}
                                        </div>
                                        <div v-if="updateError" class="alert alert-danger">
                                            {{ updateError }}
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Journal Title</label>
                                            <input type="text" class="form-control" v-model="settings.title" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" rows="4" v-model="settings.description"></textarea>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">ISSN</label>
                                                <input type="text" class="form-control" v-model="settings.issn">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">eISSN</label>
                                                <input type="text" class="form-control" v-model="settings.eissn">
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Status <span class="text-muted small">(Read Only)</span></label>
                                            <input type="text" class="form-control bg-light" :value="settings.status" disabled>
                                            <div class="form-text">Status modifications are handled via administrative procedures.</div>
                                        </div>

                                        <button type="submit" class="btn btn-primary px-4" :disabled="saving">
                                            <span v-if="saving" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Save Settings
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>
    </main>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';
import DashboardSidebar from "../../components/DashboardSidebar.vue";
import DashboardMenu from "../../components/DashboardMenu.vue";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const loading = ref(true);
const error = ref(null);
const context = ref(null);
const settings = ref({});
const saving = ref(false);
const successMessage = ref("");
const updateError = ref("");

const fetchSettings = async (slug) => {
    loading.value = true;
    error.value = null;
    
    try {
        const overviewRes = await axios.get(`/user/journals/${slug}/management/overview`, {
            headers: { Authorization: 'Bearer ' + authStore.authToken }
        });
        context.value = overviewRes.data.data;

        const res = await axios.get(`/user/journals/${slug}/management/settings`, {
            headers: { Authorization: 'Bearer ' + authStore.authToken }
        });
        settings.value = res.data.data;
    } catch (err) {
        if (err.response && err.response.status === 403) {
            error.value = "Unauthorized: You do not have management permissions for this journal.";
        } else if (err.response && err.response.status === 404) {
            error.value = "Journal not found.";
        } else {
            error.value = "An error occurred while loading settings.";
        }
    } finally {
        loading.value = false;
    }
};

const updateSettings = async () => {
    saving.value = true;
    successMessage.value = "";
    updateError.value = "";
    
    try {
        const res = await axios.patch(`/user/journals/${route.params.slug}/management/settings`, {
            title: settings.value.title,
            description: settings.value.description,
            issn: settings.value.issn,
            eissn: settings.value.eissn
        }, {
            headers: { Authorization: 'Bearer ' + authStore.authToken }
        });
        settings.value = res.data.data;
        
        // Also update context if title changed
        if (context.value && context.value.journal) {
            context.value.journal.title = res.data.data.title;
        }
        
        successMessage.value = "Settings updated successfully.";
    } catch (err) {
        if (err.response && err.response.status === 422) {
            updateError.value = "Validation failed. Please check your inputs.";
        } else if (err.response && err.response.status === 403) {
            updateError.value = "Unauthorized: You do not have permission to update settings.";
        } else {
            updateError.value = "An error occurred while updating settings.";
        }
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    if (route.params.slug) {
        fetchSettings(route.params.slug);
    }
});

watch(() => route.params.slug, (newSlug) => {
    if (newSlug) {
        fetchSettings(newSlug);
    }
});
</script>
