<template>
    <div v-if="loading" class="text-center p-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div v-else-if="error" class="alert alert-danger m-4">
        {{ error }}
        <div class="mt-3">
            <router-link :to="{ name: 'reviewer.list' }" class="btn btn-outline-danger btn-sm">Back to List</router-link>
        </div>
    </div>

    <div v-else-if="assignment" class="assignment-detail">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">
                <router-link :to="{ name: 'reviewer.list' }" class="text-decoration-none text-muted me-2">
                    <i class="fa fa-arrow-left"></i>
                </router-link>
                Review Assignment #{{ assignment.id }}
            </h4>
            <span class="badge fs-6" :class="statusBadge(assignment.status)">
                {{ formatStatus(assignment.status) }}
            </span>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">

                <!-- Submission Info -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Manuscript</h5>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title">{{ assignment.submission?.title }}</h3>
                        <p class="text-muted mb-1">
                            <strong>Journal:</strong> {{ assignment.submission?.journal?.title }}
                        </p>
                        <p class="text-muted mb-3">
                            <strong>Review Mode:</strong>
                            <span class="badge bg-secondary ms-1">{{ formatMode(assignment.review_mode) }}</span>
                            &nbsp;
                            <strong>Round:</strong> {{ assignment.round_number }}
                        </p>

                        <!-- Authors: only in open mode -->
                        <div v-if="assignment.review_mode === 'open' && assignment.submission?.authors?.length">
                            <h6>Authors</h6>
                            <ul class="list-group mb-3">
                                <li class="list-group-item d-flex justify-content-between align-items-center"
                                    v-for="author in assignment.submission.authors" :key="author.id">
                                    <span>
                                        <i class="fa fa-user text-muted me-2"></i>
                                        {{ author.name }}
                                        <span v-if="author.is_corresponding" class="badge bg-primary ms-2">Corresponding</span>
                                    </span>
                                    <span class="text-muted small">{{ author.email }}</span>
                                </li>
                            </ul>
                        </div>
                        <div v-else-if="assignment.review_mode !== 'open'" class="alert alert-info small py-2">
                            Author identity is hidden ({{ formatMode(assignment.review_mode) }} review).
                        </div>

                        <h6>Abstract</h6>
                        <p class="card-text">{{ assignment.submission?.abstract }}</p>

                        <div v-if="assignment.submission?.keywords?.length">
                            <h6>Keywords</h6>
                            <span v-for="kw in assignment.submission.keywords" :key="kw" class="badge bg-light text-dark border me-1 mb-1">{{ kw }}</span>
                        </div>
                    </div>
                </div>

                <!-- Manuscript Files -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Manuscript Files</h5>
                    </div>
                    <div class="card-body p-0">
                        <div v-if="!canDownload" class="alert alert-warning m-3 small py-2 mb-0">
                            Accept the assignment to access manuscript files.
                        </div>
                        <ul v-else-if="assignment.files && assignment.files.length" class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center"
                                v-for="file in assignment.files" :key="file.id">
                                <div>
                                    <i class="fa fa-file-pdf-o text-danger me-2"></i>
                                    {{ file.original_name }}
                                    <small class="text-muted ms-2">{{ formatFileSize(file.size) }}</small>
                                </div>
                                <a :href="`/api/reviewer/assignments/${assignment.id}/files/${file.id}/download`"
                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                    Download
                                </a>
                            </li>
                        </ul>
                        <div v-else class="text-muted small p-3">No files attached.</div>
                    </div>
                </div>

                <!-- Review Form -->
                <div class="card mb-4" v-if="showReviewForm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ isSubmitted ? 'Submitted Review' : 'Enter Review' }}</h5>
                        <span v-if="isSubmitted" class="badge bg-success">Submitted</span>
                    </div>
                    <div class="card-body">
                        <!-- Submitted read-only view -->
                        <div v-if="isSubmitted">
                            <div class="mb-3">
                                <strong>Recommendation:</strong>
                                <span class="ms-2 badge bg-primary">{{ formatStatus(peerReview.recommendation) }}</span>
                            </div>
                            <div v-if="peerReview.comments_to_editor" class="mb-3">
                                <strong>Comments to Editor:</strong>
                                <p class="mt-1 text-muted">{{ peerReview.comments_to_editor }}</p>
                            </div>
                            <div v-if="peerReview.comments_to_author" class="mb-3">
                                <strong>Comments to Author:</strong>
                                <p class="mt-1 text-muted">{{ peerReview.comments_to_author }}</p>
                            </div>
                            <div v-if="peerReview.criterion_responses?.length" class="mb-3">
                                <strong>Criterion Responses:</strong>
                                <ul class="list-group mt-2">
                                    <li class="list-group-item" v-for="cr in peerReview.criterion_responses" :key="cr.id">
                                        <small class="text-muted">Criterion #{{ cr.review_criterion_id }}</small>
                                        <div>{{ cr.response }}</div>
                                    </li>
                                </ul>
                            </div>
                            <small class="text-muted">Submitted: {{ formatDate(peerReview.submitted_at) }}</small>
                        </div>

                        <!-- Editable review form (accepted / in_progress) -->
                        <div v-else>
                            <!-- Round locked warning -->
                            <div v-if="roundLocked" class="alert alert-warning small py-2">
                                This review round has been closed by the editorial team. Review submission is no longer possible.
                            </div>
                            <fieldset :disabled="roundLocked || processing">

                                <!-- Criterion Responses -->
                                <div v-if="criteria.length" class="mb-4">
                                    <h6>Review Criteria</h6>
                                    <div v-for="criterion in criteria" :key="criterion.id" class="mb-3 p-3 border rounded">
                                        <label class="form-label fw-semibold mb-1">{{ criterion.name }}</label>

                                        <!-- Rating type -->
                                        <div v-if="criterion.type === 'rating'">
                                            <div class="d-flex gap-2 flex-wrap">
                                                <div v-for="n in ratingOptions(criterion)" :key="n">
                                                    <input type="radio"
                                                        class="btn-check"
                                                        :name="`criterion_${criterion.id}`"
                                                        :id="`crit_${criterion.id}_${n}`"
                                                        :value="String(n)"
                                                        v-model="form.criteriaResponses[criterion.id]">
                                                    <label class="btn btn-outline-primary btn-sm" :for="`crit_${criterion.id}_${n}`">{{ n }}</label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Boolean type -->
                                        <div v-else-if="criterion.type === 'boolean'">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio"
                                                    :name="`criterion_${criterion.id}`"
                                                    :id="`crit_${criterion.id}_yes`"
                                                    value="yes"
                                                    v-model="form.criteriaResponses[criterion.id]">
                                                <label class="form-check-label" :for="`crit_${criterion.id}_yes`">Yes</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio"
                                                    :name="`criterion_${criterion.id}`"
                                                    :id="`crit_${criterion.id}_no`"
                                                    value="no"
                                                    v-model="form.criteriaResponses[criterion.id]">
                                                <label class="form-check-label" :for="`crit_${criterion.id}_no`">No</label>
                                            </div>
                                        </div>

                                        <!-- Text type (default) -->
                                        <div v-else>
                                            <textarea class="form-control form-control-sm" rows="2"
                                                :placeholder="`Your response for: ${criterion.name}`"
                                                v-model="form.criteriaResponses[criterion.id]"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Recommendation -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Recommendation <span class="text-danger">*</span></label>
                                    <select class="form-select" v-model="form.recommendation" required>
                                        <option value="" disabled>Select recommendation...</option>
                                        <option value="accept">Accept</option>
                                        <option value="minor_revision">Minor Revision</option>
                                        <option value="major_revision">Major Revision</option>
                                        <option value="reject">Reject</option>
                                    </select>
                                </div>

                                <!-- Comments to Editor -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Comments to Editor</label>
                                    <textarea class="form-control" rows="4"
                                        placeholder="Confidential comments for the editor..."
                                        v-model="form.commentsToEditor"></textarea>
                                </div>

                                <!-- Comments to Author -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Comments to Author</label>
                                    <textarea class="form-control" rows="4"
                                        placeholder="Comments that will be shared with the author..."
                                        v-model="form.commentsToAuthor"></textarea>
                                </div>

                                <div v-if="!roundLocked" class="d-flex gap-2">
                                    <button class="btn btn-success"
                                        :disabled="!form.recommendation || processing"
                                        @click="submitReview">
                                        {{ processing ? 'Submitting...' : 'Submit Review' }}
                                    </button>
                                </div>
                                <small v-if="submitError" class="text-danger d-block mt-2">{{ submitError }}</small>
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">

                <!-- Assignment Actions -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Assignment</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0 small">
                            <dt class="col-sm-5">Status</dt>
                            <dd class="col-sm-7">
                                <span class="badge" :class="statusBadge(assignment.status)">{{ formatStatus(assignment.status) }}</span>
                            </dd>
                            <dt class="col-sm-5">Assigned</dt>
                            <dd class="col-sm-7">{{ formatDate(assignment.assigned_at) }}</dd>
                            <dt class="col-sm-5 mb-0" v-if="assignment.accepted_at">Accepted</dt>
                            <dd class="col-sm-7 mb-0" v-if="assignment.accepted_at">{{ formatDate(assignment.accepted_at) }}</dd>
                            <dt class="col-sm-5 mb-0" v-if="assignment.declined_at">Declined</dt>
                            <dd class="col-sm-7 mb-0" v-if="assignment.declined_at">{{ formatDate(assignment.declined_at) }}</dd>
                        </dl>

                        <hr v-if="assignment.status === 'assigned'">

                        <!-- Accept / Decline actions (only from 'assigned') -->
                        <div v-if="assignment.status === 'assigned'" class="d-grid gap-2">
                            <button class="btn btn-primary" :disabled="actionProcessing" @click="acceptAssignment">
                                {{ actionProcessing ? 'Processing...' : 'Accept Assignment' }}
                            </button>
                            <button class="btn btn-outline-danger" :disabled="actionProcessing" @click="declineAssignment">
                                {{ actionProcessing ? 'Processing...' : 'Decline Assignment' }}
                            </button>
                            <small v-if="actionError" class="text-danger">{{ actionError }}</small>
                        </div>

                        <div v-else-if="assignment.status === 'declined'" class="alert alert-danger small py-2 mb-0">
                            You declined this assignment.
                        </div>
                        <div v-else-if="assignment.status === 'cancelled'" class="alert alert-secondary small py-2 mb-0">
                            This assignment was cancelled by the editorial team.
                        </div>
                        <div v-else-if="assignment.status === 'submitted'" class="alert alert-success small py-2 mb-0">
                            Review submitted. Thank you.
                        </div>
                    </div>
                </div>

                <!-- Round Info -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Review Round</h5>
                    </div>
                    <div class="card-body small">
                        <div class="mb-2"><strong>Round:</strong> {{ assignment.round_number }}</div>
                        <div v-if="roundLocked" class="alert alert-warning py-2 mb-0 small">
                            This round is closed (editorial decision recorded).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    id: { type: [String, Number], required: true }
});

