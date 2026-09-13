<template>
    <div v-if="examStore.sessionClosed" class="d-flex align-items-center justify-content-center"
        style="height: calc(100vh - 170px)">
        <div>
            <h3 class="text-danger">{{ examStore.errorMessage }}</h3>
            <a href="/dashboard" class="btn btn-primary mt-4 w-100">
                {{ $t('Move to Dashboard') }} <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    <div v-else class="quiz-container shadow my-5" :class="!examStore.sessionClosed ? 'd-block' : 'd-none'">
        <h1>{{ $t('Test Your Limits: How Much Do You Really Know') }}?</h1>

        <div v-if="!examStore.isExamSubmitted" class="question-section">
            <div class="d-flex justify-content-between align-items-start">
                <p class="timer">{{ $t('Time Remaining') }} : <span class="fs-6 fw-bold text-primary">{{
                    examStore.formattedTime
                        }}</span></p>
                <div>
                    <p class="timer text-end">{{ $t('Total Question') }} : {{ examStore.currentQuestion.length }}</p>
                    <p class="timer text-end">{{ $t('Total Time ') }} : {{ examStore.examSession.exam_duration / 60 }}{{
                        $t('min') }}</p>
                    <p class="timer text-end">{{ $t('Total Mark') }} : {{ examStore.examSession.total_mark }}</p>
                </div>
            </div>
            <h2>{{ $t('Question') }}</h2>
            <div v-for="(questions, questionIndex) in examStore.currentQuestion" :key="questionIndex">
                <p class="questionText"><i class="bi bi-chevron-double-right"></i> {{ questionIndex + 1 }}. {{
                    questions?.question_text }}
                </p>

                <div class="options">
                    <label v-for="(option, index) in questions?.options" :key="index" class="option">
                        <input v-if="questions?.question_type === 'multiple_choice'" type="checkbox"
                            :value="option?.text"
                            @change="handleCheckboxChange(questionIndex, questions?.id, option?.text, $event)" />
                        <input v-else type="radio" :value="option?.text"
                            @change="handleCheckboxChange(questionIndex, questions?.id, option?.text, $event, type = 'radio')"
                            :name="'question_' + questionIndex" />
                        {{ option?.text }}
                    </label>
                </div>
            </div>

            <div class="navigation d-flex justify-content-end mt-5">
                <button class="btn btn-primary btn-lg px-5 py-2" @click="submitExam">
                    {{ $t('Submit') }}
                </button>
            </div>
        </div>

        <div v-else class="result-section">
            <h2 class="text-center mb-4 text-primary fs-3">{{ $t('Congratulations') }}, {{ $t('Exam Completed') }}</h2>
            <div class="container">
                <div class="row d-flex justify-content-center">
                    <div class="col-12 col-md-6 col-lg-4" style="width: 70%;">
                        <div class="card border-primary text-center">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title">{{ $t('Scoreboard') }}</h5>
                            </div>
                            <div class="card-body">
                                <!-- SVG Image for Pass/Fail -->
                                <div class="svg-container mb-4">
                                    <svg v-if="examStore.score >= examStore.examSession?.pass_mark"
                                        xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="green"
                                        class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                                        <path
                                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="red"
                                        class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                                        <path
                                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z" />
                                    </svg>
                                </div>

                                <!-- Pass/Fail Message -->
                                <h4 v-if="examStore.score >= examStore.examSession?.pass_mark"
                                    class="text-success m-0 fs-3 fw-bold">
                                    {{ $t('You Passed') }}!</h4>
                                <h4 v-else class="text-danger fs-3 fw-bold m-0">{{ $t('You Failed') }}!</h4>

                                <!-- Score Display -->
                                <p class="card-text fw-bold mt-2">
                                    {{ $t('You scored') }}: <strong class="fs-5">{{ examStore.score }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Button to Dashboard -->
                <div class="row justify-content-center mt-4">
                    <div class="col-12 col-md-6 col-lg-4" style="width:100%;">
                        <a href="/dashboard" class="btn btn-primary w-100">
                            {{ $t('Move to Dashboard') }} <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, onUnmounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useExamStore } from '../stores/exam';
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';


const authStore = useAuthStore();
const examStore = useExamStore();
const route = useRoute();
const router = useRouter();

// Reactive State
const selectedChoices = ref([]);
let examId = route.params.id;

const handleCheckboxChange = (index, questionID, value, event, type = 'checkbox') => {
    if (!selectedChoices.value[index]) {
        selectedChoices.value[index] = {
            question_id: questionID,
            ...(type === 'radio' ? { choice: null } : { choices: [] })
        };
    }

    if (event.target.checked) {
        if (type === 'radio') {
            selectedChoices.value[index].choice = value;
        } else {
            // Avoid duplicate entries in choices
            if (!selectedChoices.value[index].choices.includes(value)) {
                selectedChoices.value[index].choices.push(value);
            }
        }
    } else {
        if (type === 'checkbox') {
            const valueIndex = selectedChoices.value[index].choices.indexOf(value);
            if (valueIndex > -1) {
                selectedChoices.value[index].choices.splice(valueIndex, 1);
            }
        } else {
            selectedChoices.value[index].choice = null; // Clear choice for radio
        }
    }

    // Update or add the selectedChoices into authStore.answers
    const existingIndex = examStore.answers.findIndex(
        (answer) => answer.question_id === questionID
    );

    if (existingIndex > -1) {
        // Update the existing answer
        examStore.answers[existingIndex] = selectedChoices.value[index];
    } else {
        // Add new answer
        examStore.answers.push(selectedChoices.value[index]);
    }
}


const submitExam = () => {
    examStore.submitQuiz();
}


onMounted(() => {
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

    examStore.fetchQuestions(examId, authStore.authToken);
    examStore.isExamSubmitted = false;

});


onBeforeUnmount(() => {
    examStore.isExamSubmitted = false;
    examStore.answers = [];
    selectedChoices.value = [];
})

const handleBeforeRouteLeave = (to, from, next) => {
    Swal.fire({
        title: $t("Are you sure?"),
        text: $t("Your progress may be lost if you leave the exam"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Leave Exam",
        cancelButtonText: "Stay on Page",
    }).then((result) => {
        if (result.isConfirmed) {
            next(); // Allow navigation
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
