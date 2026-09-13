<template>
    <div v-if="loading" class="text-center p-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    
    <div v-else-if="error" class="alert alert-danger m-4">
        {{ error }}
        <div class="mt-3">
            <router-link :to="{ name: 'editorial.list' }" class="btn btn-outline-danger">Back to List</router-link>
        </div>
    </div>

    <div v-else-if="submission" class="submission-detail">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">
                <router-link :to="{ name: 'editorial.list' }" class="text-decoration-none text-muted me-2">
                    <i class="fa fa-arrow-left"></i>
                </router-link>
                Submission #{{ submission.id }}
            </h4>
            <span class="badge fs-6" :class="statusBadgeClass(submission.status)">
                {{ formatStatus(submission.status) }}
            </span>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Main Metadata -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Manuscript Details</h5>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title">{{ submission.title }}</h3>
                        <p class="text-muted mb-4">{{ submission.journal?.title }}</p>
                        
                        <h6>Abstract</h6>
                        <p class="card-text">{{ submission.abstract }}</p>

                        <h6 class="mt-4">Authors</h6>
                        <ul class="list-group mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center" v-for="author in submission.authors" :key="author.id">
                                <div>
                                    <i class="fa fa-user text-muted me-2"></i>
                                    {{ author.name }} 
                                    <span v-if="author.is_corresponding" class="badge bg-primary ms-2">Corresponding</span>
                                </div>
                                <span class="text-muted small">{{ author.email }}</span>
                            </li>
                        </ul>

                        <div v-if="submission.revisions && submission.revisions.length > 0">
                            <div v-for="revision in submission.revisions" :key="revision.id" class="mb-4 border rounded p-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="m-0 text-primary">Revision {{ revision.version_number }}</h6>
                                    <span class="text-muted small">{{ formatDate(revision.created_at) }}</span>
                                </div>
                                
                                <strong class="small text-secondary text-uppercase d-block mb-2">Files</strong>
                                <ul class="list-group mb-3">
                                    <li class="list-group-item d-flex justify-content-between align-items-center" v-for="file in revision.files" :key="file.id">
                                        <div>
                                            <i class="fa fa-file-pdf-o text-danger me-2"></i>
                                            {{ file.original_name }}
                                            <small class="text-muted ms-2">{{ formatFileSize(file.size) }}</small>
                                        </div>
                                        <a :href="`/api/submissions/${submission.id}/files/${file.id}/download`" target="_blank" class="btn btn-sm btn-outline-primary">
                                            Download
                                        </a>
                                    </li>
                                    <li v-if="!revision.files || revision.files.length === 0" class="list-group-item text-muted small">No files.</li>
                                </ul>

                                <strong class="small text-secondary text-uppercase d-block mb-2">Review Rounds</strong>
                                <div v-if="revision.review_rounds && revision.review_rounds.length > 0">
                                    <div v-for="round in revision.review_rounds" :key="round.id" class="card mb-2 border">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="card-title m-0">Round {{ round.round_number }}</h6>
                                                <small class="text-muted">{{ formatDate(round.created_at) }}</small>
                                            </div>
                                            
                                            <!-- Editorial Decision (read-only display) -->
                                            <div v-if="round.editorial_decision" class="alert alert-info py-2 px-3 mb-2">
                                                <strong>Decision: {{ formatStatus(round.editorial_decision.decision) }}</strong>
                                                <div class="small text-muted mt-1">{{ round.editorial_decision.comments || 'No additional comments.' }}</div>
                                                <div class="small text-muted">{{ formatDate(round.editorial_decision.created_at) }}</div>
                                            </div>
                                            
                                            <!-- Assignments -->
                                            <div v-if="round.assignments && round.assignments.length > 0" class="mt-2">
                                                <strong class="small text-muted d-block mb-1">Reviewer Assignments:</strong>
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item px-0 py-2 border-0" v-for="assignment in round.assignments" :key="assignment.id">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <i class="fa fa-user text-muted me-1"></i>
                                                                <strong>{{ assignment.reviewer?.name || `Reviewer #${assignment.reviewer_id}` }}</strong>
                                                                <small class="text-muted ms-2">{{ assignment.reviewer?.email }}</small>
                                                                <span class="badge ms-2" :class="assignmentBadgeClass(assignment.status)">{{ formatStatus(assignment.status) }}</span>
                                                                <div class="small text-muted">Mode: {{ formatStatus(assignment.review_mode) }}</div>
                                                            </div>
                                                            <button
                                                                v-if="!['submitted','cancelled','declined'].includes(assignment.status)"
                                                                @click="cancelAssignment(submission.id, assignment.id)"
                                                                class="btn btn-sm btn-outline-danger"
                                                                :disabled="processing">
                                                                Cancel
                                                            </button>
                                                        </div>
                                                        <!-- Peer Review -->
                                                        <div v-if="assignment.peer_review" class="mt-2 p-2 bg-light border rounded small">
                                                            <strong>Recommendation:</strong> {{ formatStatus(assignment.peer_review.recommendation) }}
                                                            <div v-if="assignment.peer_review.comments_to_editor" class="mt-1">
                                                                <strong>To Editor:</strong> {{ assignment.peer_review.comments_to_editor }}
                                                            </div>
                                                            <div v-if="assignment.peer_review.comments_to_author" class="mt-1">
                                                                <strong>To Author:</strong> {{ assignment.peer_review.comments_to_author }}
                                                            </div>
                                                            <div class="text-muted mt-1">Submitted: {{ formatDate(assignment.peer_review.submitted_at) }}</div>
                                                        </div>
                                                        <div v-else class="small text-muted mt-1 fst-italic">Peer review not yet submitted.</div>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div v-else class="mt-2 text-muted small">No reviewers assigned to this round yet.</div>

                                            <!-- Record Decision form (only if no decision yet and round has at least one submitted review) -->
                                            <div v-if="!round.editorial_decision && canProcess" class="mt-3 border-top pt-3">
                                                <strong class="small d-block mb-2">Record Editorial Decision</strong>
                                                <div class="mb-2">
                                                    <select class="form-select form-select-sm" v-model="decisionForms[round.id].decision">
                                                        <option value="" disabled>Select decision...</option>
                                                        <option value="accept">Accept</option>
                                                        <option value="revision_required">Revision Required</option>
                                                        <option value="reject">Reject</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <textarea class="form-control form-control-sm" rows="2"
                                                        placeholder="Comments (optional)"
                                                        v-model="decisionForms[round.id].comments"></textarea>
                                                </div>
                                                <button
                                                    class="btn btn-sm btn-primary"
                                                    :disabled="!decisionForms[round.id].decision || processing"
                                                    @click="recordDecision(submission.id, round.id, decisionForms[round.id])">
                                                    {{ processing ? 'Saving...' : 'Record Decision' }}
                                                </button>
                                                <small v-if="decisionForms[round.id].error" class="text-danger d-block mt-1">{{ decisionForms[round.id].error }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-muted small">No review rounds started for this revision.</div>
                            </div>
                        </div>
                        <div v-else class="text-muted">No revisions available.</div>
                    </div>
                </div>

                <!-- Editorial Audit Trail -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Editorial History</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item py-3" v-for="event in submission.editorial_events" :key="event.id">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ formatAction(event.action) }}</strong>
                                    <small class="text-muted">{{ formatDate(event.created_at) }}</small>
                                </div>
                                <div class="text-muted small mt-1">
                                    By: {{ event.user?.name || 'System' }}
                                </div>
                            </li>
                            <li v-if="!submission.editorial_events || submission.editorial_events.length === 0" class="list-group-item text-center text-muted py-4">
                                No editorial history available.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Editor Assignment -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Editor Assignment</h5>
                    </div>
                    <div class="card-body">
                        <div v-if="submission.editor">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-light rounded-circle p-3 me-3">
                                    <i class="fa fa-user-circle fa-2x text-secondary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ submission.editor.name }}</h6>
                                    <small class="text-muted">{{ submission.editor.email }}</small>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-muted mb-3">
                            <em>No editor assigned.</em>
                        </div>

                        <hr>
                        
                        <div v-if="!canAssign" class="alert alert-secondary small mb-0">
                            You are not authorized to assign an editor for this submission.
                        </div>
                        <div v-else>
                            <label class="form-label text-muted small mb-1">{{ submission.editor ? 'Reassign Editor' : 'Assign Editor' }}</label>
                            <div class="input-group">
                                <select class="form-select" v-model="selectedEditorId" :disabled="processingAssign">
                                    <option value="" disabled>Select an editor...</option>
                                    <option v-for="editor in eligibleEditors" :key="editor.id" :value="editor.id">
                                        {{ editor.name }} ({{ editor.email }})
                                    </option>
                                </select>
                                <button class="btn btn-outline-primary" @click="assignEditor" :disabled="!selectedEditorId || processingAssign">
                                    {{ processingAssign ? 'Saving...' : 'Assign' }}
                                </button>
                            </div>
                            <small v-if="assignError" class="text-danger mt-1 d-block">{{ assignError }}</small>
                        </div>
                    </div>
                </div>

                <!-- Reviewer Assignment -->
                <div class="card mb-4" v-if="canProcess && ['review_pending', 'editorial_assessment'].includes(submission.status)">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Assign Reviewer</h5>
                    </div>
                    <div class="card-body">
                        <div v-if="eligibleReviewers.length === 0" class="text-muted small">
                            No eligible reviewers available for this journal.
                        </div>
                        <div v-else>
                            <label class="form-label text-muted small mb-1">Select Reviewer</label>
                            <div class="mb-2">
                                <select class="form-select form-select-sm" v-model="selectedReviewerId">
                                    <option value="" disabled>Select a reviewer...</option>
                                    <option v-for="reviewer in eligibleReviewers" :key="reviewer.id" :value="reviewer.id">
                                        {{ reviewer.name }} ({{ reviewer.email }})
                                    </option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <select class="form-select form-select-sm" v-model="selectedReviewMode">
                                    <option value="blind">Blind</option>
                                    <option value="double_blind">Double Blind</option>
                                    <option value="open">Open</option>
                                </select>
                            </div>
                            <button
                                class="btn btn-sm btn-primary w-100"
                                :disabled="!selectedReviewerId || processingReviewer"
                                @click="assignReviewer">
                                {{ processingReviewer ? 'Assigning...' : 'Assign Reviewer' }}
                            </button>
                            <small v-if="reviewerError" class="text-danger d-block mt-1">{{ reviewerError }}</small>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Select a valid transition for the current state.
                        </p>

                        <div v-if="!canProcess" class="alert alert-secondary small mb-0">
                            You are not authorized to perform editorial actions.
                        </div>

                        <!-- submitted / revision_submitted -->
                        <div v-else-if="submission.status === 'submitted' || submission.status === 'revision_submitted'">
                            <button @click="transitionStatus('editorial_assessment')" class="btn btn-warning w-100 mb-2" :disabled="processing">
                                Begin Editorial Assessment
                            </button>
                        </div>

                        <!-- editorial_assessment -->
                        <div v-else-if="submission.status === 'editorial_assessment'">
                            <button @click="transitionStatus('review_pending')" class="btn btn-primary w-100 mb-2" :disabled="processing">
                                Send to Peer Review
                            </button>
                            <button @click="transitionStatus('revision_required')" class="btn btn-danger w-100 mb-2" :disabled="processing">
                                Request Revision
                            </button>
                            <button @click="transitionStatus('accepted')" class="btn btn-success w-100 mb-2" :disabled="processing">
                                Accept (without review)
                            </button>
                            <button @click="transitionStatus('rejected')" class="btn btn-dark w-100 mb-2" :disabled="processing">
                                Reject Submission
                            </button>
                        </div>

                        <!-- review_pending -->
                        <div v-else-if="submission.status === 'review_pending'">
                            <button @click="transitionStatus('accepted')" class="btn btn-success w-100 mb-2" :disabled="processing">
                                Accept
                            </button>
                            <button @click="transitionStatus('revision_required')" class="btn btn-danger w-100 mb-2" :disabled="processing">
                                Request Revision
                            </button>
                            <button @click="transitionStatus('rejected')" class="btn btn-dark w-100 mb-2" :disabled="processing">
                                Reject
                            </button>
                        </div>

                        <!-- terminal / other states -->
                        <div v-else class="text-center text-muted py-3">
                            <em>No actions available in current state.</em>
                        </div>

                        <small v-if="actionError" class="text-danger d-block mt-2">{{ actionError }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    id: {
        type: [String, Number],
        required: true
    }
});

