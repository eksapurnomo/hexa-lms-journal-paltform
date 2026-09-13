<template>
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">My Submissions</h5>
            <router-link :to="{ name: 'author_submission_create' }" class="btn btn-primary">
                Submit New Manuscript
            </router-link>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div v-if="loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div v-else-if="submissions.length === 0" class="text-center p-5 text-muted">
                    <i class="ri-file-text-line fs-1 mb-3"></i>
                    <h5>No Submissions Found</h5>
                    <p>You haven't submitted any manuscripts yet.</p>
                </div>

                <div v-else class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Title</th>
                                <th scope="col">Journal</th>
                                <th scope="col">Status</th>
                                <th scope="col">Submitted Date</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sub in submissions" :key="sub.id">
                                <td>{{ sub.id }}</td>
                                <td>
                                    <strong>{{ sub.title }}</strong>
                                </td>
                                <td>{{ sub.journal?.title }}</td>
                                <td>
                                    <span class="badge" :class="statusBadgeClass(sub.status)">
                                        {{ formatStatus(sub.status) }}
                                    </span>
                                </td>
                                <td>{{ sub.submitted_at ? new Date(sub.submitted_at).toLocaleDateString() : 'N/A' }}</td>
                                <td class="text-end">
                                    <router-link :to="{ name: 'author_submission_details', params: { id: sub.id } }" class="btn btn-sm btn-outline-primary">
                                        View Details
                                    </router-link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination (Simple Implementation) -->
                <div v-if="pagination && pagination.last_page > 1" class="d-flex justify-content-between align-items-center mt-4">
                    <span class="text-muted small">
                        Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} entries
                    </span>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-secondary" :disabled="pagination.current_page === 1" @click="fetchSubmissions(pagination.current_page - 1)">Previous</button>
                        <button class="btn btn-sm btn-outline-secondary" :disabled="pagination.current_page === pagination.last_page" @click="fetchSubmissions(pagination.current_page + 1)">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const submissions = ref([]);
const loading = ref(true);
const pagination = ref(null);

const fetchSubmissions = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get(`/api/submissions?page=${page}`);
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

const statusBadgeClass = (status) => {
    switch (status) {
        case 'draft': return 'bg-secondary';
        case 'submitted': return 'bg-primary';
        case 'editorial_assessment': return 'bg-info text-dark';
        case 'review_pending': return 'bg-warning text-dark';
        case 'revision_required': return 'bg-danger';
        case 'revision_submitted': return 'bg-info text-dark';
        case 'accepted': return 'bg-success';
        case 'rejected': return 'bg-dark';
        default: return 'bg-secondary';
    }
};

onMounted(() => {
    fetchSubmissions();
});
</script>
