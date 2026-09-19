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
                            <router-link :to="`/dashboard/journals/${$route.params.slug}/submissions`" class="nav-link active">
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
                            <router-link :to="`/dashboard/journals/${$route.params.slug}/settings`" class="nav-link text-dark">
                                <i class="bi bi-gear me-2"></i> Settings
                            </router-link>
                        </li>
                    </ul>

                    <div class="row g-4">
                        <div class="col-12">
                            <h5 class="fw-bold mb-3">Submissions</h5>
                            
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Author</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Editor</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="submission in submissions" :key="submission.id">
                                            <td>#{{ submission.id }}</td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 250px;" :title="submission.title">
                                                    {{ submission.title || 'Untitled' }}
                                                </div>
                                            </td>
                                            <td>
                                                <span v-if="submission.authors && submission.authors.length > 0">
                                                    {{ submission.authors.find(a => a.is_corresponding)?.first_name || submission.authors[0].first_name }}
                                                </span>
                                                <span v-else class="text-muted">N/A</span>
                                            </td>
                                            <td>{{ new Date(submission.created_at).toLocaleDateString() }}</td>
                                            <td>
                                                <span class="badge bg-secondary text-uppercase">{{ submission.status }}</span>
                                            </td>
                                            <td>
                                                {{ submission.editor ? submission.editor.name : 'Unassigned' }}
                                            </td>
                                            <td>
                                                <router-link :to="`/dashboard/journals/${$route.params.slug}/submissions/${submission.id}`" class="btn btn-sm btn-outline-primary">
                                                    View
                                                </router-link>
                                            </td>
                                        </tr>
                                        <tr v-if="submissions.length === 0">
                                            <td colspan="7" class="text-center py-4 text-muted">No submissions found in this journal.</td>
                                        </tr>
                                    </tbody>
                                </table>
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
const submissions = ref([]);

const fetchSubmissions = async (slug) => {
    loading.value = true;
    error.value = null;
    
    try {
        // First get overview to establish context and authorization
        const overviewRes = await axios.get(`/user/journals/${slug}/management/overview`, {
            headers: { Authorization: 'Bearer ' + authStore.authToken }
        });
        context.value = overviewRes.data.data;

        // Then fetch the submissions list
        const res = await axios.get(`/user/journals/${slug}/management/submissions`, {
            headers: { Authorization: 'Bearer ' + authStore.authToken }
        });
        submissions.value = res.data.data;
    } catch (err) {
        if (err.response && err.response.status === 403) {
            error.value = "Unauthorized: You do not have management permissions for this journal.";
        } else if (err.response && err.response.status === 404) {
            error.value = "Journal not found.";
        } else {
            error.value = "An error occurred while loading submissions.";
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    if (route.params.slug) {
        fetchSubmissions(route.params.slug);
    }
});

watch(() => route.params.slug, (newSlug) => {
    if (newSlug) {
        fetchSubmissions(newSlug);
    }
});
</script>
