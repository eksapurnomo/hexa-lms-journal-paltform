<template>
    <header class="navbar navbar-expand-xl"
        :class="[{ 'bg-light-primary': $route.path === '/' }, { 'scrolled fixed-top': isScrolled }]">
        <section class="container">
            <router-link to="/" class="navbar-brand">
                <img :src="masterStore?.masterData?.logo" width="150px" height="50px" class="object-fit-contain"
                    alt="LMS-logo" />
            </router-link>
            <div class="d-flex align-items-center gap-2">
                <div v-if="authStore.authToken" class="dropdown d-lg-none">
                    <a href="#" class="text-decoration-none text-dark" data-bs-toggle="dropdown" aria-expanded="false">
                        <img :src="authStore.userData.profile_picture"
                            class="auth-profile-pic rounded-circle object-fit-cover" alt="Menu" height="45px"
                            width="45px" />
                    </a>
                    <ul class="dropdown-menu position-absolute top-100 start-50 mt-5 translate-middle">
                        <li>
                            <router-link to="/dashboard" class="dropdown-item">
                                {{ $t('Dashboard') }}
                            </router-link>
                        </li>
                        <li>
                            <router-link to="/author/submissions" class="dropdown-item">
                                {{ $t('My Submissions') }}
                            </router-link>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" @click="logout()">
                                {{ $t('Log Out') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation" @click="toggleMenu = !toggleMenu">
                    <img v-if="!toggleMenu" src="/public/assets/website/icon/menubar.svg" alt="menubar" />
                    <img v-else src="/public/assets/website/icon/close.svg" alt="menubar" />
                </button>
            </div>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <div class="category-dropdown dropdown pt-3 pt-lg-0">
                    <button class="btn category-dropdown-btn mx-3 px-3" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <img :src="'/assets/images/website/category.svg'" class="me-2" />
                        {{ $t('Category') }}
                        <i class="ri ri-arrow-down-s-line ms-4"></i>
                    </button>
                    <ul class="category-menu dropdown-menu border-0 theme-shadow p-3">
                        <li v-for="category in categories" :key="category.id" class="category-menu-item border-bottom">
                            <router-link :to="'/courses?category_id=' + category.id" class="dropdown-item" href="#">
                                <div class="d-flex align-items-center">
                                    <img :src="category.image" class="me-3" height="40px" width="40px" />
                                    <div>
                                        <strong class="d-block">
                                            {{ category.title }}
                                        </strong>
                                        <small>
                                            {{ category.course_count }}
                                            {{ $t('Courses') }}
                                        </small>
                                    </div>
                                </div>
                            </router-link>
                        </li>
                    </ul>
                </div>
                <ul class="navbar-nav ms-md-2 me-auto my-3 my-md-0 mb-lg-0">
                    <li class="nav-item">
                        <router-link to="/" :class="['nav-link m-0 pb-1 pt-0', $route.path === '/' ? 'active' : '',]">
                            {{ $t('Home') }}
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/courses"
                            :class="['nav-link m-0 pb-1 pt-0', $route.path === '/courses' ? 'active' : '',]">
                            {{ $t('Courses') }}
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/about-us"
                            :class="['nav-link m-0 pb-1 pt-0', $route.path === '/about-us' ? 'active' : '',]">
                            {{ $t('About Us') }}
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/contact-us"
                            :class="['nav-link m-0 pb-1 pt-0', $route.path === '/contact-us' ? 'active' : '',]">
                            {{ $t('Contact Us') }}
                        </router-link>
                    </li>
                </ul>
                <div class="language dropdown border-1 border-secondary rounded me-3 p-2">
                    <a href="#" class="text-decoration-none fs-6 text-dark" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <img :src="'/assets/images/menu/lang.svg'" />
                        {{
                            {
                                en: $t('English'),
                                bn: $t('Bangla'),
                                fr: $t('French')
                            }[localeStore.language] || localeStore.language
                        }}
                        <i class="ri ri-arrow-down-s-line ms-2"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-center">
                        <li v-for="lang in masterStore.masterData?.languages" :key="lang">
                            <span class="dropdown-item" @click="modifyLang(lang.name)">
                                {{
                                    {
                                        en: $t('English'),
                                        bn: $t('Bangla'),
                                        fr: $t('French')
                                    }[lang.name] || lang.name
                                }}
                            </span>
                        </li>
                    </ul>
                </div>
                <div v-if="authStore.authToken"
                    class="auth-profile dropdown me-3 my-0 my-lg-2 d-flex justify-content-center align-items-center">

                    <a href="#" class="text-decoration-none text-dark d-flex align-items-center"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="d-none d-md-flex flex-column me-2 text-end">
                            <strong class="title">{{ authStore.userData.name }}</strong>
                            <small class="subtitle">{{ authStore.userData.email }}</small>
                        </span>
                        <img :src="authStore.userData.profile_picture"
                            class="auth-profile-pic rounded-circle object-fit-cover" alt="Menu" height="45px"
                            width="45px" />
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <router-link to="/dashboard" class="dropdown-item">
                                {{ $t('Dashboard') }}
                            </router-link>
                        </li>
                        <li>
                            <router-link to="/author/submissions" class="dropdown-item">
                                {{ $t('My Submissions') }}
                            </router-link>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" @click="logout()">
                                {{ $t('Log Out') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div v-else class="mx-3">
                    <router-link to="/login" class="btn btn-primary rounded-pill w-100 w-md-0 login-btn">
                        {{ $t('Login') }}
                    </router-link>
                </div>
            </div>
        </section>
    </header>
</template>

<style scoped>
.auth-profile {
    .title {
        font-family: 'Poppins', sans-serif;
        font-size: 0.8rem;
        font-weight: 600;
        color: #9e4aed;
        margin: 0;
    }

    .subtitle {
        font-family: 'Poppins', sans-serif;
        font-size: 0.7rem;
        font-weight: 400;
        color: #3a3838;
        margin: 0;
    }
}

.auth-profile-pic {
    width: 48px;
    height: 48px;
    border: 2px solid #78b3ce;
}

.collapsing {
    transition: all ease-in-out 1s;
}

.scrolled {
    background-color: #ffffff;
}

.scrolled .category-dropdown .category-dropdown-btn {
    border: 1px solid #ddd2e9;
    background-color: transparent !important;
}

.navbar {
    /* transition: all ease-in-out .5s; */

    .nav-item {
        padding-left: 1rem;
        padding-right: 1rem;

        .nav-link {
            padding-left: 0;
            padding-right: 0;
            padding-bottom: 4px;

            &:hover {
                color: #9e4aed;
            }
        }

        .nav-link.active {
            color: #9e4aed;
            font-weight: bold;
            border-bottom: 2px solid #9e4aed;
        }
    }

    .category-dropdown {
        .category-dropdown-btn {
            border: 1px solid #ddd2e9;
            background-color: #f3e9fe;
        }

        .category-menu {
            height: 500px;
            overflow-y: auto;

            .category-menu-item {
                .dropdown-item {
                    border: 1px solid #fff;
                    border-left: 3px solid #fff;
                    padding: 6px 50px 6px 10px;
                    margin-top: 6px;
                    margin-bottom: 6px;
                    border-radius: 0.7rem;
                    transition: all 0.2s ease-in-out;

                    &:hover {
                        border-color: #9e4aed;
                        background-color: #f3e9fe;
                    }
                }
            }
        }
    }
}

.navbar .nav-item .nav-link {
    font-size: 1rem;
}

@media (max-width: 1600px) {
    .navbar .nav-item .nav-link {
        font-size: 0.875rem;
    }

    .navbar .login-btn {
        padding: 0.5rem 0.875rem;
    }

    .navbar .category-dropdown .category-dropdown-btn {
        padding: 0.5rem 0.875rem;
    }
}

@media (max-width: 1400px) {
    .navbar .nav-item .nav-link {
        font-size: 0.75rem;
    }

    .navbar .login-btn {
        padding: 0.5rem 0.75rem;
        margin: 0.5rem 0.75rem;
    }

    .navbar .category-dropdown .category-dropdown-btn {
        padding: 0.5rem 0.5rem;
        margin: 0.5rem 0.5rem;
    }
}
</style>

<script setup>
import { useAuthStore } from "@/stores/auth";
import { useMasterStore } from "@/stores/master";
import { useLocaleStore } from "../stores/locale";
import Swal from "sweetalert2";
import { ref, onMounted, onUnmounted, watch } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import localization from "../localization";
import { useI18n } from "vue-i18n";

const router = useRouter();

let toggleMenu = ref(false);

const authStore = useAuthStore();
const masterStore = useMasterStore();
const localeStore = useLocaleStore();
const isLoggedIn = ref(false);
const isScrolled = ref(false);
let currentLanguage = ref('en');
const { t } = useI18n();

const handleScroll = () => {
    isScrolled.value = window.scrollY > 0;
};

let categories = ref([]);

const fetchCategories = async () => {
    try {
        const response = await axios.get(`/categories`, {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            params: {
                items_per_page: 20,
                page_number: 1,
            },
        });
        categories.value = response.data.data.categories;
    } catch (error) {
        console.error("Error fetching categories:", error);
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    fetchCategories();
    if (authStore.authToken) {
        isLoggedIn.value = true;
    }

    // Fetch master data
    if (!masterStore.data) {
        axios
            .get(`/master`, {
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
            })
            .then((response) => {
                masterStore.setMasterData(response.data.data.master);

                if (localeStore.defaultLanguage !== masterStore.masterData.default_language) {
                    localeStore.setLang(masterStore.masterData.default_language);
                    localeStore.setDefaultLang(masterStore.masterData.default_language);
                    location.reload();
                }
            })
            .catch((error) => {
                console.error("Error fetching data:", error);
            });
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

watch(() => localeStore.language, (oldValue, newValue) => {
    if (oldValue !== newValue) {
        modifyLang(localeStore.language);
    }
});

function logout() {
    Swal.fire({
        title: t("Are you sure?"),
        text: t("Do you want to log out?"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: t("Yes, log out!"),
        cancelButtonText: t("No"),
    }).then((result) => {
        if (result.isConfirmed) {
            authStore.clearAuthData();

            Swal.fire({
                title: t("Logged Out!"),
                text: t("Log out successful."),
                showConfirmButton: false,
                icon: "success",
                timer: 1500,
            });
        }
        router.push("/");
    });
}



const modifyLang = (lang) => {
    localeStore.setLang(lang);
    const language = masterStore.masterData.languages.find(lang => lang.name === localeStore.language);
    if (language) {
        localeStore.language = language.name;
    }
    localization.fetchLocalizationData();
};

</script>