const submission = ref(null);
const loading = ref(true);
const error = ref(null);
const processing = ref(false);
const actionError = ref('');

// Editor assignment
const eligibleEditors = ref([]);
const canAssign = ref(false);
const selectedEditorId = ref('');
const processingAssign = ref(false);
const assignError = ref('');

// Reviewer assignment
const canProcess = ref(false);
const eligibleReviewers = ref([]);
const selectedReviewerId = ref('');
const selectedReviewMode = ref('blind');
const processingReviewer = ref(false);
const reviewerError = ref('');

// Per-round decision forms
const decisionForms = reactive({});

const initDecisionForms = (revisions) => {
    if (!revisions) return;
    revisions.forEach(rev => {
        if (!rev.review_rounds) return;
        rev.review_rounds.forEach(round => {
            if (!decisionForms[round.id]) {
                decisionForms[round.id] = { decision: '', comments: '', error: '' };
            }
        });
    });
};

const fetchSubmission = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get(`/editorial/submissions/${props.id}`);
        submission.value = response.data.data;
        initDecisionForms(submission.value.revisions);
        await Promise.all([fetchEligibleEditors(), fetchEligibleReviewers()]);
    } catch (err) {
        if (err.response && err.response.status === 403) {
            error.value = "Access Denied: You do not have permission to view this submission.";
        } else if (err.response && err.response.status === 404) {
            error.value = "Submission not found.";
        } else {
            error.value = "Failed to load submission details.";
        }
    } finally {
        loading.value = false;
    }
};

