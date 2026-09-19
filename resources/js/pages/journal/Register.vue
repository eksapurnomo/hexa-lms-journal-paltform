<template>
    <div class="container my-5">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <router-link :to="{ name: 'journal_directory' }" class="text-decoration-none me-2">
                    <i class="ri-arrow-left-line"></i>
                </router-link>
                Journal Membership Registration
            </h4>
        </div>

        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Loading...</p>
        </div>

        <div v-else-if="error" class="alert alert-danger">
            {{ error }}
        </div>

        <div v-else class="card shadow-sm">
            <div class="card-body p-4">
                <div class="alert alert-info mb-4">
                    <i class="ri-information-line me-1"></i> You are applying to <strong>{{ journal.title }}</strong>.
                </div>

                <form @submit.prevent="saveDraft">
                    
                    <!-- Section 1: Account -->
                    <h5 class="border-bottom pb-2 mb-3">1. Account Information</h5>
                    <div class="row g-3 mb-4 text-muted">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" :value="user.name" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" :value="user.email" readonly disabled>
                        </div>
                    </div>

                    <!-- Section 2: Academic Identity -->
                    <h5 class="border-bottom pb-2 mb-3">2. Academic Identity</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Academic Type <span class="text-danger">*</span></label>
                            <select v-model="form.academic_type" class="form-select" required>
                                <option value="lecturer">Lecturer</option>
                                <option value="researcher">Researcher</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Highest Degree</label>
                            <input type="text" v-model="form.highest_degree" class="form-control" placeholder="e.g. PhD, MSc">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Academic Position</label>
                            <input type="text" v-model="form.academic_position" class="form-control" placeholder="e.g. Professor, Postdoc">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Institution Type</label>
                            <select v-model="form.institution_type" class="form-select">
                                <option value="">-- Select Type --</option>
                                <option value="university">University</option>
                                <option value="research_institute">Research Institute</option>
                                <option value="government_research">Government Research</option>
                                <option value="ngo">NGO</option>
                                <option value="private_research">Private Research</option>
                                <option value="think_tank">Think Tank</option>
                                <option value="independent">Independent</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Institution Name</label>
                            <input type="text" v-model="form.institution" class="form-control" placeholder="Enter institution name" :disabled="form.institution_type === 'independent'">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <input type="text" v-model="form.department" class="form-control" :disabled="form.institution_type === 'independent'">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Country</label>
                            <input type="text" v-model="form.country" class="form-control">
                        </div>
                    </div>

                    <!-- Section 3: Researcher Profile -->
                    <h5 class="border-bottom pb-2 mb-3">3. Researcher Profile (Optional)</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">ORCID</label>
                            <input type="text" v-model="form.orcid" class="form-control" placeholder="0000-0000-0000-0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SINTA ID (Indonesia)</label>
                            <input type="text" v-model="form.sinta_id" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Scopus Author ID</label>
                            <input type="text" v-model="form.scopus_author_id" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Google Scholar URL</label>
                            <input type="url" v-model="form.google_scholar_url" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Institutional Email</label>
                            <input type="email" v-model="form.institutional_email" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Biography</label>
                            <textarea v-model="form.biography" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Research Interests</label>
                            <textarea v-model="form.research_interests" class="form-control" rows="2" placeholder="e.g. Artificial Intelligence, Data Science"></textarea>
                        </div>
                    </div>

                    <!-- Section 4: Membership & Role -->
                    <h5 class="border-bottom pb-2 mb-3">4. Requested Role</h5>
                    <div class="mb-4">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" v-model="form.requested_role" value="member" id="roleMember" :disabled="hasDraft">
                            <label class="form-check-label" for="roleMember">Member</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" v-model="form.requested_role" value="reviewer" id="roleReviewer" :disabled="hasDraft">
                            <label class="form-check-label" for="roleReviewer">Reviewer</label>
                        </div>
                        <div class="form-text mt-2 text-muted">
                            <i class="ri-alert-line"></i> Requested role is subject to journal verification and does not automatically grant access.
                        </div>
                    </div>

                    <!-- Section 5: Reviewer Specifics -->
                    <template v-if="form.requested_role === 'reviewer'">
                        <h5 class="border-bottom pb-2 mb-3">5. Reviewer Information</h5>
                        <div class="mb-4">
                            <label class="form-label">Recruitment Source (Optional)</label>
                            <input type="text" v-model="form.recruitment_source" class="form-control" placeholder="e.g. Invited by Editor, Colleague referral">
                        </div>
                        
                        <div class="mb-4 p-3 border rounded bg-light">
                            <h6>Declarations</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="declare1" v-model="declarations.confidentiality" required>
                                <label class="form-check-label" for="declare1">
                                    I agree to keep all manuscript details confidential during the review process. <span class="text-danger">*</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="declare2" v-model="declarations.coi" required>
                                <label class="form-check-label" for="declare2">
                                    I will declare any conflicts of interest before accepting a review assignment. <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                    </template>

                    <div class="d-flex justify-content-end mt-5 pt-3 border-top">
                        <button v-if="hasDraft" type="button" class="btn btn-success me-3" @click="submitApplication" :disabled="submitting">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Final Submit for Review
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Save Draft
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';

