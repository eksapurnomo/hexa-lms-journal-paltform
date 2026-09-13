<template>
    <section class="bg-light py-4">
        <section class="container">
            <div class="row">
                <div class="col-12 d-block d-lg-none mb-4 mb-lg-0 d-flex justify-content-end align-items-center gap-2">
                    <span class="text-muted fs-6">{{ $t('Filtering') }}: </span>
                    <button type="button" class="btn btn-sm px-3 py-2 fs-5 fw-bold" :class="filterToggle
                        ? 'btn-outline-primary'
                        : 'border-2 border-primary'
                        " @click="filterToggle = !filterToggle">
                        <i :class="filterToggle
                            ? 'bi bi-funnel-fill'
                            : 'text-primary bi bi-funnel'
                            "></i>
                    </button>
                </div>
                <!-- Filter -->
                <div class="col-12 col-lg-3 d-none d-lg-block mb-4 mb-lg-0">
                    <div class="bg-white rounded-3 overflow-hidden" style="position: sticky; top: 100px;">
                        <div
                            class="d-flex justify-content-between align-items-center border-bottom border-light px-3 py-4">
                            <h5>{{ $t('Filters') }}</h5>
                            <span @click="applyReset"
                                class="text-decoration-none cursor-pointer text-danger">{{ $t('Reset') }}</span>
                        </div>
                        <div class="input-group my-2 px-2" role="search">
                            <span
                                class="input-group-text bg-light border-0 border-start border-top border-bottom px-3 py-2"
                                id="basic-addon1">
                                <i class="ri ri-search-line"></i>
                            </span>
                            <input v-model="filterQuery"
                                class="form-control search-input border-0 border-top border-bottom border-end bg-light ps-0 py-2"
                                type="search" :placeholder="$t('Search Filters data here')" />
                        </div>

                        <div style="overflow-y: auto; max-height: calc(100vh - 245px);">
                            <div class="px-3 py-1 border-bottom">
                                <CategoryFilter @categoryFilter="applyCatFilter" />
                            </div>
                            <div class="px-3 py-1 border-bottom">
                                <SortOptions @sort="applySort" />
                            </div>
                            <div class="px-3 py-1 border-bottom">
                                <InstructorFilter @instructorFilter="applyInstFilter" />
                            </div>
                            <div class="px-3 py-1">
                                <RatingFilter @RatingFilter="applyRatingFilter" />
                            </div>
                        </div>

                    </div>
                </div>
                <!-- filter end -->

                <div class="col-12 col-lg-3 mb-4 mb-lg-0" :class="filterToggle ? 'd-block' : 'd-none'">
                    <div class="bg-white rounded-3">
                        <div
                            class="d-flex justify-content-between align-items-center border-bottom border-light px-3 py-4">
                            <h5>{{ $t('Filters') }}</h5>
                            <span @click="applyReset"
                                class="text-decoration-none cursor-pointer text-danger">{{ $t('Reset') }}</span>
                        </div>

                        <div class="px-3 py-1 border-bottom">
                            <CategoryFilter @categoryFilter="applyCatFilter" />
                        </div>

                        <div class="px-3 py-1 border-bottom">
                            <SortOptions @sort="applySort" />
                        </div>

                        <div class="px-3 py-1 border-bottom">
                            <InstructorFilter @instructorFilter="applyInstFilter" />
                        </div>

                        <div class="px-3 py-1">
                            <RatingFilter @RatingFilter="applyRatingFilter" />
                        </div>


                    </div>
                </div>

                <div class="col-12 col-lg-9">
                    <h1 v-if="search" class="fw-bold text-center mb-5">
                        <span class="text-muted">{{ $t('Search') }} -</span> {{ search }}
                    </h1>
                    <h1 v-if="category_id" class="fw-bold text-center mb-5">
                        <span class="text-muted">{{ $t('Category') }} -</span>
                        {{ categoryTitle }}
                    </h1>
                    <section class="row align-items-center p-3 rounded-3 bg-white mb-4">
                        <div class="col-12 col-lg-6 text-center mb-3 mb-lg-0 text-lg-start">
                            <span>{{ $t('Showing') }} {{ courses.length }} of
                                {{ totalItems }} {{ $t('courses') }}</span>
                        </div>

                        <div class="col-12 col-lg-6">
                            <form @submit.prevent="performSearch" class="input-group border rounded-pill" role="search">
                                <input v-model="searchInputQuery"
                                    class="form-control border-0 rounded-pill search-input" type="search"
                                    :placeholder="$t('Search Course')" @input="
                                        searchInputQuery === ''
                                            ? applyReset()
                                            : null
                                        " />
                                <button type="submit" class="btn btn-primary d-flex rounded-pill px-4">
                                    <img :src="'/assets/images/website/search.svg'" alt="Search" />
                                </button>
                            </form>
                        </div>
                    </section>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 row-cols-xl-3">
                        <div v-for="course in courses" :key="course.id" class="mb-4">
                            <CourseCard :course="course" />
                        </div>
                    </div>
                    <div v-if="courses.length == 0" class="text-center my-5">
                        <h1>
                            <i class="ri-emotion-unhappy-line text-muted d-block display-1 mb-3"></i>
                        </h1>
                        <h3>{{ $t('No courses found') }}.</h3>
                    </div>

                    <div v-if="courses.length > 0" class="text-center my-4">
                        <VueAwesomePaginate v-model="currentPage" :total-items="totalItems"
                            :items-per-page="itemsPerPage" :max-pages-shown="5" @click="onClickHandler" />
                    </div>
                </div>
            </div>
        </section>
    </section>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import axios from "axios";
