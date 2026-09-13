import { defineStore } from "pinia";

export const useQuizStore = defineStore("quiz", {
    state: () => ({
        currentQuestion: 1,
        totalQuestion: 0,
        isResetTimer: false,
        authToken: "",
        quizData: {},
        quizSession: {},
        answers: {},
        currentTimer: 0,
        timerManager: 0,
        isQuizSubmitted: false,
        score: 0,
        timerInterval: null,
    }),
    getters: {
        formattedTime(state) {
            const minutes = Math.floor(state.currentTimer / 60);
            const seconds = state.currentTimer % 60;
            return minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;
        },
    },
    actions: {
        startTimer() {
            this.stopTimer();
            this.timerInterval = setInterval(() => {
                if (this.currentTimer > 0) {
                    this.currentTimer--;
                } else {
                    this.stopTimer();
                }
            }, 1000);
        },

        stopTimer() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
        },

        resetTimer() {
            this.isResetTimer = false;
            this.stopTimer();
            this.currentTimer = this.timerManager;
        },

        resetQuizState() {
            this.currentQuestion = 1;
            this.currentTimer = 0;
            this.timerManager = 0;
            this.timerInterval = null;
            this.answers = {};
        },

        async nextQuestion() {
            await axios.post(`/quiz/submit/${this.quizSession.id}`,
                    {
                        answer: this.answers,
                    },
                    {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: `Bearer ${this.authToken}`,
                        },
                    }
                )
                .then((res) => {
                    if (res.data.data.question) {
                        this.quizData = res.data.data.question;
                        this.quizData.quizSession = res.data.data.quiz_session;
                        this.quizData.timerManager = res.data.data.quiz_session.quiz_duration;
                        this.currentQuestion++;
                        this.resetTimer();
                        this.startTimer();
                    } else {
                        this.quizData.quizSession = res.data.data.quiz_session;
                        this.currentQuestion = 1;
                        this.isQuizSubmitted = true;
                        this.score = this.quizData.quizSession.obtained_mark;
                        this.currentTimer = 0;
                        this.timerManager = 0;
                        this.timerInterval = null;
                        this.stopTimer();
                        this.resetQuizState();
                    }
                    this.answers = {};
                })
                .catch((error) => {
                    console.log(error);
                });
        },
    },
    persist: true,
});
