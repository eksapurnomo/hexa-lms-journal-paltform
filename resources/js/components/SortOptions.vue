<template>
    <ul class="list-unstyled main-item">
        <div class="mb-3" style="background: white; position: sticky; top: 0px;">{{ $t('Sort By') }}</div>
        <li class="filter-item">
            <div class="form-check mb-2">
                <input @change="emit('sort', null, null), sortChanged = false" class="form-check-input" type="radio"
                    id="flexRadioDefault1" :checked="!sortChanged" :name="sortChanged ? 'flexRadioDefault' : ''" />
                <label class="form-check-label" for="flexRadioDefault1">
                    {{ $t('Relevance') }} ({{ $t('Default') }})
                </label>
            </div>
        </li>
        <li class="filter-item">
            <div class="form-check mb-2">
                <input @change="emit('sort', 'price', 'desc'), sortChanged = true" class="form-check-input" type="radio"
                    name="flexRadioDefault" id="flexRadioDefault2" />
                <label class="form-check-label" for="flexRadioDefault2">
                    {{ $t('Course Fee') }}: {{ $t('High to Low') }}
                </label>
            </div>
        </li>
        <li class="filter-item">
            <div class="form-check mb-2">
                <input @change="emit('sort', 'price', 'asc'), sortChanged = true" class="form-check-input" type="radio"
                    name="flexRadioDefault" id="flexRadioDefault3" />
                <label class="form-check-label" for="flexRadioDefault3">
                    {{ $t('Course Fee') }}: {{ $t('Low to High') }}
                </label>
            </div>
        </li>
        <li class="filter-item">
            <div class="form-check mb-2">
                <input @change="emit('sort', 'view_count', 'desc'), sortChanged = true" class="form-check-input"
                    type="radio" name="flexRadioDefault" id="flexRadioDefault4" />
                <label class="form-check-label" for="flexRadioDefault4">
                    {{ $t('Popular Courses') }}
                </label>
            </div>
        </li>
        <li>
            <div class="form-check mb-2">
                <input @change="emit('sort', 'published_at', 'desc'), sortChanged = true" class="form-check-input"
                    type="radio" name="flexRadioDefault" id="flexRadioDefault5" />
                <label class="form-check-label" for="flexRadioDefault5">
                    {{ $t('Newest Courses') }}
                </label>
            </div>
        </li>
        <!-- <li>
            <div class="form-check mb-2">
                <input @input="emit('sort', property, order);" class="form-check-input" type="radio"
                    name="flexRadioDefault" id="flexRadioDefault6">
                <label class="form-check-label" for="flexRadioDefault6">
                    Best Selling Courses
                </label>
            </div>
        </li> -->
        <li>
            <div class="form-check mb-2">
                <input @change="emit('sort', 'average_rating', 'desc'), sortChanged = true" class="form-check-input"
                    type="radio" name="flexRadioDefault" id="flexRadioDefault7" />
                <label class="form-check-label" for="flexRadioDefault7">
                    {{ $t('Rating') }}
                </label>
            </div>
        </li>
    </ul>
</template>

<script setup>
import { ref } from 'vue';
const emit = defineEmits(["sort"]);
let sortChanged = ref(false);

</script>
