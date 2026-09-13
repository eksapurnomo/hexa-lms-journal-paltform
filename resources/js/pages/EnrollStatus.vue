<template>
    <Header />
    <main class="content">
        <section class="enrollment-status d-flex justify-content-center align-items-center">
            <div class="container text-center">
                <transition name="fade">
                    <div v-if="status === 'success'" class="status-card success">
                        <i class="ri-checkbox-circle-fill icon text-success"></i>
                        <h3 class="fw-bold text-success">{{ $t('Congratulations') }} 🎉</h3>
                        <p class="message">
                            {{ $t('You have successfully enrolled in the course! Watch your course video lessons, complete assignments, and take quizzes to start your learning journey') }}
                        </p>
                        <router-link :to="'/play/' + courseId" class="btn btn-primary btn-lg">
                            {{ $t('Start learning') }}🚀
                        </router-link>
                    </div>
                </transition>

                <transition name="fade">
                    <div v-if="status === 'failed' || status === 'fail'" class="status-card failed">
                        <i class="ri-close-circle-fill icon text-danger"></i>
                        <h3 class="fw-bold text-danger">{{ $t('Sorry! Something went wrong') }} 😢</h3>
                        <p class="message">
                            {{ $t('Please try again. If the problem persists, contact the support team') }}
                        </p>
                        <router-link :to="'/checkout/' + courseId" class="btn btn-outline-danger">
                            {{ $t('Try again') }} 🔄
                        </router-link>
                    </div>
                </transition>

                <transition name="fade">
                    <div v-if="status === 'cancel'" class="status-card cancel">
                        <i class="ri-close-circle-fill icon text-warning"></i>
                        <h3 class="fw-bold text-warning">{{ $t('Your payment has been cancelled') }} ⚠️</h3>
                        <p class="message">
                            {{ $t('You have cancelled the Your payment. If this was a mistake, you can try again') }}
                        </p>
                        <router-link :to="'/checkout/' + courseId" class="btn btn-warning">
                            {{ $t('Try again') }} 🔄
                        </router-link>
                    </div>
                </transition>

                <transition name="fade">
                    <div v-if="status === 'timeout'" class="status-card cancel">
                        <i class="ri-close-circle-fill icon text-warning"></i>
                        <h3 class="fw-bold text-warning">{{ $t('Payment Request Timeout') }} ⚠️</h3>
                        <p class="message">
                            {{ $t('You have cancelled the registration. If this was a mistake, you can try again') }}
                        </p>
                        <router-link :to="'/checkout/' + courseId" class="btn btn-warning">
                            {{ $t('Re-register') }} 🔄
                        </router-link>
                    </div>
                </transition>
            </div>
        </section>
    </main>
    <Footer />
</template>

<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { onMounted } from 'vue';
import Header from '../components/Header.vue';
import Footer from "../components/DashboardFooter.vue";

const route = useRoute();
const router = useRouter();
const status = ref(route.query.status);
const courseId = ref(route.query.course_id);

onMounted(() => {
    if (!route.query.status) {
        router.push('/')
    }
});

</script>

<style scoped>
.content {
    height: calc(100vh - 170px);
}

.enrollment-status {
    min-height: 80vh;
    background: linear-gradient(135deg, #f9f9f9, #ffffff);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 0;
}

.container {
    max-width: 600px;
    padding: 40px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    animation: fadeIn 0.5s ease-in-out;
}

.status-card {
    text-align: center;
    padding: 20px;
}

.icon {
    font-size: 5rem;
    margin-bottom: 20px;
}

.message {
    font-size: 1.1rem;
    color: #555;
    margin-bottom: 20px;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}

.fade-enter,
.fade-leave-to {
    opacity: 0;
}
</style>
