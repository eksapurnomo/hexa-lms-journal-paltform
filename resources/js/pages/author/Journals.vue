<template>
    <div>
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <div>
                <h3 class="mb-1 fw-bold text-dark">Journals</h3>
                <div class="text-muted small">
                    Browse journals available for submission.
                </div>
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

        <div v-else-if="journals.length === 0" class="text-center py-5 text-muted bg-white shadow-sm rounded">
            <i class="bi bi-journal-x fs-1 d-block mb-3"></i>
            <h5>No Journals Found</h5>
            <p>There are currently no journals available.</p>
        </div>

        <div v-else class="row g-4">
            <div v-for="journal in journals" :key="journal.id" class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0">
                    <img v-if="journal.cover_image" :src="journal.cover_image" class="card-img-top object-fit-cover" alt="Journal Cover" style="height: 150px;">
                    <div v-else class="card-img-top bg-secondary d-flex justify-content-center align-items-center" style="height: 150px;">
                        <i class="bi bi-journal-text text-white fs-1"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-truncate">{{ journal.title }}</h5>
                        <p class="card-text small text-muted text-truncate">{{ journal.description || 'No description provided.' }}</p>
                        <div class="mb-3">
                            <span class="badge bg-primary text-uppercase me-2">{{ journal.status }}</span>
                            <small class="text-muted" v-if="journal.issn">ISSN: {{ journal.issn }}</small>
                        </div>
                        <router-link :to="{ path: '/author/submissions/create', query: { journal: journal.id } }" class="btn btn-outline-primary btn-sm w-100">
                            Submit to this Journal
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(true);
const error = ref(null);
const journals = ref([]);

const fetchJournals = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get('/api/journals');
        journals.value = response.data.data || [];
    } catch (err) {
        error.value = "Failed to load journals.";
        console.error(err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchJournals();
});
</script>
