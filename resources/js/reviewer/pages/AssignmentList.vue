<template>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">My Review Assignments</h5>
        </div>
        <div class="card-body p-0">
            <div v-if="loading" class="text-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <div v-else-if="error" class="alert alert-danger m-4">{{ error }}</div>

            <div v-else-if="assignments.length === 0" class="text-center p-5 text-muted">
                <i class="fa fa-inbox fa-3x mb-3"></i>
                <h5>No Review Assignments</h5>
                <p>You have no review assignments at this time.</p>
            </div>

            <div v-else class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Journal</th>
                            <th>Submission</th>
                            <th>Round</th>
                            <th>Mode</th>
                            <th>Status</th>
                            <th>Assigned</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="a in assignments" :key="a.id">
                            <td>#{{ a.id }}</td>
                            <td>{{ a.submission?.journal?.title ?? '—' }}</td>
                            <td>
                                <strong>{{ a.submission?.title ?? 'N/A' }}</strong>
                            </td>
                            <td>Round {{ a.round_number }}</td>
                            <td>
                                <span class="badge bg-secondary text-capitalize">{{ formatMode(a.review_mode) }}</span>
                            </td>
                            <td>
                                <span class="badge" :class="statusBadge(a.status)">{{ formatStatus(a.status) }}</span>
                            </td>
                            <td>{{ formatDate(a.assigned_at) }}</td>
                            <td>
                                <router-link
                                    :to="{ name: 'reviewer.detail', params: { id: a.id } }"
                                    class="btn btn-sm btn-primary">
                                    View
                                </router-link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="pagination && pagination.last_page > 1" class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted">Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }}</small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                            <button class="page-link" @click="fetch(pagination.current_page - 1)">Previous</button>
                        </li>
                        <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                            <button class="page-link" @click="fetch(pagination.current_page + 1)">Next</button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const assignments = ref([]);
const pagination = ref(null);
const loading = ref(true);
const error = ref(null);

const fetch = async (page = 1) => {
    loading.value = true;
    error.value = null;
    try {
        const res = await axios.get(`/reviewer/assignments?page=${page}`);
        assignments.value = res.data.data;
        pagination.value = res.data.meta;
    } catch (err) {
        error.value = 'Failed to load assignments. Please try again.';
    } finally {
        loading.value = false;
    }
};

const formatStatus = (s) => {
    if (!s) return 'Unknown';
    return s.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
};

const formatMode = (m) => {
    if (!m) return '';
    return { blind: 'Blind', double_blind: 'Double Blind', open: 'Open' }[m] ?? m;
};

const statusBadge = (s) => {
    switch (s) {
        case 'assigned':    return 'bg-warning text-dark';
        case 'accepted':    return 'bg-primary';
        case 'in_progress': return 'bg-info text-dark';
        case 'submitted':   return 'bg-success';
        case 'declined':    return 'bg-danger';
        case 'cancelled':   return 'bg-dark';
        default:            return 'bg-secondary';
    }
};

const formatDate = (d) => {
    if (!d) return '—';
    return new Date(d).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

onMounted(fetch);
</script>
