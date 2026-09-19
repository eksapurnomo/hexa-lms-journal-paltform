<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">{{ management.journal.title }}</h4>
            <span class="badge bg-primary text-capitalize">{{ management.role }}</span>
        </div>
        
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="bi bi-tools fs-1 text-muted mb-3"></i>
                    <h5>Journal Management Workspace</h5>
                    <p class="text-muted">Management capabilities for this journal are active.</p>
                    
                    <div class="mt-4">
                        <button class="btn btn-outline-primary mx-2" disabled>
                            <i class="bi bi-file-earmark-text me-2"></i> Submissions
                        </button>
                        <button class="btn btn-outline-primary mx-2" disabled>
                            <i class="bi bi-people me-2"></i> Members
                        </button>
                        <button class="btn btn-outline-primary mx-2" disabled>
                            <i class="bi bi-gear me-2"></i> Settings
                        </button>
                    </div>
                    <p class="text-muted small mt-4">
                        <i class="bi bi-info-circle me-1"></i> These features are being integrated into the new Unified Dashboard in a subsequent phase. 
                        Please use the existing Admin Editorial Desk for current management tasks.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    management: {
        type: Object,
        required: true
    }
});
</script>
