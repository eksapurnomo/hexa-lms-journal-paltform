import { defineStore } from "pinia";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        authToken: null,
        userData: null,
        dashboardContext: {
            submission: { has_submissions: false, count: 0 },
            memberships: [],
            managed_journals: [],
        }
    }),
    actions: {
        setAuthData(token, data) {
            this.authToken = token;
            this.userData = data;
        },

        clearAuthData() {
            this.authToken = null;
            this.userData = null;
            this.dashboardContext = {
                submission: { has_submissions: false, count: 0 },
                memberships: [],
                managed_journals: [],
            };
            localStorage.removeItem("quiz");
            localStorage.removeItem("exam");
        },

        async fetchDashboardContext() {
            if (!this.authToken) return;
            try {
                const response = await axios.get('/user/dashboard-context', {
                    headers: {
                        Accept: 'application/json',
                        Authorization: 'Bearer ' + this.authToken,
                    }
                });
                if (response.data && response.data.data) {
                    this.dashboardContext = response.data.data;
                }
            } catch (error) {
                console.error("Failed to fetch dashboard context", error);
            }
        }
    },
    persist: true,
});