import CategoryFilter from "../components/CategoryFilter.vue";
import RatingFilter from "../components/RatingFilter.vue";
import InstructorFilter from "../components/InstructorFilter.vue";
import SortOptions from "../components/SortOptions.vue";
import { VueAwesomePaginate } from "vue-awesome-paginate";
import CourseCard from "../components/CourseCard.vue";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();
let filterToggle = ref(false);

const search = ref(route.query.search);
const category_id = ref(route.query.category_id);
const searchInputQuery = ref("");

let categoryTitle = ref("");

let courses = ref([]);
let currentPage = ref(1);
let itemsPerPage = ref(15);
let totalItems = ref(0);
let filterQuery = ref("");

const onClickHandler = (page) => {
    fetchCourses(page);
};

let params = {
    items_per_page: 10,
    page_number: 1,
    sort: "view_count",
    sortDirection: "desc",
};

function applyCatFilter(filterCat) {
    if (filterCat.length === 0) {
        params = {
            items_per_page: 12,
            page_number: 1,
            sort: "view_count",
            sortDirection: "desc",
        };
        router.push("/courses");
        fetchCourses();
    } else {
        params.category_id = filterCat;
        fetchCourses();
    }
}

function applyRatingFilter(filterRat) {
    params.average_rating = filterRat;
    fetchCourses();
}

function applyInstFilter(filterInst) {
    if (filterInst.length === 0) {
        params = {
            items_per_page: 12,
            page_number: 1,
            sort: "view_count",
            sortDirection: "desc",
        };
        router.push("/courses");
        fetchCourses();
    } else {
        params.instructor_id = filterInst;
        fetchCourses();
    }
}

function applySort(property, order) {
    params.sort = property;
    params.sortDirection = order;
    fetchCourses();
}

function applyReset() {
    search.value = null;
    params = {
        items_per_page: 12,
        page_number: 1,
        sort: "view_count",
        sortDirection: "desc",
    };
    fetchCourses();

    const radioInputs = document.querySelectorAll('input[type="radio"]');
    radioInputs.forEach((input) => {
        input.checked = false;
    });

    const checkboxInputs = document.querySelectorAll('input[type="checkbox"]');
    checkboxInputs.forEach((input) => {
        input.checked = false;
    });
    location.reload();
}

onMounted(() => {
    fetchCourses();
});

watch(
    () => route.query,
    () => {
        search.value = route.query.search;
        category_id.value = route.query.category_id;
        searchInputQuery.value = route.query.search;
        fetchCourses();
    }
);

// Fetch courses
function fetchCourses(pageNumber = 1) {
    if (search.value) {
        params["search"] = search.value;
    }

    if (category_id.value) {
        params["category_id"] = category_id.value;
    }

    params["items_per_page"] = itemsPerPage.value;
    params["page_number"] = pageNumber;

    if (authStore?.authToken) {
        axios
            .get(`/course/list`, {
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    Authorization: `Bearer ${authStore.authToken}`,
                },
                params: params,
            })
            .then((res) => {
                courses.value = res.data.data.courses;
                totalItems.value = res.data.data.total_courses;
                categoryTitle.value = res.data.data.courses[0]?.category || "";
            })
            .catch((error) => {
                console.error("Error fetching courses:", error);
            });
    } else {
        axios
            .get(`/course/list`, {
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                params: params,
            })
            .then((res) => {
                courses.value = res.data.data.courses;
                totalItems.value = res.data.data.total_courses;
                categoryTitle.value = res.data.data.courses[0]?.category || "";
            })
            .catch((error) => {
                console.error("Error fetching courses:", error);
            });
    }
}

const performSearch = () => {
    if (searchInputQuery.value) {
        router.push(`/courses?search=${searchInputQuery.value}`);
    }
};

watch(
    () => filterQuery.value,
    () => {
        searchFilter();
    }
);

function searchFilter() {
    const filterValue = filterQuery.value.toLowerCase().trim();
    const items = document.querySelectorAll('.filter-item');
    const mainItems = document.querySelectorAll('.main-item');

    items.forEach(item => {
        const isVisible = item.textContent.toLowerCase().includes(filterValue);
        item.classList.toggle('d-block', isVisible);
        item.classList.toggle('d-none', !isVisible);
    });

    mainItems.forEach(mainItem => {
        const visibleChildren = [...mainItem.children].some(child => child.classList.contains('d-block'));
        mainItem.classList.toggle('d-block', visibleChildren);
        mainItem.classList.toggle('d-none', !visibleChildren);
    });
}


</script>

<style lang="scss">
.filter-list {
    height: 110px;
    overflow-y: scroll;
}
</style>
