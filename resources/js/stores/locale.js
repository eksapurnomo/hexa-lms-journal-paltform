import { defineStore } from "pinia";

export const useLocaleStore = defineStore("locale", {
    state: () => ({
        language: localStorage.getItem("lang") || "en",
        defaultLanguage: localStorage.getItem("default_lang") || "en",
    }),
    actions: {
        setLang(lang) {
            this.language = lang;
            localStorage.setItem("lang", lang);
        },
        setDefaultLang(lang) {
            this.defaultLanguage = lang;
            localStorage.setItem("default_lang", lang);
        },
    },
    persist: {
        enabled: true,
        strategies: [
            {
                key: "locale",
                storage: localStorage,
            },
        ],
    },
});
