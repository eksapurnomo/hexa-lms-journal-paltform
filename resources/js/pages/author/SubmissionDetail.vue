<template>
    <div class="container my-5">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <router-link :to="{ name: 'author_submission_list' }" class="text-decoration-none me-2">
                    <i class="ri-arrow-left-line"></i>
                </router-link>
                Submission Details
            </h4>
        </div>

        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div v-else-if="error" class="alert alert-danger">
            {{ error }}
        </div>
        <div v-else-if="submission" class="submission-detail">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Manuscript</h5>
                            <span class="badge" :class="statusBadgeClass(submission.status)">
                                {{ formatStatus(submission.status) }}
                            </span>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title">{{ submission.title }}</h3>
                            <p class="text-muted mb-4">{{ submission.journal?.title }}</p>

                            <h6 class="fw-bold">Abstract</h6>
                            <p class="card-text">{{ submission.abstract }}</p>

                            <h6 class="fw-bold mt-4">Keywords</h6>
                            <div>
                                <span v-for="keyword in submission.keywords" :key="keyword" class="badge bg-light text-dark border me-2">
                                    {{ keyword }}
                                </span>
                            </div>

                            <h6 class="fw-bold mt-4">Authors</h6>
                            <ul class="list-group list-group-flush mb-0">
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center" v-for="author in submission.authors" :key="author.id">
                                    <div>
                                        <strong>{{ author.first_name }} {{ author.last_name }}</strong>
                                        <div class="text-muted small">{{ author.email }}</div>
                                    </div>
                                    <span v-if="author.is_corresponding" class="badge bg-primary rounded-pill">Corresponding</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Revisions Section -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Revisions & Files</h5>
                        </div>
                        <div class="card-body">
                            <div v-if="submission.revisions && submission.revisions.length > 0">
                                <div v-for="revision in submission.revisions" :key="revision.id" class="mb-4 border rounded p-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">Revision #{{ revision.version_number }}</h6>
                                        <small class="text-muted">{{ new Date(revision.created_at).toLocaleString() }}</small>
                                    </div>
                                    
                                    <h7 class="fw-bold fs-6 text-muted">Files</h7>
                                    <ul class="list-group mt-2">
                                        <li class="list-group-item d-flex justify-content-between align-items-center" v-for="file in revision.files" :key="file.id">
                                            <div>
                                                <i class="ri-file-pdf-line text-danger me-2"></i>
                                                {{ file.original_name }}
                                                <small class="text-muted ms-2">({{ formatBytes(file.size) }})</small>
                                            </div>
                                            <a :href="`/api/submissions/${submission.id}/files/${file.id}/download`" target="_blank" class="btn btn-sm btn-outline-primary">
                                                Download
                                            </a>
                                        </li>
                                        <li v-if="!revision.files || revision.files.length === 0" class="list-group-item text-muted text-center py-3">
                                            No files attached to this revision.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div v-else class="text-center py-4 text-muted">
                                No revisions found.
                            </div>

                            <!-- Upload New File (Only allowed when draft or revision_required) -->
                            <div v-if="['draft', 'revision_required'].includes(submission.status)" class="mt-4 pt-3 border-top">
                                <h6>Upload Manuscript File</h6>
                                <p class="text-muted small">Only one manuscript file is supported. Uploading a new file will replace the current draft file.</p>
                                <div class="input-group">
                                    <input type="file" class="form-control" @change="handleFileUpload" accept=".pdf,.doc,.docx">
                                    <button class="btn btn-primary" type="button" @click="uploadFile" :disabled="!selectedFile || uploading">
                                        <span v-if="uploading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Upload
                                    </button>
                                </div>
                                <div v-if="uploadError" class="text-danger small mt-1">{{ uploadError }}</div>
                                <div v-if="uploadSuccess" class="text-success small mt-1">File uploaded successfully.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Actions -->
                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Workflow Actions</h5>
                        </div>
                        <div class="card-body">
                            <!-- Draft Actions -->
                            <div v-if="submission.status === 'draft'">
                                <p class="text-muted small mb-3">Your submission is in draft. Once all authors and files are added, you can submit it.</p>
                                <button class="btn btn-primary w-100 mb-2" @click="submitManuscript" :disabled="submittingAction">
                                    <span v-if="submittingAction" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Submit Manuscript
                                </button>
                            </div>

                            <!-- Revision Required Actions -->
                            <div v-else-if="submission.status === 'revision_required'">
                                <p class="text-muted small mb-3">The editorial team has requested a revision. Upload your updated manuscript and submit the revision.</p>
                                <button class="btn btn-warning w-100 text-dark mb-2" @click="submitRevision" :disabled="submittingAction">
                                    <span v-if="submittingAction" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Submit Revision
                                </button>
                            </div>

                            <div v-else class="text-center py-3">
                                <i class="ri-lock-line fs-2 text-muted mb-2 d-block"></i>
                                <span class="text-muted">No actions available at this stage.</span>
                            </div>

                            <div v-if="actionError" class="alert alert-danger mt-3 py-2 small mb-0">{{ actionError }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const submissionId = route.params.id;
const submission = ref(null);
const loading = ref(true);
const error = ref(null);

const selectedFile = ref(null);
const uploading = ref(false);
const uploadError = ref(null);
const uploadSuccess = ref(false);

const submittingAction = ref(false);
const actionError = ref(null);

const fetchSubmission = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get(`/api/submissions/${submissionId}`);
        submission.value = response.data.data;
    } catch (err) {
        if (err.response && err.response.status === 403) {
            error.value = "Access Denied: You do not have permission to view this submission.";
        } else if (err.response && err.response.status === 404) {
            error.value = "Submission not found.";
        } else {
            error.value = "Failed to load submission details.";
        }
        console.error("Fetch error", err);
    } finally {
        loading.value = false;
    }
};

