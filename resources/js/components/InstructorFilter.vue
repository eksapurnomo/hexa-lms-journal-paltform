<template>
    <ul class="list-unstyled main-item">
        <div class="mb-3" id="instructor" style="background: white; position: sticky; top: 0px;">
            {{ $t('Instructors') }} ({{ totalInstructors }})
        </div>
        <li v-for="instructor in instructors" :key="instructor.id" class="filter-item">
            <div class="form-check mb-2">
                <input @change="applyInstFilter($event, instructor.id)" class="form-check-input" type="checkbox"
                    name="instructor" :id="'instFilter' + instructor.id" :value="instructor.id" />
                <label class="form-check-label" :for="'instFilter' + instructor.id">
                    {{ instructor.name }}
                </label>
            </div>
        </li>
    </ul>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

const router = useRouter();
const baseUrl = import.meta.env.VITE_APP_URL;
const instructors = ref([]);
const totalInstructors = ref(0);
const selectedInst = ref([]);
const emit = defineEmits(["instructorFilter"]);

function applyInstFilter(e, id) {
    if (e.target.checked) {
        selectedInst.value.push(id);
    } else {
        selectedInst.value = selectedInst.value.filter((item) => item !== id);
    }

    emit("instructorFilter", selectedInst.value);
    router.push("/courses");
}

// Fetch instructors
function fetchInstructors() {
    axios
        .get(`/instructor/list`, {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
            },
        })
        .then((res) => {
            instructors.value = res.data.data.instructors;
            totalInstructors.value = res.data.data.total_items;
        })
        .catch((error) => {
            console.error("Error fetching instructors:", error);
        });
}

onMounted(() => {
    fetchInstructors();
});
</script>
