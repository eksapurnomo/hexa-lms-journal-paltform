<template>
  <div class="journal-directory py-5">
    <div class="container">
      <div class="row mb-4">
        <div class="col-12 text-center">
          <h1 class="display-5 fw-bold">Journal Directory</h1>
          <p class="text-muted lead">Explore our collection of active scholarly journals.</p>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-md-6 mx-auto">
          <div class="input-group">
            <input 
              type="text" 
              class="form-control" 
              placeholder="Search journals by title..." 
              v-model="searchQuery"
              @keyup.enter="fetchJournals(1)"
            >
            <button class="btn btn-primary" type="button" @click="fetchJournals(1)">
              Search
            </button>
          </div>
        </div>
      </div>

      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <div v-else-if="error" class="alert alert-danger text-center">
        {{ error }}
      </div>

      <div v-else-if="journals.length === 0" class="text-center py-5">
        <h4>No journals found</h4>
        <p class="text-muted">Try adjusting your search criteria.</p>
      </div>

      <div v-else>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
          <div class="col" v-for="journal in journals" :key="journal.slug">
            <div class="card h-100 shadow-sm">
              <div class="card-body">
                <h5 class="card-title fw-bold text-primary">{{ journal.title }}</h5>
                <p class="card-text text-muted small mb-3">
                  <span v-if="journal.issn">ISSN: {{ journal.issn }}</span>
                  <span v-if="journal.issn && journal.eissn"> | </span>
                  <span v-if="journal.eissn">eISSN: {{ journal.eissn }}</span>
                </p>
                <p class="card-text">{{ truncate(journal.description, 120) }}</p>
              </div>
              <div class="card-footer bg-white border-top-0 pb-3 text-center">
                <router-link :to="{ name: 'journal_details', params: { slug: journal.slug } }" class="btn btn-outline-primary w-100">
                  View Details
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <div class="row mt-5" v-if="totalItems > perPage">
          <div class="col-12 d-flex justify-content-center">
            <vue-awesome-paginate
              :total-items="totalItems"
              :items-per-page="perPage"
              :max-pages-shown="5"
              v-model="currentPage"
              :on-click="onClickHandler"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const journals = ref([]);
const loading = ref(true);
const error = ref(null);
const searchQuery = ref('');

const currentPage = ref(1);
const totalItems = ref(0);
const perPage = ref(15);

const fetchJournals = async (page = 1) => {
  loading.value = true;
  error.value = null;
  currentPage.value = page;

  try {
    const params = {
      page_number: page,
      items_per_page: perPage.value
    };

    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim();
    }

    const response = await axios.get('/api/journals', { params });
    journals.value = response.data.data.journals;
    totalItems.value = response.data.data.total_journals;
  } catch (err) {
    if (err.response && err.response.status === 404) {
      journals.value = [];
      totalItems.value = 0;
    } else {
      error.value = 'An error occurred while fetching journals. Please try again later.';
      console.error('Error fetching journals:', err);
    }
  } finally {
    loading.value = false;
  }
};

const onClickHandler = (page) => {
  fetchJournals(page);
};

const truncate = (text, length) => {
  if (!text) return '';
  return text.length > length ? text.substring(0, length) + '...' : text;
};

onMounted(() => {
  document.title = 'Journal Directory | HexaLMS';
  fetchJournals();
});
</script>

<style scoped>
.journal-directory .card {
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.journal-directory .card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
</style>
