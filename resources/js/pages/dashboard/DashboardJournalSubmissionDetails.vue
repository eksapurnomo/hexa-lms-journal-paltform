<template>
    <main class="px-3 h-100 d-flex flex-column flex-grow-1 bg-light">
        <section class="row flex-grow-1">
            <section class="col-4 border-end d-none d-xl-block col-xl-3 bg-white">
                <DashboardSidebar />
            </section>
            
            <section class="col-xl-9 p-5 bg-white shadow-sm">
                <div v-if="loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div v-else-if="error" class="text-center py-5 text-danger">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                    <h5 class="mt-3">{{ error }}</h5>
                    <button class="btn btn-outline-secondary mt-3" @click="$router.push(`/dashboard/journals/${$route.params.slug}/submissions`)">Back to Submissions</button>
                </div>

                <div v-else class="journal-workspace">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                        <div>
                            <h3 class="mb-1 fw-bold text-dark">{{ context?.journal?.title }}</h3>
                        </div>
                        <router-link :to="`/dashboard/journals/${$route.params.slug}/submissions`" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Submissions
                        </router-link>
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

                    <div class="row g-4" v-if="submission">
                        <div class="col-md-8">
                            <h4 class="fw-bold mb-3">{{ submission.title }}</h4>
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="fw-bold">Abstract</h6>
                                    <p class="text-muted">{{ submission.abstract }}</p>
                                    <h6 class="fw-bold mt-3">Keywords</h6>
                                    <p class="text-muted">{{ submission.keywords }}</p>
                                    
                                    <h6 class="fw-bold mt-4">Authors</h6>
                                    <ul class="list-unstyled">
                                        <li v-for="author in submission.authors" :key="author.id">
                                            {{ author.first_name }} {{ author.last_name }}
                                            <span v-if="author.is_corresponding" class="badge bg-primary ms-1">Corresponding</span>
                                            <div class="text-muted small">{{ author.affiliation }} - {{ author.email }}</div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body">
                                    <h6 class="fw-bold">Status</h6>
                                    <span class="badge bg-secondary text-uppercase fs-6 mb-3">{{ submission.status }}</span>
                                    
                                    <h6 class="fw-bold">Assigned Editor</h6>
                                    <p v-if="submission.editor" class="text-muted mb-0">
                                        {{ submission.editor.name }}<br>
                                        <small>{{ submission.editor.email }}</small>
                                    </p>
                                    <p v-else class="text-muted fst-italic mb-0">Unassigned</p>

                                    <h6 class="fw-bold mt-3">Submitted At</h6>
                                    <p class="text-muted mb-0">{{ new Date(submission.created_at).toLocaleDateString() }}</p>
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
import { ref, onMounted } from 'vue';
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
const submission = ref(null);

const fetchSubmissionDetail = async (slug, id) => {
    loading.value = true;
    error.value = null;
    
    try {
        const overviewRes = await axios.get(`/user/journals/${slug}/management/overview`, {
            headers: { Authorization: 'Bearer ' + authStore.authToken }
        });
        context.value = overviewRes.data.data;

        const res = await axios.get(`/user/journals/${slug}/management/submissions/${id}`, {
            headers: { Authorization: 'Bearer ' + authStore.authToken }
        });
        submission.value = res.data.data;
    } catch (err) {
        if (err.response && err.response.status === 403) {
            error.value = "Unauthorized: You do not have permission to view this submission.";
        } else if (err.response && err.response.status === 404) {
            error.value = "Submission not found or does not belong to this journal.";
        } else {
            error.value = "An error occurred while loading submission details.";
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    if (route.params.slug && route.params.id) {
        fetchSubmissionDetail(route.params.slug, route.params.id);
    }
});
</script>
