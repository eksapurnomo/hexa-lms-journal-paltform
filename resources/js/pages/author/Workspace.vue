<template>
    <UserWorkspaceLayout>
        <template #sidebar>
            <AuthorSidebar />
        </template>
        
        <template #mobile-nav>
            <AuthorMenu />
        </template>
        
        <template #content>
            <WorkspaceContainer type="standard">

                <div class="author-workspace mt-4 mt-xl-0 bg-white p-4 shadow-sm rounded">
                    <router-view></router-view>
                </div>
            </WorkspaceContainer>
        </template>
    </UserWorkspaceLayout>
</template>

<script setup>
import UserWorkspaceLayout from "../../components/UserWorkspaceLayout.vue";
import WorkspaceContainer from "../../components/WorkspaceContainer.vue";
import AuthorSidebar from "../../components/AuthorSidebar.vue";
import AuthorMenu from "../../components/AuthorMenu.vue";
</script>
