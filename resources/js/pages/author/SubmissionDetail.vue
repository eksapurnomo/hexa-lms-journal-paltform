<template>
    <div class="py-2">
        <div class="d-flex align-items-center mb-4 gap-3">
            <router-link :to="{ name: 'author_submission_list' }" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;">
                <i class="bi bi-arrow-left fs-5"></i>
            </router-link>
            <div>
                <h4 class="mb-1 fw-bold">Submission Details</h4>
                <p class="text-muted small mb-0">View manuscript details and manage revisions.</p>
            </div>
        </div>

        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div v-else-if="error" class="alert alert-danger d-flex align-items-center border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>{{ error }}</div>
        </div>
        <div v-else-if="submission" class="submission-detail">
            <div class="row g-4">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <div class="theme-shadow rounded bg-white border border-light mb-4">
                        <div class="p-4 border-bottom border-light d-flex justify-content-between align-items-center bg-light rounded-top">
                            <h5 class="mb-0 fw-bold d-flex align-items-center">
                                <i class="bi bi-journal-text me-2 text-primary"></i> Manuscript
                            </h5>
                            <span class="badge rounded-pill border" :class="statusBadgeClass(submission.status)">
                                {{ formatStatus(submission.status) }}
                            </span>
                        </div>
                        <div class="p-4">
                            <h3 class="fw-bold mb-2 text-dark">{{ submission.title }}</h3>
                            <div class="text-muted small mb-4 d-flex align-items-center">
                                <i class="bi bi-journal me-1"></i> {{ submission.journal?.title || 'Unknown Journal' }}
                                <span class="mx-2">&bull;</span>
                                <i class="bi bi-clock me-1"></i> {{ submission.submitted_at ? new Date(submission.submitted_at).toLocaleDateString() : 'Not Submitted' }}
                            </div>

                            <h6 class="fw-bold text-uppercase text-muted small mb-2" style="letter-spacing: 0.5px;">Abstract</h6>
                            <p class="text-dark" style="line-height: 1.6;">{{ submission.abstract }}</p>

                            <h6 class="fw-bold text-uppercase text-muted small mt-4 mb-2" style="letter-spacing: 0.5px;">Keywords</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <span v-for="keyword in submission.keywords" :key="keyword" class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-medium">
                                    {{ keyword }}
                                </span>
                            </div>

                            <h6 class="fw-bold text-uppercase text-muted small mt-4 mb-3" style="letter-spacing: 0.5px;">Authors</h6>
                            <ul class="list-group list-group-flush border-top border-bottom rounded-0">
                                <li class="list-group-item px-0 py-3 border-light d-flex justify-content-between align-items-center" v-for="author in submission.authors" :key="author.id">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="bi bi-person text-secondary fs-5"></i>
                                        </div>
                                        <div>
                                            <strong class="d-block text-dark">{{ author.first_name }} {{ author.last_name }}</strong>
                                            <div class="text-muted small">{{ author.email }}</div>
                                        </div>
                                    </div>
                                    <span v-if="author.is_corresponding" class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Corresponding</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Revisions Section -->
                    <div class="theme-shadow rounded bg-white border border-light mb-4">
                        <div class="p-4 border-bottom border-light bg-light rounded-top">
                            <h5 class="mb-0 fw-bold d-flex align-items-center">
                                <i class="bi bi-file-earmark-check me-2 text-primary"></i> Revisions & Files
                            </h5>
                        </div>
                        <div class="p-4">
                            <div v-if="submission.revisions && submission.revisions.length > 0">
                                <div v-for="revision in submission.revisions" :key="revision.id" class="mb-4 border border-light rounded p-0 overflow-hidden shadow-sm">
                                    <div class="bg-light p-3 border-bottom border-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold text-dark">Revision #{{ revision.version_number }}</h6>
                                        <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>{{ new Date(revision.created_at).toLocaleString() }}</small>
                                    </div>
                                    
                                    <div class="p-3">
                                        <h6 class="fw-bold text-uppercase text-muted small mb-2" style="letter-spacing: 0.5px;">Files</h6>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item px-0 border-0 d-flex justify-content-between align-items-center" v-for="file in revision.files" :key="file.id">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-file-earmark-pdf fs-4 text-danger me-3"></i>
                                                    <div>
                                                        <div class="text-dark fw-medium">{{ file.original_name }}</div>
                                                        <small class="text-muted">{{ formatBytes(file.size) }}</small>
                                                    </div>
                                                </div>
                                                <a :href="`/api/submissions/${submission.id}/files/${file.id}/download`" target="_blank" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3 fw-medium">
                                                    <i class="bi bi-download me-1"></i> Download
                                                </a>
                                            </li>
                                            <li v-if="!revision.files || revision.files.length === 0" class="list-group-item border-0 text-muted text-center py-3">
                                                No files attached to this revision.
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-5 text-muted">
                                <i class="bi bi-folder-x fs-1 mb-3 text-light-subtle"></i>
                                <h6 class="fw-bold">No revisions found</h6>
                            </div>

                            <!-- Upload New File (Only allowed when draft or revision_required AND user is creator) -->
                            <div v-if="submission.created_by === authStore.userData?.id && ['draft', 'revision_required'].includes(submission.status)" class="mt-4 pt-4 border-top border-light">
                                <h6 class="fw-bold mb-2">Upload Manuscript File</h6>
                                <p class="text-muted small mb-3"><i class="bi bi-info-circle me-1"></i>Only one manuscript file is supported. Uploading a new file will replace the current draft file.</p>
                                
                                <div class="p-4 border border-dashed rounded bg-light text-center" style="border-style: dashed !important; border-width: 2px !important; border-color: #dee2e6 !important;">
                                    <i class="bi bi-cloud-upload fs-1 text-primary mb-2"></i>
                                    <div class="mb-3">
                                        <input type="file" id="fileUpload" class="d-none" @change="handleFileUpload" accept=".pdf,.doc,.docx">
                                        <label for="fileUpload" class="btn btn-outline-primary mb-2" style="cursor: pointer;">
                                            Choose File
                                        </label>
                                        <div class="text-muted small fw-medium">{{ selectedFile ? selectedFile.name : 'No file selected (PDF, DOC, DOCX)' }}</div>
                                    </div>
                                    
                                    <button class="btn btn-primary shadow-sm px-4 fw-medium" type="button" @click="uploadFile" :disabled="!selectedFile || uploading">
                                        <span v-if="uploading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        <i v-if="!uploading" class="bi bi-upload me-2"></i> Upload File
                                    </button>
                                </div>

                                <div v-if="uploadError" class="alert alert-danger mt-3 mb-0 py-2 small border-0"><i class="bi bi-exclamation-circle me-1"></i>{{ uploadError }}</div>
                                <div v-if="uploadSuccess" class="alert alert-success mt-3 mb-0 py-2 small border-0"><i class="bi bi-check-circle me-1"></i>File uploaded successfully.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Actions -->
                <div class="col-lg-4">
                    <div class="theme-shadow rounded bg-white border border-light mb-4 sticky-top" style="top: 20px;">
                        <div class="p-4 border-bottom border-light bg-light rounded-top">
                            <h5 class="mb-0 fw-bold">Workflow Actions</h5>
                        </div>
                        <div class="p-4">
                            <!-- Draft Actions -->
                            <div v-if="submission.created_by === authStore.userData?.id && submission.status === 'draft'">
                                <div class="d-flex mb-3">
                                    <i class="bi bi-info-circle-fill text-primary mt-1 me-2 fs-5"></i>
                                    <p class="text-muted small mb-0">Your submission is in draft. Once all authors and files are added, you can submit it to the journal.</p>
                                </div>
                                <button class="btn btn-primary w-100 py-2 fw-medium shadow-sm" @click="submitManuscript" :disabled="submittingAction">
                                    <span v-if="submittingAction" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    <i v-else class="bi bi-send me-2"></i> Submit Manuscript
                                </button>
                            </div>

                            <!-- Revision Required Actions -->
                            <div v-else-if="submission.created_by === authStore.userData?.id && submission.status === 'revision_required'">
                                <div class="d-flex mb-3">
                                    <i class="bi bi-exclamation-circle-fill text-warning mt-1 me-2 fs-5"></i>
                                    <p class="text-muted small mb-0">The editorial team has requested a revision. Upload your updated manuscript and submit the revision.</p>
                                </div>
                                <button class="btn btn-warning w-100 py-2 fw-medium shadow-sm text-dark" @click="submitRevision" :disabled="submittingAction">
                                    <span v-if="submittingAction" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    <i v-else class="bi bi-arrow-repeat me-2"></i> Submit Revision
                                </button>
                            </div>

                            <div v-else class="text-center py-4 bg-light rounded border border-light">
                                <div class="rounded-circle bg-white d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 60px; height: 60px;">
                                    <i class="bi bi-lock text-muted fs-3"></i>
                                </div>
                                <p class="text-muted small mb-0 fw-medium">No actions available at this stage.</p>
                            </div>

                            <div v-if="actionError" class="alert alert-danger mt-3 py-2 small mb-0 border-0"><i class="bi bi-exclamation-circle me-1"></i>{{ actionError }}</div>
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
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
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
        const response = await axios.get(`/submissions/${submissionId}`, {
            headers: {
                Authorization: "Bearer " + authStore.authToken,
                Accept: "application/json"
            }
        });
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
        const response = await axios.post(`/submissions/${submissionId}/files`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                Authorization: "Bearer " + authStore.authToken,
                Accept: "application/json"
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
        const response = await axios.post(`/submissions/${submissionId}/submit`, {}, {
            headers: {
                Authorization: "Bearer " + authStore.authToken,
                Accept: "application/json"
            }
        });
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
        const response = await axios.post(`/submissions/${submissionId}/revision`, {}, {
            headers: {
                Authorization: "Bearer " + authStore.authToken,
                Accept: "application/json"
            }
        });
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

<style scoped>
.theme-shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}
</style>
