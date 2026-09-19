<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">{{ membership.journal.title }}</h4>
            <router-link :to="{ name: 'journal_membership_status', params: { slug: membership.journal.slug } }" class="btn btn-sm btn-outline-primary">
                View Membership Status <i class="bi bi-arrow-right"></i>
            </router-link>
        </div>
        
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small text-uppercase">Role</label>
                        <h5 class="text-capitalize text-primary">{{ membership.role }}</h5>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small text-uppercase">Status</label>
                        <div>
                            <span class="badge bg-success text-capitalize">{{ membership.status }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    membership: {
        type: Object,
        required: true
    }
});
</script>
