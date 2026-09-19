<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">My Submissions</h4>
            <router-link :to="{ name: 'author_submission_list' }" class="btn btn-sm btn-outline-primary">
                View All Submissions <i class="bi bi-arrow-right"></i>
            </router-link>
        </div>
        
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div v-if="loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div v-else-if="submissions.length === 0" class="text-center py-5 text-muted">
                    <i class="bi bi-file-earmark-text fs-1 mb-3 text-secondary"></i>
                    <h5>No Submissions Found</h5>
                    <p>You haven't submitted any manuscripts yet.</p>
                </div>

                <div v-else class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Title</th>
                                <th>Journal</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sub in submissions" :key="sub.id">
                                <td class="ps-4 text-truncate" style="max-width: 250px;">{{ sub.title }}</td>
                                <td>{{ sub.journal?.title || 'Unknown' }}</td>
                                <td><span class="badge bg-secondary text-capitalize">{{ sub.status }}</span></td>
                                <td class="text-end pe-4">
                                    <router-link :to="{ name: 'author_submission_details', params: { id: sub.id } }" class="btn btn-sm btn-light">
                                        View
                                    </router-link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const authStore = useAuthStore();
const submissions = ref([]);
const loading = ref(true);

onMounted(async () => {
    try {
        const response = await axios.get('/submissions', {
            headers: {
                Authorization: 'Bearer ' + authStore.authToken
            },
            params: { per_page: 5 } // only fetch the latest 5 for dashboard
        });
        submissions.value = response.data.data;
    } catch (error) {
        console.error("Error fetching dashboard submissions:", error);
    } finally {
        loading.value = false;
    }
});
</script>
