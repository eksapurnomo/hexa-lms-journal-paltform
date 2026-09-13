<template>
    <div class="container my-5">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <router-link :to="{ name: 'author_submission_list' }" class="text-decoration-none me-2">
                    <i class="ri-arrow-left-line"></i>
                </router-link>
                Submit New Manuscript
            </h4>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form @submit.prevent="submitForm">
                    <div v-if="error" class="alert alert-danger mb-4">{{ error }}</div>
                    <div v-if="success" class="alert alert-success mb-4">Submission created successfully. Redirecting...</div>

                    <!-- Step 1: Journal Selection -->
                    <h5 class="border-bottom pb-2 mb-3">1. Select Journal</h5>
                    <div class="mb-4">
                        <label class="form-label">Target Journal <span class="text-danger">*</span></label>
                        <select v-model="form.journal_id" class="form-select" required :disabled="loadingJournals || submitting">
                            <option value="" disabled>-- Select a Journal --</option>
                            <option v-for="journal in journals" :key="journal.id" :value="journal.id">
                                {{ journal.title }}
                            </option>
                        </select>
                        <div v-if="loadingJournals" class="form-text text-muted">Loading journals...</div>
                    </div>

                    <!-- Step 2: Metadata -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">2. Manuscript Details</h5>
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" v-model="form.title" class="form-control" required :disabled="submitting">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Abstract</label>
                        <textarea v-model="form.abstract" class="form-control" rows="5" :disabled="submitting"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Keywords</label>
                        <input type="text" v-model="keywordsInput" @keydown.enter.prevent="addKeyword" placeholder="Type and press enter" class="form-control mb-2" :disabled="submitting">
                        <div>
                            <span v-for="(kw, idx) in form.keywords" :key="idx" class="badge bg-secondary me-2 p-2">
                                {{ kw }}
                                <i class="ri-close-line ms-1" style="cursor: pointer;" @click="removeKeyword(idx)"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Step 3: Authors -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4 d-flex justify-content-between align-items-center">
                        3. Authors
                        <button type="button" class="btn btn-sm btn-outline-primary" @click="addAuthor" :disabled="submitting">
                            <i class="ri-add-line"></i> Add Author
                        </button>
                    </h5>
                    
                    <div v-for="(author, idx) in form.authors" :key="idx" class="border p-3 mb-3 rounded bg-light position-relative">
                        <button v-if="form.authors.length > 1" type="button" class="btn-close position-absolute top-0 end-0 m-2" @click="removeAuthor(idx)" :disabled="submitting"></button>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" v-model="author.first_name" class="form-control" required :disabled="submitting">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" v-model="author.last_name" class="form-control" :disabled="submitting">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" v-model="author.email" class="form-control" :disabled="submitting">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Affiliation</label>
                                <input type="text" v-model="author.affiliation" class="form-control" :disabled="submitting">
                            </div>
                            <div class="col-12 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" :name="'corresponding_author'" :id="'corr_'+idx" :checked="author.is_corresponding" @change="setCorrespondingAuthor(idx)" :disabled="submitting">
                                    <label class="form-check-label" :for="'corr_'+idx">
                                        Primary Contact (Corresponding Author)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 d-flex justify-content-end border-top pt-3">
                        <router-link :to="{ name: 'author_submission_list' }" class="btn btn-light me-2" :disabled="submitting">Cancel</router-link>
                        <button type="submit" class="btn btn-primary" :disabled="submitting">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Save Draft & Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import axios from 'axios';

const router = useRouter();
const authStore = useAuthStore();

const journals = ref([]);
const loadingJournals = ref(true);
const submitting = ref(false);
const error = ref(null);
const success = ref(false);

const keywordsInput = ref('');

const form = ref({
    journal_id: '',
    title: '',
    abstract: '',
    keywords: [],
    authors: [
        {
            first_name: authStore.userData?.name?.split(' ')[0] || '',
            last_name: authStore.userData?.name?.split(' ').slice(1).join(' ') || '',
            email: authStore.userData?.email || '',
            affiliation: '',
            is_corresponding: true
        }
    ]
});

const loadJournals = async () => {
    loadingJournals.value = true;
    try {
        // Fetch all active journals (publicly accessible or specific endpoint)
        const response = await axios.get('/api/journals'); // Assuming there's a public/author journal list
        journals.value = response.data.data || response.data; // Handle pagination if necessary
    } catch (err) {
        console.error("Failed to load journals", err);
    } finally {
        loadingJournals.value = false;
    }
};

const addKeyword = () => {
    const kw = keywordsInput.value.trim();
    if (kw && !form.value.keywords.includes(kw)) {
        form.value.keywords.push(kw);
    }
    keywordsInput.value = '';
};

const removeKeyword = (idx) => {
    form.value.keywords.splice(idx, 1);
};

const addAuthor = () => {
    form.value.authors.push({
        first_name: '',
        last_name: '',
        email: '',
        affiliation: '',
        is_corresponding: false
    });
};

const removeAuthor = (idx) => {
    form.value.authors.splice(idx, 1);
    // Ensure at least one corresponding author
    const hasCorr = form.value.authors.some(a => a.is_corresponding);
    if (!hasCorr && form.value.authors.length > 0) {
        form.value.authors[0].is_corresponding = true;
    }
};

const setCorrespondingAuthor = (index) => {
    form.value.authors.forEach((a, idx) => {
        a.is_corresponding = (idx === index);
    });
};

const submitForm = async () => {
    error.value = null;
    submitting.value = true;

    if (!form.value.journal_id) {
        error.value = "Please select a target journal.";
        submitting.value = false;
        return;
    }

    try {
        const response = await axios.post('/api/submissions', form.value);
        success.value = true;
        const newSubmissionId = response.data.data.id;
        
        setTimeout(() => {
            router.push({ name: 'author_submission_details', params: { id: newSubmissionId } });
        }, 1500);
    } catch (err) {
        if (err.response && err.response.data && err.response.data.errors) {
            // Flatten errors
            error.value = Object.values(err.response.data.errors).flat().join(" ");
        } else {
            error.value = err.response?.data?.message || "Failed to create submission.";
        }
    } finally {
        submitting.value = false;
    }
};

onMounted(() => {
    loadJournals();
});
</script>