export default {
    name: 'JournalRegister',
    data() {
        return {
            loading: true,
            saving: false,
            submitting: false,
            error: null,
            journal: null,
            user: null,
            hasDraft: false,
            applicationId: null,
            declarations: {
                confidentiality: false,
                coi: false
            },
            form: {
                requested_role: 'member',
                academic_type: 'researcher',
                highest_degree: '',
                academic_position: '',
                institution_type: '',
                institution_id: null,
                institution: '',
                department: '',
                country: '',
                biography: '',
                research_interests: '',
                institutional_email: '',
                orcid: '',
                sinta_id: '',
                scopus_author_id: '',
                google_scholar_url: '',
                recruitment_source: ''
            }
        }
    },
    async mounted() {
        const authStore = useAuthStore();
        this.user = authStore.userData;

        try {
            // 1. Resolve Journal
            const journalSlug = this.$route.params.slug;
            const journalRes = await axios.get(`/api/journals/${journalSlug}`);
            this.journal = journalRes.data.data.journal;

            // 2. Fetch Academic Profile (Pre-fill)
            try {
                const profileRes = await axios.get('/api/profile/academic');
                if (profileRes.data && profileRes.data.data) {
                    const profile = profileRes.data.data;
                    Object.keys(this.form).forEach(key => {
                        if (profile[key] !== undefined && profile[key] !== null && key !== 'requested_role' && key !== 'recruitment_source') {
                            this.form[key] = profile[key];
                        }
                    });
                }
            } catch (err) {
                // Profile might not exist yet, that's fine.
            }

            // 3. Fetch existing application draft if any
            try {
                const appRes = await axios.get(`/api/journals/${this.journal.slug}/membership-application`);
                if (appRes.data && appRes.data.data && appRes.data.data.application) {
                    const app = appRes.data.data.application;
                    
                    if (app.status === 'submitted' || app.status === 'approved' || app.status === 'rejected') {
                        // Already beyond draft, redirect to status
                        this.$router.push({ name: 'journal_membership_status', params: { slug: this.journal.slug } });
                        return;
                    }

                    this.hasDraft = true;
                    this.applicationId = app.id;
                    this.form.requested_role = app.requested_role;
                    this.form.recruitment_source = app.recruitment_source || '';
                    if (app.declarations) {
                        this.declarations = app.declarations;
                    }
                }
            } catch (err) {
                // Application not found, normal registration flow.
            }

        } catch (error) {
            this.error = 'Failed to load registration data. Please verify the journal exists.';
            console.error(error);
        } finally {
            this.loading = false;
        }
    },
    methods: {
        getPayload() {
            const payload = { ...this.form };
            if (payload.requested_role === 'reviewer') {
                payload.declarations = this.declarations;
            } else {
                payload.recruitment_source = null;
                payload.declarations = null;
            }
            return payload;
        },
        async saveDraft() {
            this.saving = true;
            this.error = null;
            try {
                const payload = this.getPayload();

                if (this.hasDraft) {
                    await axios.patch(`/api/journals/${this.journal.slug}/membership-application`, payload);
                    alert('Draft saved successfully!');
                } else {
                    await axios.post(`/api/journals/${this.journal.slug}/membership-application`, payload);
                    this.hasDraft = true;
                    alert('Draft created successfully! You can now submit it for review.');
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to save draft.';
                console.error(error);
            } finally {
                this.saving = false;
            }
        },
        async submitApplication() {
            if (!confirm('Are you sure you want to submit this application for review?')) {
                return;
            }

            this.submitting = true;
            this.error = null;

            try {
                // Save draft one last time before submitting
                await axios.patch(`/api/journals/${this.journal.slug}/membership-application`, this.getPayload());

                // Submit
                await axios.post(`/api/journals/${this.journal.slug}/membership-application/submit`);
                
                this.$router.push({ name: 'journal_membership_status', params: { slug: this.journal.slug } });
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to submit application.';
                console.error(error);
            } finally {
                this.submitting = false;
            }
        }
    }
}
</script>
