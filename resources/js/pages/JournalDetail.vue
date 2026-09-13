<template>
  <div class="journal-detail py-5">
    <div class="container">
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <div v-else-if="error" class="alert alert-danger text-center">
        {{ error }}
        <div class="mt-3">
          <router-link :to="{ name: 'journal_directory' }" class="btn btn-outline-danger">
            Back to Directory
          </router-link>
        </div>
      </div>

      <div v-else-if="journal">
        <div class="row mb-5">
          <div class="col-lg-8 mx-auto">
            <nav aria-label="breadcrumb" class="mb-4">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><router-link :to="{ name: 'journal_directory' }">Journals</router-link></li>
                <li class="breadcrumb-item active" aria-current="page">{{ journal.title }}</li>
              </ol>
            </nav>

            <div class="card shadow-sm border-0">
              <div class="card-body p-5">
                <h1 class="display-6 fw-bold mb-3 text-primary">{{ journal.title }}</h1>
                
                <div class="d-flex gap-3 mb-4 text-muted border-bottom pb-3">
                  <div v-if="journal.issn">
                    <strong>ISSN:</strong> {{ journal.issn }}
                  </div>
                  <div v-if="journal.eissn">
                    <strong>eISSN:</strong> {{ journal.eissn }}
                  </div>
                </div>

                <div class="mb-5">
                  <h4 class="fw-bold mb-3">About this Journal</h4>
                  <div class="lead" style="white-space: pre-wrap;">{{ journal.description || 'No description available.' }}</div>
                </div>

                <!-- Future placeholder for issues -->
                <div class="alert alert-info mt-5 border-start border-info border-4">
                  <h5 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Upcoming Feature</h5>
                  <p class="mb-0">Issues and scholarly publications will appear here when the publishing module is available.</p>
                </div>
              </div>
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

const journal = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchJournalDetail = async () => {
  loading.value = true;
  error.value = null;

  try {
    const response = await axios.get(`/api/journals/${route.params.slug}`);
    journal.value = response.data.data.journal;
    document.title = `${journal.value.title} | HexaLMS`;
  } catch (err) {
    if (err.response && err.response.status === 404) {
      error.value = 'Journal not found or is no longer available.';
    } else {
      error.value = 'An error occurred while fetching journal details.';
      console.error('Error fetching journal detail:', err);
    }
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchJournalDetail();
});
</script>

<style scoped>
.journal-detail .breadcrumb-item a {
  text-decoration: none;
}
</style>
