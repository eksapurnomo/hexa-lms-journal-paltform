import { createI18n } from "vue-i18n";
import axios from "axios";

const i18n = createI18n({
    locale: localStorage.getItem("lang"),
    messages: {},
});
function fetchLocalizationData() {
    const lang = localStorage.getItem("lang") || "en";
    axios.get("/lang/" + lang)
        .then((response) => {
            i18n.global.setLocaleMessage(lang, response.data);
            i18n.global.locale = lang;
        })
        .catch((error) => {
            console.error("Failed to load language file", error);
        });
}

export default { i18n, fetchLocalizationData };
