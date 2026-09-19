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
                <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane fade show active" id="v-pills-dashboard" role="tabpanel"
                        aria-labelledby="v-pills-dashboard-tab" tabindex="0">
                        <DashboardHome />
                    </div>
                    <div class="tab-pane fade" id="v-pills-profile" role="tabpanel"
                        aria-labelledby="v-pills-profile-tab" tabindex="0">
                        <DashboardProfile />
                    </div>
                    <div class="tab-pane fade" id="v-pills-courses" role="tabpanel"
                        aria-labelledby="v-pills-courses-tab" tabindex="0">
                        <DashboardCourses />
                    </div>
                    <div class="tab-pane fade" id="v-pills-certificate" role="tabpanel"
                        aria-labelledby="v-pills-certificate-tab" tabindex="0">
                        <DashboardCertificates />
                    </div>
                    <div class="tab-pane fade" id="v-pills-payment" role="tabpanel"
                        aria-labelledby="v-pills-payment-tab" tabindex="0">
                        <DashboardPayment />
                    </div>

                    <!-- Journal & Submissions -->
                    <div class="tab-pane fade" id="v-pills-submissions" role="tabpanel"
                        aria-labelledby="v-pills-submissions-tab" tabindex="0">
                        <DashboardSubmissions v-if="authStore.dashboardContext?.submission?.has_submissions" />
                    </div>

                    <!-- Journal Memberships -->
                    <div v-for="membership in authStore.dashboardContext?.memberships || []" :key="'pane-mem-'+membership.journal.id" 
                        class="tab-pane fade" :id="'v-pills-membership-' + membership.journal.id" role="tabpanel" tabindex="0">
                        <DashboardMembership :membership="membership" />
                    </div>

                    <!-- Journal Management -->
                    <div v-for="mgmt in authStore.dashboardContext?.managed_journals || []" :key="'pane-mgmt-'+mgmt.journal.id" 
                        class="tab-pane fade" :id="'v-pills-mgmt-' + mgmt.journal.id" role="tabpanel" tabindex="0">
                        <DashboardJournalManagement :management="mgmt" />
                    </div>
                </div>
            </WorkspaceContainer>
        </template>
    </UserWorkspaceLayout>
</template>

<script setup>
import { onMounted } from "vue";
import { useAuthStore } from "@/stores/auth";
import UserWorkspaceLayout from "../components/UserWorkspaceLayout.vue";
import WorkspaceContainer from "../components/WorkspaceContainer.vue";
import DashboardProfile from "../components/DashboardProfile.vue";
import DashboardHome from "../components/DashboardHome.vue";
import DashboardMenu from "../components/DashboardMenu.vue";
import DashboardSidebar from "../components/DashboardSidebar.vue";
import DashboardCourses from "../components/DashboardCourses.vue";
import DashboardCertificates from "../components/DashboardCertificates.vue";
import DashboardPayment from "../components/DashboardPayment.vue";
import DashboardSubmissions from "../components/DashboardSubmissions.vue";
import DashboardMembership from "../components/DashboardMembership.vue";
import DashboardJournalManagement from "../components/DashboardJournalManagement.vue";

const authStore = useAuthStore();

onMounted(() => {
    authStore.fetchDashboardContext();
});
</script>
