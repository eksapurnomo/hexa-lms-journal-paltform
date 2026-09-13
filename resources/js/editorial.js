import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './editorial/App.vue';
import SubmissionList from './editorial/pages/SubmissionList.vue';
import SubmissionDetail from './editorial/pages/SubmissionDetail.vue';

// Initialize Axios with default configuration (from bootstrap.js if needed, or explicitly here)
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.baseURL = '/api';

const routes = [
    {
        path: '/admin/editorial',
        name: 'editorial.list',
        component: SubmissionList
    },
    {
        path: '/admin/editorial/submissions/:id',
        name: 'editorial.detail',
        component: SubmissionDetail,
        props: true
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

const app = createApp(App);
app.use(router);
app.mount('#editorial-app');
