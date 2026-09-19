<template>
    <div class="py-2">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="mb-1 fw-bold">My Submissions</h4>
                <p class="text-muted small mb-0">Manage your manuscript submissions and track their status.</p>
            </div>
            <router-link :to="{ name: 'author_submission_create' }" class="btn btn-primary shadow-sm d-inline-flex align-items-center">
                <i class="bi bi-plus-circle me-2"></i> Submit New Manuscript
            </router-link>
        </div>

        <div class="theme-shadow rounded bg-white">
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <div v-else-if="submissions.length === 0" class="text-center p-5 text-muted">
                <i class="bi bi-file-earmark-text fs-1 mb-3 text-light"></i>
                <h5 class="fw-bold">No Submissions Found</h5>
                <p class="small mb-0">You haven't submitted any manuscripts yet. Click the button above to start.</p>
            </div>

            <div v-else class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" class="border-0 rounded-top-start ps-4 py-3 text-muted small fw-bold text-uppercase">Manuscript</th>
                            <th scope="col" class="border-0 py-3 text-muted small fw-bold text-uppercase">Journal</th>
                            <th scope="col" class="border-0 py-3 text-muted small fw-bold text-uppercase">Role</th>
                            <th scope="col" class="border-0 py-3 text-muted small fw-bold text-uppercase">Status</th>
                            <th scope="col" class="border-0 rounded-top-end pe-4 py-3 text-end text-muted small fw-bold text-uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <tr v-for="sub in submissions" :key="sub.id" class="border-bottom">
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark mb-1">{{ sub.title }}</div>
                                <div class="text-muted small">
                                    <i class="bi bi-clock me-1"></i> {{ sub.submitted_at ? new Date(sub.submitted_at).toLocaleDateString() : 'Draft' }}
                                    <span class="mx-1">&bull;</span>
                                    ID: {{ sub.id }}
                                </div>
                            </td>
                            <td class="py-3">
                                <span class="text-dark small">{{ sub.journal?.title || 'Unknown Journal' }}</span>
                            </td>
                            <td class="py-3">
                                <span v-if="sub.created_by === authStore.userData?.id" class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Owner</span>
                                <span v-else-if="isCorrespondingAuthor(sub)" class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Corresponding</span>
                                <span v-else class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Co-Author</span>
                            </td>
                            <td class="py-3">
                                <span class="badge rounded-pill border" :class="statusBadgeClass(sub.status)">
                                    {{ formatStatus(sub.status) }}
                                </span>
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <router-link :to="{ name: 'author_submission_details', params: { id: sub.id } }" class="btn btn-sm btn-light border shadow-sm">
                                    View
                                </router-link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (Simple Implementation) -->
            <div v-if="pagination && pagination.last_page > 1" class="d-flex justify-content-between align-items-center p-3 border-top bg-light rounded-bottom">
                <span class="text-muted small fw-medium">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} entries
                </span>
                <div class="btn-group shadow-sm">
                    <button class="btn btn-sm btn-white border" :disabled="pagination.current_page === 1" @click="fetchSubmissions(pagination.current_page - 1)">Previous</button>
                    <button class="btn btn-sm btn-white border" :disabled="pagination.current_page === pagination.last_page" @click="fetchSubmissions(pagination.current_page + 1)">Next</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const submissions = ref([]);
const loading = ref(true);
const pagination = ref(null);

const fetchSubmissions = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get(`/submissions?page=${page}`, {
            headers: {
                Authorization: "Bearer " + authStore.authToken,
                Accept: "application/json"
            }
        });
        submissions.value = response.data.data;
        pagination.value = response.data.meta;
    } catch (error) {
        console.error("Failed to load submissions", error);
    } finally {
        loading.value = false;
    }
};

const formatStatus = (status) => {
    if (!status) return 'Unknown';
    return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const isCorrespondingAuthor = (sub) => {
    if (!sub.authors || !authStore.userData) return false;
    return sub.authors.some(a => a.user_id === authStore.userData.id && a.is_corresponding);
};

const statusBadgeClass = (status) => {
    switch (status) {
        case 'draft': return 'bg-light text-secondary border-secondary-subtle';
        case 'submitted': return 'bg-primary-subtle text-primary border-primary-subtle';
        case 'editorial_assessment': return 'bg-info-subtle text-info border-info-subtle';
        case 'review_pending': return 'bg-warning-subtle text-warning border-warning-subtle';
        case 'revision_required': return 'bg-danger-subtle text-danger border-danger-subtle';
        case 'revision_submitted': return 'bg-info-subtle text-info border-info-subtle';
        case 'accepted': return 'bg-success-subtle text-success border-success-subtle';
        case 'rejected': return 'bg-dark text-white border-dark';
        default: return 'bg-light text-secondary border-secondary-subtle';
    }
};

onMounted(() => {
    fetchSubmissions();
});
</script>

<style scoped>
.btn-white {
    background-color: #fff;
}
.btn-white:hover:not(:disabled) {
    background-color: #f8f9fa;
}
</style>
