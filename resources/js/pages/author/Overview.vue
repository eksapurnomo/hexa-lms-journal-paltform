<template>
    <div>
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <div>
                <h3 class="mb-1 fw-bold text-dark">Author Workspace</h3>
                <div class="text-muted small">
                    Welcome back. Manage your journal submissions here.
                </div>
            </div>
            <div>
                <router-link to="/author/submissions/create" class="btn btn-primary shadow-sm rounded-pill px-4 py-2">
                    <i class="bi bi-plus-lg me-2"></i> New Submission
                </router-link>
            </div>
        </div>

        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div v-else-if="error" class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ error }}
        </div>

        <div v-else>
            <h5 class="fw-bold mb-3"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Submission Summary</h5>
            <div class="row g-3 mb-5">
                <div class="col-sm-6 col-lg-3">
                    <div class="card bg-primary text-white shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <h1 class="display-5 fw-bold mb-0">{{ totalSubmissions }}</h1>
                            <span class="small text-uppercase">Total Submissions</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card bg-secondary text-white shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <h1 class="display-5 fw-bold mb-0">{{ draftCount }}</h1>
                            <span class="small text-uppercase">Drafts</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card bg-info text-white shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <h1 class="display-5 fw-bold mb-0">{{ submittedCount }}</h1>
                            <span class="small text-uppercase">Submitted</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card bg-success text-white shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <h1 class="display-5 fw-bold mb-0">{{ acceptedCount }}</h1>
                            <span class="small text-uppercase">Accepted</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h5 class="fw-bold mb-3"><i class="bi bi-journal-text me-2 text-primary"></i>Latest Activity</h5>
                    <div v-if="recentSubmissions.length > 0" class="list-group shadow-sm border-0">
                        <router-link v-for="sub in recentSubmissions" :key="sub.id" :to="`/author/submissions/${sub.id}`" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">
                            <div>
                                <h6 class="mb-1 text-truncate" style="max-width: 300px;">{{ sub.title || 'Untitled Draft' }}</h6>
                                <small class="text-muted">{{ sub.journal ? sub.journal.title : 'No Journal' }}</small>
                            </div>
                            <span class="badge bg-secondary text-uppercase">{{ sub.status }}</span>
                        </router-link>
                    </div>
                    <div v-else class="text-muted p-4 text-center bg-white shadow-sm rounded">
                        No recent activity.
                    </div>
                </div>
                
                <div class="col-md-6 mt-4 mt-md-0">
                    <h5 class="fw-bold mb-3"><i class="bi bi-lightning-charge me-2 text-primary"></i>Quick Actions</h5>
                    <div class="list-group shadow-sm border-0">
                        <router-link to="/author/submissions/create" class="list-group-item list-group-item-action p-3">
                            <i class="bi bi-file-earmark-plus me-2 text-primary"></i> Start a new submission
                        </router-link>
                        <router-link to="/author/submissions" class="list-group-item list-group-item-action p-3">
                            <i class="bi bi-list-task me-2 text-primary"></i> View all my submissions
                        </router-link>
                        <router-link to="/author/journals" class="list-group-item list-group-item-action p-3">
                            <i class="bi bi-journal-bookmark me-2 text-primary"></i> Browse journals
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const authStore = useAuthStore();
const loading = ref(true);
const error = ref(null);
const submissions = ref([]);

const fetchSubmissions = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get('/submissions', {
            headers: {
                Authorization: 'Bearer ' + authStore.authToken
            }
        });
        submissions.value = response.data.data || [];
    } catch (err) {
        error.value = "Failed to load submission data.";
        console.error(err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchSubmissions();
});

const totalSubmissions = computed(() => submissions.value.length);
const draftCount = computed(() => submissions.value.filter(s => s.status === 'draft').length);
const submittedCount = computed(() => submissions.value.filter(s => s.status === 'submitted').length);
const acceptedCount = computed(() => submissions.value.filter(s => s.status === 'accepted').length);

const recentSubmissions = computed(() => {
    return [...submissions.value].sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at)).slice(0, 4);
});
</script>
