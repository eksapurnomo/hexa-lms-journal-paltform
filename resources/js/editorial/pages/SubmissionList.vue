<template>
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 text-primary"><i class="fa fa-inbox me-2"></i>Editorial Desk</h5>
        </div>
        
        <div class="card-body bg-light border-bottom">
            <div class="row g-2">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                        <input type="text" class="form-control" v-model="filters.search" placeholder="Search by title or ID..." @keyup.enter="fetchSubmissions(1)">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" v-model="filters.status" @change="fetchSubmissions(1)">
                        <option value="">All Statuses</option>
                        <option value="submitted">Submitted</option>
                        <option value="editorial_assessment">Editorial Assessment</option>
                        <option value="review_pending">Review Pending</option>
                        <option value="revision_required">Revision Required</option>
                        <option value="revision_submitted">Revision Submitted</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" v-model="filters.journal_id" @change="fetchSubmissions(1)">
                        <option value="">All Journals</option>
                        <option v-for="journal in journals" :key="journal.id" :value="journal.id">
                            {{ journal.title }}
                        </option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary" @click="fetchSubmissions(1)">Filter</button>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div v-if="loading" class="text-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2 text-muted">Loading submissions...</div>
            </div>
            
            <div v-else-if="error" class="alert alert-danger m-4 d-flex align-items-center">
                <i class="fa fa-exclamation-triangle me-3 fa-2x"></i>
                <div>
                    <strong>Failed to load submissions.</strong><br>
                    {{ error }}
                </div>
                <button class="btn btn-outline-danger ms-auto btn-sm" @click="fetchSubmissions(pagination?.current_page || 1)">Retry</button>
            </div>

            <div v-else-if="submissions.length === 0" class="text-center p-5 text-muted bg-light m-4 rounded border">
                <i class="fa fa-folder-open fa-3x mb-3 text-secondary"></i>
                <h5>No Submissions Found</h5>
                <p>Try changing your filters or search term, or you currently do not have access to any submissions.</p>
                <button class="btn btn-outline-secondary mt-2" @click="clearFilters">Clear Filters</button>
            </div>

            <div v-else class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th @click="sortBy('id')" class="cursor-pointer" style="width: 8%">
                                ID <i class="fa" :class="sortIcon('id')"></i>
                            </th>
                            <th @click="sortBy('title')" class="cursor-pointer" style="width: 35%">
                                Submission <i class="fa" :class="sortIcon('title')"></i>
                            </th>
                            <th style="width: 20%">Journal & Authors</th>
                            <th @click="sortBy('status')" class="cursor-pointer" style="width: 15%">
                                Status <i class="fa" :class="sortIcon('status')"></i>
                            </th>
                            <th @click="sortBy('created_at')" class="cursor-pointer" style="width: 12%">
                                Date <i class="fa" :class="sortIcon('created_at')"></i>
                            </th>
                            <th style="width: 10%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sub in submissions" :key="sub.id" :class="{'table-warning': needsAttention(sub)}">
                            <td class="text-muted fw-bold">#{{ sub.id }}</td>
                            <td>
                                <strong class="d-block text-dark">{{ sub.title }}</strong>
                                <small v-if="sub.editor" class="text-muted"><i class="fa fa-user-circle me-1"></i>Editor: {{ sub.editor.name }}</small>
                                <small v-else class="text-warning fw-bold"><i class="fa fa-exclamation-circle me-1"></i>Unassigned Editor</small>
                            </td>
                            <td>
                                <div class="text-primary fw-bold mb-1" style="font-size: 0.9em;">{{ sub.journal?.title }}</div>
                                <div class="text-muted small text-truncate" style="max-width: 200px;" :title="authorNames(sub)">
                                    <i class="fa fa-users me-1"></i>{{ authorNames(sub) }}
                                </div>
                            </td>
                            <td>
                                <span class="badge w-100 p-2" :class="statusBadgeClass(sub.status)">
                                    {{ formatStatus(sub.status) }}
                                </span>
                            </td>
                            <td class="small">
                                <div class="text-dark">{{ formatDate(sub.created_at) }}</div>
                                <div class="text-muted" style="font-size: 0.85em;">Updated: {{ formatDate(sub.updated_at) }}</div>
                            </td>
                            <td>
                                <router-link :to="{ name: 'editorial.detail', params: { id: sub.id } }" class="btn btn-sm btn-outline-primary w-100">
                                    Open
                                </router-link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3" v-if="pagination && pagination.total > 0">
            <small class="text-muted fw-bold">Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} submissions</small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                        <button class="page-link" @click="fetchSubmissions(pagination.current_page - 1)">Previous</button>
                    </li>
                    <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                        <button class="page-link" @click="fetchSubmissions(pagination.current_page + 1)">Next</button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const submissions = ref([]);
