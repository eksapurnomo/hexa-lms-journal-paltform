import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './reviewer/App.vue';
import AssignmentList from './reviewer/pages/AssignmentList.vue';
import AssignmentDetail from './reviewer/pages/AssignmentDetail.vue';

import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.baseURL = '/api';

const routes = [
    {
        path: '/reviewer/assignments',
        name: 'reviewer.list',
        component: AssignmentList,
    },
    {
        path: '/reviewer/assignments/:id',
        name: 'reviewer.detail',
        component: AssignmentDetail,
        props: true,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

const app = createApp(App);
app.use(router);
app.mount('#reviewer-app');
