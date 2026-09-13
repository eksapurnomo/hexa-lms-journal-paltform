<template>
    <footer class="px-3 border-top px-lg-5 py-4">
        <div class="row">
            <div class="col-12 my-3 my-lg-0">
                <p class="text-end m-0 text-primary">
                    <strong>{{ masterData?.credit_text }}</strong>
                </p>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { useMasterStore } from "@/stores/master";
import { ref } from "vue";

const masterStore = useMasterStore();
const masterData = ref(masterStore.masterData);
</script>