const fetchEligibleEditors = async () => {
    try {
        const response = await axios.get(`/editorial/submissions/${props.id}/eligible-editors`);
        eligibleEditors.value = response.data.data;
        canAssign.value = true;
    } catch (err) {
        if (err.response && err.response.status === 403) {
            canAssign.value = false;
        }
    }
};

const fetchEligibleReviewers = async () => {
    try {
        const response = await axios.get(`/editorial/submissions/${props.id}/eligible-reviewers`);
        eligibleReviewers.value = response.data.data;
        canProcess.value = true;
    } catch (err) {
        if (err.response && err.response.status === 403) {
            canProcess.value = false;
        }
    }
};

const assignEditor = async () => {
    if (!selectedEditorId.value) return;
    processingAssign.value = true;
    assignError.value = '';
    try {
        const response = await axios.patch(`/editorial/submissions/${props.id}/assign`, {
            editor_id: selectedEditorId.value
        });
        submission.value = response.data.data;
        selectedEditorId.value = '';
    } catch (err) {
        assignError.value = err.response?.data?.message || 'Failed to assign editor.';
    } finally {
        processingAssign.value = false;
    }
};

const assignReviewer = async () => {
    if (!selectedReviewerId.value) return;
    processingReviewer.value = true;
    reviewerError.value = '';
    try {
        await axios.post(`/editorial/submissions/${props.id}/review-assignments`, {
            reviewer_id: selectedReviewerId.value,
            review_mode: selectedReviewMode.value,
        });
        selectedReviewerId.value = '';
        await fetchSubmission();
    } catch (err) {
        reviewerError.value = err.response?.data?.message || 'Failed to assign reviewer.';
    } finally {
        processingReviewer.value = false;
    }
};