const assignment = ref(null);
const criteria = ref([]);
const loading = ref(true);
const error = ref(null);
const processing = ref(false);
const submitError = ref('');
const actionProcessing = ref(false);
const actionError = ref('');
const roundLocked = ref(false);

const form = ref({
    recommendation: '',
    commentsToEditor: '',
    commentsToAuthor: '',
    criteriaResponses: {}, // { criterionId: responseValue }
});

const peerReview = computed(() => assignment.value?.peer_review ?? null);
const isSubmitted = computed(() => !!peerReview.value?.submitted_at);

const canDownload = computed(() =>
    ['accepted', 'in_progress', 'submitted'].includes(assignment.value?.status)
);

const showReviewForm = computed(() =>
    ['accepted', 'in_progress', 'submitted'].includes(assignment.value?.status)
);

const fetchAssignment = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await axios.get(`/reviewer/assignments/${props.id}`);
        assignment.value = res.data.data ?? res.data;

        // Check if round is locked (editorial_decision present)
        // The backend does NOT expose editorial_decision to reviewer — so we rely on 422 from submit.
        // We do not need to check it here; UI allows attempt and server enforces.

        if (!isSubmitted.value && canDownload.value) {
            await fetchCriteria();
        }

        // Pre-fill criterion responses if already in peerReview (incomplete/draft scenario not supported by backend)
    } catch (err) {
        if (err.response?.status === 403) {
            error.value = 'Access Denied: You do not have permission to view this assignment.';
        } else if (err.response?.status === 404) {
            error.value = 'Assignment not found.';
        } else {
            error.value = 'Failed to load assignment.';
        }
    } finally {
        loading.value = false;
    }
};