const journals = ref([]);
const pagination = ref(null);
const loading = ref(true);
const error = ref(null);

const filters = ref({
    search: '',
    status: '',
    journal_id: '',
    sort_by: 'id',
    sort_dir: 'desc'
});

const fetchJournals = async () => {
    try {
        const response = await axios.get('/journals');
        const data = response.data.data || response.data;
        journals.value = data.journals || [];
    } catch (err) {
        console.error("Failed to load journals for filter");
    }
};

const fetchSubmissions = async (page = 1) => {
    loading.value = true;
    error.value = null;
    try {
        // Construct query params
        const params = new URLSearchParams({
            page: page,
            sort_by: filters.value.sort_by,
            sort_dir: filters.value.sort_dir
        });
        
        if (filters.value.search) params.append('search', filters.value.search);
        if (filters.value.status) params.append('status', filters.value.status);
        if (filters.value.journal_id) params.append('journal_id', filters.value.journal_id);

        const response = await axios.get(`/editorial/submissions?${params.toString()}`);
        submissions.value = response.data.data || [];
        pagination.value = response.data.meta || response.data || null;
    } catch (err) {
        if (err.response && err.response.status === 403) {
            error.value = "Access Denied: You do not have permission to access the Editorial Desk.";
        } else {
            error.value = err.response?.data?.message || "Failed to load submissions. Please try again later.";
        }
    } finally {
        loading.value = false;
    }
};

const clearFilters = () => {
    filters.value.search = '';
    filters.value.status = '';
    filters.value.journal_id = '';
    fetchSubmissions(1);
};

const sortBy = (field) => {
    if (filters.value.sort_by === field) {
        filters.value.sort_dir = filters.value.sort_dir === 'asc' ? 'desc' : 'asc';
    } else {
        filters.value.sort_by = field;
        filters.value.sort_dir = 'asc';
    }
    fetchSubmissions(1);
};

const sortIcon = (field) => {
    if (filters.value.sort_by !== field) return 'fa-sort text-muted opacity-50';
    return filters.value.sort_dir === 'asc' ? 'fa-sort-up text-primary' : 'fa-sort-down text-primary';
};

const authorNames = (sub) => {
    if (!sub.authors || sub.authors.length === 0) return 'Unknown Author';
    return sub.authors.map(a => a.name || `${a.first_name} ${a.last_name}`).join(', ');
};

const needsAttention = (sub) => {
    return sub.status === 'submitted' || sub.status === 'revision_submitted' || (sub.status !== 'rejected' && sub.status !== 'accepted' && !sub.editor);
};

const formatStatus = (status) => {
    if (!status) return 'Unknown';
    return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const statusBadgeClass = (status) => {
    switch (status) {
        case 'submitted': return 'bg-info text-dark border border-info';
        case 'editorial_assessment': return 'bg-warning text-dark border border-warning';
        case 'revision_required': return 'bg-danger text-white';
        case 'revision_submitted': return 'bg-info text-dark border border-info';
        case 'review_pending': return 'bg-primary text-white';
        case 'accepted': return 'bg-success text-white';
        case 'rejected': return 'bg-dark text-white';
        default: return 'bg-secondary text-white';
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

onMounted(() => {
    fetchJournals();
    fetchSubmissions();
});
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
    user-select: none;
}
.cursor-pointer:hover {
    background-color: #f8f9fa;
}
.table th {
    font-weight: 600;
}
</style>