const cancelAssignment = async (submissionId, assignmentId) => {
    if (!confirm('Cancel this reviewer assignment?')) return;
    processing.value = true;
    actionError.value = '';
    try {
        await axios.delete(`/editorial/submissions/${submissionId}/review-assignments/${assignmentId}`);
        await fetchSubmission();
    } catch (err) {
        actionError.value = err.response?.data?.message || 'Failed to cancel assignment.';
    } finally {
        processing.value = false;
    }
};

const recordDecision = async (submissionId, roundId, form) => {
    if (!form.decision) return;
    form.error = '';
    processing.value = true;
    try {
        await axios.post(`/editorial/submissions/${submissionId}/rounds/${roundId}/decision`, {
            decision: form.decision,
            comments: form.comments,
        });
        await fetchSubmission();
    } catch (err) {
        form.error = err.response?.data?.message || 'Failed to record decision.';
    } finally {
        processing.value = false;
    }
};

const transitionStatus = async (newStatus) => {
    if (!confirm(`Transition this submission to "${formatStatus(newStatus)}"?`)) return;
    processing.value = true;
    actionError.value = '';
    try {
        const response = await axios.patch(`/editorial/submissions/${props.id}/status`, {
            status: newStatus
        });
        submission.value = response.data.data;
        initDecisionForms(submission.value.revisions);
    } catch (err) {
        actionError.value = err.response?.data?.message || 'Failed to update status.';
    } finally {
        processing.value = false;
    }
};

const formatStatus = (status) => {
    if (!status) return 'Unknown';
    return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const formatAction = (action) => {
    if (!action) return 'Unknown Action';
    return action.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const statusBadgeClass = (status) => {
    switch (status) {
        case 'submitted': return 'bg-info text-dark';
        case 'editorial_assessment': return 'bg-warning text-dark';
        case 'revision_required': return 'bg-danger';
        case 'revision_submitted': return 'bg-info text-dark';
        case 'review_pending': return 'bg-primary';
        case 'accepted': return 'bg-success';
        case 'rejected': return 'bg-dark';
        default: return 'bg-secondary';
    }
};

const assignmentBadgeClass = (status) => {
    switch (status) {
        case 'assigned': return 'bg-warning text-dark';
        case 'accepted': return 'bg-primary';
        case 'in_progress': return 'bg-info text-dark';
        case 'submitted': return 'bg-success';
        case 'cancelled': return 'bg-dark';
        case 'declined': return 'bg-danger';
        default: return 'bg-secondary';
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleString(undefined, { 
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

onMounted(() => {
    fetchSubmission();
});
</script>
