import { defineStore } from "pinia";

export const useExamStore = defineStore("exam", {
    state: () => ({
        token: "",
        currentQuestion: [],
        examSession: {},
        isExamSubmitted: false,
        currentTimer: 0,
        answers: [],
        timerInterval: null,
        score: 0,
        perQeustionTime: 0,
        errorMessage: "",
        sessionClosed: false,
        isResetTimer: false,
    }),
    getters: {
        formattedTime(state) {
            const minutes = Math.floor(state.currentTimer / 60);
            const seconds = state.currentTimer % 60;
            return `${minutes}:${seconds < 10 ? "0" : ""}${seconds}`;
        },
    },
    actions: {
        // Decrement the timer
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

        // Stop the timer when needed
        stopTimer() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
        },

        // Reset the timer
        resetTimer() {
            this.isResetTimer = false;
            this.stopTimer();
            this.currentTimer = this.perQeustionTime;
        },

        async fetchQuestions(examId, token) {
            this.token = token;
            this.sessionClosed = false;
            try {
                const response = await axios.get(`/exam/start/${examId}`, {
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        Authorization: `Bearer ${token}`,
                    },
                });
                this.currentQuestion = response.data.data.questions;
                this.examSession = response.data.data.examSession;
                this.perQeustionTime = this.examSession.exam_duration;

                if (this.isResetTimer) {
                    this.resetTimer();
                }
            } catch (error) {
                this.errorMessage = error.response.data.message;
                this.sessionClosed = true;
            }
            this.startTimer();
        },

        async submitQuiz() {
            this.sessionClosed = false;
            try {
                const response = await axios.post(
                    `/exam/submit/${this.examSession.id}`,
                    {
                        answers: this.answers,
                    },
                    {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: `Bearer ${this.token}`,
                        },
                    }
                );
                this.score = response.data.data.obtained_mark;
                this.isExamSubmitted = true;
                this.stopTimer();
                this.answers = [];
                this.currentQuestion = 0;
                this.currentTimer = 0;
            } catch (error) {
                this.errorMessage = error.response.data.message;
                this.sessionClosed = true;
            }
        },
    },
    persist: true,
});
