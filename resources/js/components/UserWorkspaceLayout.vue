<template>
    <main class="h-100 d-flex flex-column flex-grow-1 bg-light user-workspace-layout">
        <section class="d-flex flex-grow-1 position-relative overflow-hidden">
            <!-- Sidebar Navigation (Desktop) -->
            <aside class="workspace-sidebar bg-white border-end d-none d-xl-flex flex-column" style="width: 260px; min-width: 260px; overflow-y: auto;">
                <slot name="sidebar"></slot>
            </aside>
            
            <!-- Main Content Area -->
            <section class="workspace-content bg-light flex-grow-1 d-flex flex-column" style="min-width: 0; overflow-y: auto;">
                <div class="px-3 px-md-4 py-4 w-100 h-100">
                    
                    <!-- Mobile Menu Toggle -->
                    <button class="d-xl-none btn theme-shadow btn-outline-primary px-4 py-2 rounded-1 mb-4" type="button"
                        data-bs-toggle="offcanvas" data-bs-target="#workspaceMobileMenu" aria-controls="workspaceMobileMenu">
                        {{$t('Menu')}} <i class="bi bi-arrow-right-circle"></i>
                    </button>

                    <!-- Mobile Menu Offcanvas -->
                    <div class="offcanvas offcanvas-start" tabindex="-1" id="workspaceMobileMenu">
                        <div class="offcanvas-header border-bottom">
                            <h5 class="offcanvas-title fw-bold">Menu</h5>
                            <button type="button" class="theme-shadow btn-outline-primary px-3 py-2 rounded-1 btn m-0"
                                data-bs-dismiss="offcanvas" aria-label="Close">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                        <div class="offcanvas-body">
                            <slot name="mobile-nav"></slot>
                        </div>
                    </div>

                    <!-- Slot for the actual page content -->
                    <slot name="content"></slot>

                </div>
            </section>
        </section>
    </main>
</template>

<script setup>
// Shared Layout wrapper for User Dashboard and Workspaces
</script>

<style scoped>
.user-workspace-layout {
    height: 100vh;
}
.workspace-sidebar::-webkit-scrollbar,
.workspace-content::-webkit-scrollbar {
    width: 6px;
}
.workspace-sidebar::-webkit-scrollbar-thumb,
.workspace-content::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 10px;
}
</style>
