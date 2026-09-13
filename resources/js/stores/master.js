import { defineStore } from "pinia";

export const useMasterStore = defineStore("master", {
    state: () => ({
        masterData: null,
    }),
    actions: {
        setMasterData(data) {
            this.masterData = data;
        },

        clearMasterData() {
            this.masterData = null;
        },
    },
    persist: true,
});
