<template>
    <ul class="list-unstyled main-item">
        <div class="mb-3 bg-white" id="category" style="position: sticky; top: 0px;">
            {{$t('Categories')}} ({{ totalCategories }})
        </div>
        <li v-for="category in categories" :key="category.id" class="filter-item">
            <div v-if="category.show !== false" class="form-check mb-2">
                <input @change="applyCatFilter($event, category.id)" :id="'catFilter' + category.id"
                    class="form-check-input" type="checkbox" :value="category.id" :name="'catFilter'" />
                <label :for="'catFilter' + category.id" class="form-check-label">
                    {{ category.title }}
                </label>
            </div>
        </li>
    </ul>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const categories = ref([]);
const totalCategories = ref(0);

const selectedCat = ref([]);
const searchInputQuery = ref("");
const emit = defineEmits(["categoryFilter"]);

function applyCatFilter(e, id) {
    // if (e.target.checked) {
    //     selectedCat.value.push(id);
    //     emit("categoryFilter", selectedCat.value);
    //     router.push("/courses");
    // } else {
    //     emit("categoryFilter", selectedCat.value);
    //     router.push("/courses");
    // }
    if (e.target.checked) {
        selectedCat.value.push(id);
    } else {
        selectedCat.value = selectedCat.value.filter((item) => item !== id);
    }

    emit("categoryFilter", selectedCat.value);
    router.push("/courses");
}

// Fetch categories
function fetchCategories(searchQuery) {
    axios.get("/categories", {
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        params: {
            search: searchQuery,
        },
    })
        .then((res) => {
            categories.value = res.data.data.categories;
            totalCategories.value = res.data.data.total_items;
        })
        .catch((error) => {
            console.error("Error fetching categories:", error);
        });
}

onMounted(() => {
    fetchCategories();
});
</script>
