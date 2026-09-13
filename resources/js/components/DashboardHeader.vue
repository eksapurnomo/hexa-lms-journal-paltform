<template>
    <header class="navbar navbar-expand-xl shadow-sm" :class="[{ 'bg-light-primary': $route.path === '/' }]">
        <section class="container">
            <router-link to="/" class="navbar-brand">
                <img :src="masterStore?.masterData?.logo" width="150px" height="50px" class="object-fit-contain"
                    alt="LMS" />
            </router-link>
            <div class="d-flex align-items-center gap-2">
                <div v-if="authStore.authToken" class="dropdown d-lg-none">
                    <a href="#" class="text-decoration-none text-dark" data-bs-toggle="dropdown" aria-expanded="false">
                        <img :src="authStore.userData.profile_picture"
                            class="auth-profile-pic rounded-circle object-fit-cover" alt="Menu" height="45px"
                            width="45px" />
                    </a>
                    <ul class="dropdown-menu position-absolute top-100 start-50 mt-5 translate-middle">
                        <router-link to="/" class="dropdown-item text-primary">
                            <i class="bi bi-house-door me-2"></i>{{ $t('Home') }}
                        </router-link>
                        <li>
                            <a class="dropdown-item" href="#" @click="logout()">{{ $t('Log Out') }}</a>
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
                <ul class="navbar-nav me-md-4 ms-auto my-3 my-md-0 mb-lg-0">
                    <li class="nav-item">
                        <router-link to="/" :class="[
                            'nav-link m-0 pb-1 pt-0',
                            $route.path === '/' ? 'active' : '',
                        ]">{{ $t('Home') }}</router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/courses" :class="[
                            'nav-link m-0 pb-1 pt-0',
                            $route.path === '/courses' ? 'active' : '',
                        ]">{{ $t('Courses') }}</router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/about-us" :class="[
                            'nav-link m-0 pb-1 pt-0',
                            $route.path === '/about-us' ? 'active' : '',
                        ]">{{ $t('About Us') }}</router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/contact-us" :class="[
                            'nav-link m-0 pb-1 pt-0',
                            $route.path === '/contact-us' ? 'active' : '',
                        ]">{{ $t('Contact Us') }}</router-link>
                    </li>
                </ul>
                <div v-if="authStore.authToken"
                    class="auth-profile dropdown me-3 my-0 my-lg-2 d-flex justify-content-center align-items-center">
                    <a href="#" class="text-decoration-none text-dark" data-bs-toggle="dropdown" aria-expanded="false">
                        <img :src="authStore.userData.profile_picture"
                            class="auth-profile-pic rounded-circle object-fit-cover" alt="Menu" height="45px"
                            width="45px" />
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <router-link to="/" class="dropdown-item text-primary">
                            <i class="bi bi-house-door me-2"></i>{{ $t('Home') }}
                        </router-link>
                        <li>
                            <a class="dropdown-item text-danger" href="#" @click="logout()"><i
                                    class="bi bi-box-arrow-right me-2"></i>{{ $t('Log Out') }}</a>
                        </li>
                    </ul>
                </div>
                <div v-else class="mx-3">
                    <router-link to="/login" class="btn btn-primary rounded-pill w-100 w-md-0">{{ $t('Login') }}</router-link>
                </div>
            </div>
        </section>
    </header>
</template>

<style scoped>
.auth-profile-pic {
    width: 48px;
    height: 48px;
    border: 2px solid #78b3ce;
}

.collapsing {
    transition: all ease-in-out 1s;
}

.navbar {
    transition: all ease-in-out .5s;

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
</style>

<script setup>
import { useAuthStore } from "@/stores/auth";
import { useMasterStore } from "@/stores/master";
import Swal from "sweetalert2";
import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

const router = useRouter();

let toggleMenu = ref(false);

const authStore = useAuthStore();
const masterStore = useMasterStore();
const isLoggedIn = ref(false);

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
            })
            .catch((error) => {
                console.error("Error fetching data:", error);
            });
    }
});

function logout() {
    Swal.fire({
        title: "Are you sure?",
        text: "Do you want to log out?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, log out!",
    }).then((result) => {
        if (result.isConfirmed) {
            authStore.clearAuthData();

            Swal.fire({
                title: "Logged Out!",
                text: "Log out successful.",
                showConfirmButton: false,
                icon: "success",
                timer: 1500,
            });
        }
        router.push("/");
    });
}
</script>
