<template>
    <div class="container my-5">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <router-link :to="{ name: 'journal_directory' }" class="text-decoration-none me-2">
                    <i class="ri-arrow-left-line"></i>
                </router-link>
                Membership Status
            </h4>
        </div>

        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Loading...</p>
        </div>

        <div v-else-if="error" class="alert alert-danger">
            {{ error }}
        </div>

        <div v-else>
            <!-- Application Status Card -->
            <div class="card shadow-sm mb-4" v-if="application">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="mb-0">Application: {{ journal.title }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">Requested Role</div>
                        <div class="col-md-9 fw-bold text-capitalize">{{ application.requested_role }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">Status</div>
                        <div class="col-md-9">
                            <span :class="statusBadgeClass(application.status)">
                                {{ formatStatus(application.status) }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3" v-if="application.submitted_at">
                        <div class="col-md-3 text-muted">Submitted At</div>
                        <div class="col-md-9">{{ new Date(application.submitted_at).toLocaleString() }}</div>
                    </div>
                    
                    <div v-if="application.status === 'needs_revision'" class="alert alert-warning mt-3">
                        <h6 class="alert-heading">Revision Required</h6>
                        <p class="mb-0">{{ application.reviewer_note || 'Please update your application draft and resubmit.' }}</p>
                        <div class="mt-3">
                            <router-link :to="{ name: 'journal_register', params: { slug: journal.slug } }" class="btn btn-warning btn-sm">
                                Resume Application
                            </router-link>
                        </div>
                    </div>

                    <div v-if="application.status === 'rejected'" class="alert alert-danger mt-3">
                        <h6 class="alert-heading">Application Rejected</h6>
                        <p class="mb-0">{{ application.reviewer_note || 'Your application was not approved.' }}</p>
                    </div>
                </div>
            </div>

            <!-- Active Membership Card -->
            <div class="card shadow-sm border-success mb-4" v-if="membership && membership.status === 'active'">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="ri-check-line me-2"></i> Active Membership</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">Journal</div>
                        <div class="col-md-9 fw-bold">{{ journal.title }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">Role</div>
                        <div class="col-md-9 fw-bold text-capitalize">{{ membership.role }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">Member Since</div>
                        <div class="col-md-9">{{ new Date(membership.created_at).toLocaleDateString() }}</div>
                    </div>
                </div>
            </div>

            <!-- Reviewer Capability Card -->
            <div class="card shadow-sm border-info mb-4" v-if="capability">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="ri-user-star-line me-2"></i> Reviewer Capabilities</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Available for Review</div>
                        <div class="col-md-8">
                            <span class="badge" :class="capability.available_for_review ? 'bg-success' : 'bg-secondary'">
                                {{ capability.available_for_review ? 'Yes' : 'No' }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Max Reviews per Month</div>
                        <div class="col-md-8 fw-bold">{{ capability.max_reviews_per_month }}</div>
                    </div>
                    <div class="row mb-3" v-if="capability.years_of_experience">
                        <div class="col-md-4 text-muted">Years of Experience</div>
                        <div class="col-md-8">{{ capability.years_of_experience }}</div>
                    </div>
                </div>
            </div>
            
            <div v-if="!application && !membership" class="alert alert-secondary">
                You do not have any application or membership for this journal.
                <div class="mt-3">
                    <router-link :to="{ name: 'journal_register', params: { slug: journal.slug } }" class="btn btn-primary">
                        Apply Now
                    </router-link>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'MembershipStatus',
    data() {
        return {
            loading: true,
            error: null,
            journal: null,
            application: null,
            membership: null,
            capability: null
        }
    },
    async mounted() {
        try {
            const journalSlug = this.$route.params.slug;
            
            // 1. Resolve Journal
            const journalRes = await axios.get(`/api/journals/${journalSlug}`);
            this.journal = journalRes.data.data.journal;

            // 2. Fetch Application Status
            try {
                const appRes = await axios.get(`/api/journals/${this.journal.slug}/membership-application/status`);
                if (appRes.data && appRes.data.data && appRes.data.data.status) {
                    this.application = appRes.data.data.status;
                }
            } catch (err) {
                // Application not found or draft doesn't exist
            }

            // 3. Fetch Membership
            try {
                const memRes = await axios.get(`/api/journals/${this.journal.slug}/membership`);
                if (memRes.data && memRes.data.data && memRes.data.data.membership) {
                    this.membership = memRes.data.data.membership;

                    // 4. Fetch Reviewer Capability if membership is active reviewer
                    if (this.membership.role === 'reviewer' && this.membership.status === 'active') {
                        try {
                            const capRes = await axios.get(`/api/journals/${this.journal.slug}/reviewer-capability`);
                            if (capRes.data && capRes.data.data && capRes.data.data.capability) {
                                this.capability = capRes.data.data.capability;
                            }
                        } catch (err) {
                            // Capability not found
                        }
                    }
                }
            } catch (err) {
                // Membership not found
            }

        } catch (error) {
            this.error = 'Failed to load status data. Please verify the journal exists.';
            console.error(error);
        } finally {
            this.loading = false;
        }
    },
    methods: {
        formatStatus(status) {
            if (!status) return 'Unknown';
            return status.replace(/_/g, ' ').toUpperCase();
        },
        statusBadgeClass(status) {
            switch(status) {
                case 'draft': return 'badge bg-secondary';
                case 'submitted': return 'badge bg-primary';
                case 'under_review': return 'badge bg-info text-dark';
                case 'needs_revision': return 'badge bg-warning text-dark';
                case 'approved': return 'badge bg-success';
                case 'rejected': return 'badge bg-danger';
                default: return 'badge bg-secondary';
            }
        }
    }
}
</script>