const handleFileUpload = (event) => {
    selectedFile.value = event.target.files[0];
    uploadError.value = null;
    uploadSuccess.value = false;
};

const uploadFile = async () => {
    if (!selectedFile.value) return;

    uploading.value = true;
    uploadError.value = null;
    uploadSuccess.value = false;

    const formData = new FormData();
    formData.append('file', selectedFile.value);

    try {
        const response = await axios.post(`/api/submissions/${submissionId}/files`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        submission.value = response.data.data;
        uploadSuccess.value = true;
        selectedFile.value = null;
        // Reset file input visually
        const fileInput = document.querySelector('input[type="file"]');
        if (fileInput) fileInput.value = '';
    } catch (err) {
        uploadError.value = err.response?.data?.message || "Failed to upload file.";
    } finally {
        uploading.value = false;
    }
};

const submitManuscript = async () => {
    if (!confirm("Are you sure you want to submit this manuscript? You will not be able to edit it once submitted.")) {
        return;
    }

    submittingAction.value = true;
    actionError.value = null;

    try {
        const response = await axios.post(`/api/submissions/${submissionId}/submit`);
        submission.value = response.data.data;
    } catch (err) {
        actionError.value = err.response?.data?.message || "Failed to submit manuscript.";
    } finally {
        submittingAction.value = false;
    }
};

const submitRevision = async () => {
    if (!confirm("Are you sure you want to submit this revision?")) {
        return;
    }

    submittingAction.value = true;
    actionError.value = null;

    try {
        const response = await axios.post(`/api/submissions/${submissionId}/submit-revision`);
        submission.value = response.data.data;
    } catch (err) {
        actionError.value = err.response?.data?.message || "Failed to submit revision.";
    } finally {
        submittingAction.value = false;
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

const formatBytes = (bytes, decimals = 2) => {
    if (!+bytes) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
};

onMounted(() => {
    fetchSubmission();
});
</script>
