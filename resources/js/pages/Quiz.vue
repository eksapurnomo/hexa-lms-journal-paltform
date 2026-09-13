<template>
    <div v-if="errorMessage" class="d-flex align-items-center justify-content-center"
        style="height: calc(100vh - 170px)">
        <div>
            <h3 class="text-danger">{{ errorMessage }}</h3>
            <a href="/dashboard" class="btn btn-primary mt-4 w-100">
                {{ $t('Move to Dashboard') }} <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    <div v-else class="quiz-container shadow my-5">
        <h1 class="mb-4">{{ $t('Start Your Quiz') }}</h1>
        <div v-if="!quizStore.isQuizSubmitted" class="question-section">
            <div class="d-flex justify-content-between align-items-center">
                <p class="timer">{{ $t('Time Remaining') }} : <span class="fs-6 fw-bold text-primary">{{ quizStore.formattedTime
                        }}</span></p>
                <div>
                    <p class="timer text-end fw-bold">{{ $t('Total Question') }} : {{ quizStore.currentQuestion }}/{{
                        quizStore.totalQuestion }}</p>
                </div>
            </div>
            <h2>{{ $t('Question') }} {{ quizStore.currentQuestion }}</h2>
            <div>
                <p class="questionText"><i class="bi bi-chevron-double-right"></i> {{
                    quizStore.quizData?.question_text }}
                </p>
                <div class="options">
                    <label v-for="(option, index) in quizStore.quizData?.options" :key="index" class="option">
                        <input v-if="quizStore.quizData?.question_type === 'multiple_choice'" :value="option?.text"
                            v-model="selectedChoices" type="checkbox" :name="'question_' + index" />
                        <input v-else="quizStore.quizData?.question_type === 'single_choice'" :value="option?.text"
                            v-model="selectedChoices" type="checkbox" :name="'question_' + index" />
                        <input v-else type="radio" :value="option?.text" v-model="selectedChoices"
                            :name="'question_' + index" />
                        {{ option?.text }}
                    </label>
                </div>
            </div>

            <div class="navigation d-flex justify-content-between align-items-center mt-5">
                <button class="btn btn-danger btn-sm px-5 py-2" @click="skipQuestion">
                    {{ $t('Skip') }}
                    <FontAwesomeIcon :icon="faForward" />
                </button>
                <button class="btn btn-primary btn-sm px-5 py-2" @click="nextQuestion">
                    {{ $t('Next') }} <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>

        <div v-else class="result-section">
            <h2>{{ $t('Congretulations') }}, {{ $t('Quiz Completed') }}!</h2>
            <div class="container">
                <div class="row">
                    <div class="col-12 col-md-4 offset-md-4 mt-5">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title">{{ $t('Scoreboard') }}</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text fw-bold">
                                    {{ $t('You scored') }}: <strong class="fs-5">{{ quizStore.score }}</strong>
                                </p>

                            </div>
                        </div>
                    </div>
                </div>
                <a href="/dashboard" class="btn btn-primary mt-4 w-100">
                    {{ $t('Move to Dashboard') }} <i class="bi bi-arrow-right"></i>
                </a>
            </div>

        </div>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useQuizStore } from '../stores/quiz';
import { useRoute, useRouter } from 'vue-router';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faForward } from '@fortawesome/free-solid-svg-icons';
import Swal from 'sweetalert2';
import axios from 'axios';

const authStore = useAuthStore();
const quizStore = useQuizStore();
const route = useRoute();
const router = useRouter();

const errorMessage = ref('');
let selectedChoices = ref([]);
const isSkipped = ref(false);
const isSkipComplete = ref(false);

const updateAnswer = () => {

    const questionId = quizStore.quizData.id;
    const questionType = quizStore.quizData.question_type;

    const answerData = {
        question_id: questionId,
        ...(questionType === 'multiple_choice'
            ? { choices: isSkipped.value ? [] : selectedChoices.value }
            : { choice: isSkipped.value ? null : selectedChoices.value }
        ),
        skip: isSkipped.value, // Explicitly include skip flag
    };

    quizStore.answers = answerData;
    // Reset selected choices
    selectedChoices.value = questionType === 'multiple_choice' ? [] : [null];
};


watch(quizStore, () => {
    if (quizStore.currentTimer == 0 && !isSkipComplete.value) {
        skipQuestion();
        isSkipComplete.value = true;
    }

}, { deep: true });

onMounted(async () => {
    const beforeRouteLeave = router.beforeEach((to, from, next) => {
        if (route.name === from.name) {
            handleBeforeRouteLeave(to, from, next);
        } else {
            next();
        }
    });

    router.afterEach(() => {
        beforeRouteLeave();
    });

    try {

        const response = await axios.get(`/quiz/start/${route.params.id}`, {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: `Bearer ${authStore.authToken}`,
            },
        });

        const newQuizSession = response.data.data?.quiz_session;

        quizStore.totalQuestion = response.data.data?.question_count;
        quizStore.authToken = authStore.authToken;
        quizStore.quizData = response.data.data?.question;
        quizStore.quizSession = newQuizSession;
        quizStore.timerManager = newQuizSession?.quiz_duration || 0;

        if (quizStore.isResetTimer) {
            quizStore.isQuizSubmitted = false;
            quizStore.resetTimer();
        }

        quizStore.timerManager = newQuizSession?.quiz_duration || 0;

    } catch (error) {
        errorMessage.value = error.response.data.message;
    }
    quizStore.startTimer();
});


const skipQuestion = () => {
    isSkipped.value = true;
    updateAnswer();
    nextQuestion();
}

const nextQuestion = () => {
    updateAnswer();
    quizStore.resetTimer();
    quizStore.nextQuestion();
}



onBeforeUnmount(() => {
    isSkipComplete.value = false;
    quizStore.currentTimer = 0;
    quizStore.timerManager = 0;
    quizStore.currentQuestion = 1;
    quizStore.quizData = {},
        quizStore.isQuizSubmitted = false
    quizStore.stopTimer();
    quizStore.score = 0;
    selectedChoices.value = [];
})

const handleBeforeRouteLeave = (to, from, next) => {
    Swal.fire({
        title: "Are you sure?",
        text: "Your progress may be lost if you leave the exam.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Leave Quiz",
        cancelButtonText: "Stay on Page",
    }).then((result) => {
        if (result.isConfirmed) {
            next();
        } else {
            next(false); // Cancel navigation
        }
    });
};


</script>

<style scoped>
.quiz-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 100px;
    font-family: Arial, sans-serif;
}

h1 {
    text-align: center;
    font-size: 1.5rem;
    font-weight: 600;
}

h2 {
    margin-top: 3rem;
    font-size: 1.4rem;
    font-weight: 600;
    color: #007bff;
}

.timer {
    text-align: center;
    font-size: 0.8rem;
    margin: 10px 0;
}

.questionText {
    font-size: 1rem;
    font-weight: 600;
    margin: 10px 0;
}

.options {
    margin: 20px 0;
}

.option {
    display: block;
    margin: 10px 0;
    font-size: 0.9rem;
    font-weight: 400;
}


.navigation {
    display: flex;
    justify-content: space-between;
}

button {
    padding: 10px 20px;
    border: none;
    color: white;
    cursor: pointer;
    border-radius: 5px;
}

button:disabled {
    background-color: #ccc;
}

.result-section {
    text-align: center;
}
</style>
