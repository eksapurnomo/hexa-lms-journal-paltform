<template>
    <main class="px-3 h-100 d-flex flex-column flex-grow-1 bg-light">
        <section class="row flex-grow-1">
            <section class="col-4 border-end d-none d-xl-block col-xl-3 bg-white">
                <DashboardSidebar />
            </section>
            
            <section class="col-xl-9 p-5 bg-white shadow-sm">
                <div v-if="loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div v-else-if="error" class="text-center py-5 text-danger">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                    <h5 class="mt-3">{{ error }}</h5>
                    <button class="btn btn-outline-secondary mt-3" @click="$router.push('/dashboard')">Back to Dashboard</button>
                </div>

                <div v-else class="journal-workspace">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                        <div>
                            <h3 class="mb-1 fw-bold text-dark">{{ context.journal.title }}</h3>
                        </div>
                    </div>

                    <ul class="nav nav-pills mb-4">
                        <li class="nav-item">
                            <router-link :to="`/dashboard/journals/${$route.params.slug}/overview`" class="nav-link text-dark">
                                <i class="bi bi-columns-gap me-2"></i> Overview
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link :to="`/dashboard/journals/${$route.params.slug}/submissions`" class="nav-link text-dark">
                                <i class="bi bi-file-earmark-text me-2"></i> Submissions
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link :to="`/dashboard/journals/${$route.params.slug}/members`" class="nav-link text-dark">
                                <i class="bi bi-people me-2"></i> Members
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link :to="`/dashboard/journals/${$route.params.slug}/editorial-process`" class="nav-link active">
                                <i class="bi bi-diagram-3 me-2"></i> Editorial Process
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link :to="`/dashboard/journals/${$route.params.slug}/settings`" class="nav-link text-dark">
                                <i class="bi bi-gear me-2"></i> Settings
                            </router-link>
                        </li>
                    </ul>

                    <div class="row g-4">
                        <div class="col-12">
                            <h5 class="fw-bold mb-3">Editorial Process Flow</h5>
                            
                            <!-- Process Alert -->
                            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
                                <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
                                <div>
                                    <h6 class="mb-1 fw-bold">{{ context.current_process.process }}</h6>
                                    <span class="small">{{ context.current_process.blocker }}</span>
                                </div>
                            </div>
                            
                            <div class="row g-4">
                                <!-- Editorial Screening -->
                                <div class="col-md-6 col-lg-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <h6 class="fw-bold text-primary mb-3">Editorial Desk</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">Assigned</span>
                                                <span class="fw-bold">{{ context.stats.editorial.assigned }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2" v-if="context.role === 'owner'">
                                                <span class="text-muted small">Unassigned</span>
                                                <span class="fw-bold text-warning">{{ context.stats.editorial.unassigned }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Reviewer Selection -->
                                <div class="col-md-6 col-lg-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <h6 class="fw-bold text-info mb-3">Reviewer Selection</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">Active Rounds</span>
                                                <span class="fw-bold">{{ context.stats.reviewerSelection.rounds }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">Awaiting</span>
                                                <span class="fw-bold text-warning">{{ context.stats.reviewerSelection.awaiting }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">Assigned</span>
                                                <span class="fw-bold">{{ context.stats.reviewerSelection.assigned }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Peer Review -->
                                <div class="col-md-6 col-lg-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <h6 class="fw-bold text-warning mb-3">Peer Review</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">Pending</span>
                                                <span class="fw-bold text-warning">{{ context.stats.peerReview.pending }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">Completed</span>
                                                <span class="fw-bold">{{ context.stats.peerReview.completed }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Editorial Decision -->
                                <div class="col-md-6 col-lg-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <h6 class="fw-bold text-success mb-3">Editorial Decision</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">Pending Decision</span>
                                                <span class="fw-bold text-danger">{{ context.stats.decision.pending }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">Completed</span>
                                                <span class="fw-bold">{{ context.stats.decision.completed }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>
    </main>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';
import DashboardSidebar from "../../components/DashboardSidebar.vue";
import DashboardMenu from "../../components/DashboardMenu.vue";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const loading = ref(true);
const error = ref(null);
const context = ref(null);

const fetchProcessFlow = async (slug) => {
    loading.value = true;
    error.value = null;
    
    try {
        const res = await axios.get(`/user/journals/${slug}/management/editorial-process`, {
            headers: { Authorization: 'Bearer ' + authStore.authToken }
        });
        context.value = res.data.data;
    } catch (err) {
        if (err.response && err.response.status === 403) {
            error.value = "Unauthorized: You do not have management permissions for this journal.";
        } else if (err.response && err.response.status === 404) {
            error.value = "Journal not found.";
        } else {
            error.value = "An error occurred while loading the editorial process.";
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    if (route.params.slug) {
        fetchProcessFlow(route.params.slug);
    }
});

watch(() => route.params.slug, (newSlug) => {
    if (newSlug) {
        fetchProcessFlow(newSlug);
    }
});
</script>