const fetchCriteria = async () => {
    try {
        const res = await axios.get(`/reviewer/assignments/${props.id}/criteria`);
        criteria.value = res.data.data ?? [];
        // Initialize form.criteriaResponses
        criteria.value.forEach(c => {
            form.value.criteriaResponses[c.id] = form.value.criteriaResponses[c.id] ?? '';
        });
    } catch {
        // Non-fatal: criteria may be empty for this journal
        criteria.value = [];
    }
};

const acceptAssignment = async () => {
    actionProcessing.value = true;
    actionError.value = '';
    try {
        await axios.post(`/reviewer/assignments/${props.id}/accept`);
        await fetchAssignment();
    } catch (err) {
        actionError.value = err.response?.data?.message ?? 'Failed to accept assignment.';
    } finally {
        actionProcessing.value = false;
    }
};

const declineAssignment = async () => {
    if (!confirm('Are you sure you want to decline this assignment?')) return;
    actionProcessing.value = true;
    actionError.value = '';
    try {
        await axios.post(`/reviewer/assignments/${props.id}/decline`);
        await fetchAssignment();
    } catch (err) {
        actionError.value = err.response?.data?.message ?? 'Failed to decline assignment.';
    } finally {
        actionProcessing.value = false;
    }
};

const submitReview = async () => {
    if (!form.value.recommendation) return;
    if (!confirm('Submit your review? This action cannot be undone.')) return;

    processing.value = true;
    submitError.value = '';

    const criteriaResponses = Object.entries(form.value.criteriaResponses)
        .filter(([, v]) => v !== '' && v !== null)
        .map(([criterion_id, response]) => ({ criterion_id: parseInt(criterion_id), response }));

    try {
        await axios.post(`/reviewer/assignments/${props.id}/submit`, {
            recommendation:    form.value.recommendation,
            comments_to_editor: form.value.commentsToEditor || null,
            comments_to_author: form.value.commentsToAuthor || null,
            criteria_responses: criteriaResponses.length ? criteriaResponses : undefined,
        });
        await fetchAssignment();
    } catch (err) {
        if (err.response?.status === 422) {
            // May be locked round
            const msg = err.response?.data?.message ?? 'Validation error.';
            submitError.value = msg;
            if (msg.toLowerCase().includes('lock') || msg.toLowerCase().includes('decision')) {
                roundLocked.value = true;
            }
        } else {
            submitError.value = err.response?.data?.message ?? 'Failed to submit review.';
        }
    } finally {
        processing.value = false;
    }
};

const ratingOptions = (criterion) => {
    const min = criterion.config?.min ?? 1;
    const max = criterion.config?.max ?? 5;
    const opts = [];
    for (let i = min; i <= max; i++) opts.push(i);
    return opts;
};

const formatStatus = (s) => {
    if (!s) return 'Unknown';
    return s.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
};

const formatMode = (m) => {
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
    return new Date(d).toLocaleString(undefined, {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};

const formatFileSize = (bytes) => {
    if (!bytes) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

onMounted(fetchAssignment);
</script>
