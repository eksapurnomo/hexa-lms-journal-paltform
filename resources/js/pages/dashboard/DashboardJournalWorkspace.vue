<template>
    <UserWorkspaceLayout>
        <template #sidebar>
            <DashboardSidebar />
        </template>
        
        <template #mobile-nav>
            <DashboardMenu />
        </template>
        
        <template #content>
            <WorkspaceContainer type="standard">

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

                <!-- Workspace Content -->
                <div v-else class="journal-workspace">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                        <div>
                            <h3 class="mb-1 fw-bold text-dark">{{ context.journal.title }}</h3>
                            <div class="text-muted small">
                                <span v-if="context.journal.issn" class="me-3"><i class="bi bi-upc-scan me-1"></i> ISSN: {{ context.journal.issn }}</span>
                                <span v-if="context.journal.eissn" class="me-3"><i class="bi bi-upc-scan me-1"></i> eISSN: {{ context.journal.eissn }}</span>
                                <span class="badge bg-secondary text-uppercase">{{ context.journal.status }}</span>
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-primary text-uppercase px-3 py-2 shadow-sm rounded-pill">
                                {{ context.role }}
                            </span>
                        </div>
                    </div>

                    <!-- Module Navigation -->
                    <ul class="nav nav-pills mb-4">
                        <li class="nav-item">
                            <router-link :to="`/dashboard/journals/${$route.params.slug}/overview`" class="nav-link active">
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
                            <router-link :to="`/dashboard/journals/${$route.params.slug}/editorial-process`" class="nav-link text-dark">
                                <i class="bi bi-diagram-3 me-2"></i> Editorial Process
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link :to="`/dashboard/journals/${$route.params.slug}/settings`" class="nav-link text-dark">
                                <i class="bi bi-gear me-2"></i> Settings
                            </router-link>
                        </li>
                    </ul>

                    <!-- Overview Content -->
                    <div class="row g-4">
                        <div class="col-md-8">
                            <h5 class="fw-bold mb-3"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Editorial Snapshot</h5>
                            <div class="row g-3">
                                <div class="col-sm-6 col-lg-4">
                                    <div class="card bg-primary text-white shadow-sm border-0 h-100">
                                        <div class="card-body text-center">
                                            <h1 class="display-5 fw-bold mb-0">{{ context.editorial_snapshot.new_submissions }}</h1>
                                            <span class="small text-uppercase">New Submissions</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-4">
                                    <div class="card bg-info text-white shadow-sm border-0 h-100">
                                        <div class="card-body text-center">
                                            <h1 class="display-5 fw-bold mb-0">{{ context.editorial_snapshot.under_review }}</h1>
                                            <span class="small text-uppercase">Under Review</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-4">
                                    <div class="card bg-warning text-dark shadow-sm border-0 h-100">
                                        <div class="card-body text-center">
                                            <h1 class="display-5 fw-bold mb-0">{{ context.editorial_snapshot.revision }}</h1>
                                            <span class="small text-uppercase">In Revision</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-6">
                                    <div class="card bg-success text-white shadow-sm border-0 h-100">
                                        <div class="card-body text-center">
                                            <h1 class="display-5 fw-bold mb-0">{{ context.editorial_snapshot.accepted }}</h1>
                                            <span class="small text-uppercase">Accepted</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-6">
                                    <div class="card bg-danger text-white shadow-sm border-0 h-100">
                                        <div class="card-body text-center">
                                            <h1 class="display-5 fw-bold mb-0">{{ context.editorial_snapshot.rejected }}</h1>
                                            <span class="small text-uppercase">Rejected</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <h5 class="fw-bold mb-3"><i class="bi bi-person-workspace me-2 text-primary"></i>My Editorial Work</h5>
                            <div class="card shadow-sm border-0 bg-light mb-3" v-if="context.my_editorial_work">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                        <span class="text-muted fw-semibold">Assigned Submissions</span>
                                        <span class="badge bg-secondary rounded-pill fs-6">{{ context.my_editorial_work.assigned_submissions }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted fw-semibold">Pending Actions</span>
                                        <span class="badge bg-danger rounded-pill fs-6">{{ context.my_editorial_work.pending_actions }}</span>
                                    </div>
                                </div>
                            </div>

                            <h5 class="fw-bold mb-3 mt-4"><i class="bi bi-lightning-charge me-2 text-primary"></i>Quick Actions</h5>
                            <div class="list-group shadow-sm border-0">
                                <a href="#" class="list-group-item list-group-item-action disabled" title="Coming in next phase">
                                    <i class="bi bi-file-earmark-plus me-2 text-muted"></i> Assign Reviewers
                                </a>
                                <a href="#" class="list-group-item list-group-item-action disabled" title="Coming in next phase">
                                    <i class="bi bi-envelope-check me-2 text-muted"></i> Record Editorial Decision
                                </a>
                                <a href="#" class="list-group-item list-group-item-action disabled" title="Coming in next phase">
                                    <i class="bi bi-gear me-2 text-muted"></i> Journal Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </WorkspaceContainer>
        </template>
    </UserWorkspaceLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';
import UserWorkspaceLayout from "../../components/UserWorkspaceLayout.vue";
import WorkspaceContainer from "../../components/WorkspaceContainer.vue";
import DashboardSidebar from "../../components/DashboardSidebar.vue";
import DashboardMenu from "../../components/DashboardMenu.vue";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const loading = ref(true);
const error = ref(null);
const context = ref(null);

const fetchWorkspaceContext = async (slug) => {
    loading.value = true;
    error.value = null;
    
    try {
        const response = await axios.get(`/user/journals/${slug}/management/overview`, {
            headers: {
                Authorization: 'Bearer ' + authStore.authToken
            }
        });
        
        context.value = response.data.data;
    } catch (err) {
        if (err.response && err.response.status === 403) {
            error.value = "Unauthorized: You do not have management permissions for this journal.";
        } else if (err.response && err.response.status === 404) {
            error.value = "Journal not found.";
        } else {
            error.value = "An error occurred while loading the workspace.";
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    if (route.params.slug) {
        fetchWorkspaceContext(route.params.slug);
    }
});

// React to route parameter changes if user navigates between journals without full reload
watch(() => route.params.slug, (newSlug) => {
    if (newSlug) {
        fetchWorkspaceContext(newSlug);
    }
});
</script>
