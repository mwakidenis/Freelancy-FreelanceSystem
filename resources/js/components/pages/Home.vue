<script setup>
import { ref, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { debounce } from "lodash";
import { useAuthStore } from "../../authStore";
import api from "../../api";
import InputBox from "../component/InputBox.vue";
import { Search } from 'lucide-vue-next';
import { ClipLoader } from 'vue-spinner/dist/vue-spinner.min.js';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const isLoading = ref(true);

const queryParams = ref(route.query.q || ""); 
const currentPage = ref(parseInt(route.query.page) || 1);
const sortParams = ref(route.query.sort || "1");

const freelances = ref([]);
const numberOfPage = ref(1);

onMounted(async() => {
    await fetchFreelances();
    isLoading.value = false;
});

// Watch multiple parameters and update API call accordingly
watch([queryParams, sortParams, currentPage], ([newQuery, newSort, newPage], [oldQuery, oldSort, oldPage]) => {
    if (newQuery !== oldQuery) { //search
        numberOfPage.value = 1;
        currentPage.value = 1; 
        fetchFreelancesDebounced();
    } else if (newSort !== oldSort) { //sort
        numberOfPage.value = 1;
        currentPage.value = 1;
        fetchFreelances();
    } else if (newPage !== oldPage) { // page
        fetchFreelances();
    }

    router.replace({
        query: {
            q: queryParams.value || undefined,
            page: currentPage.value  || undefined,
            sort: sortParams.value !== "1" ? sortParams.value : undefined,
        }
    });
});


// For url routing watch (cleaning params)
watch(
    () => route.query,
    (newQuery) => {
        if (!newQuery.q && !newQuery.page && !newQuery.sort) {
            queryParams.value = "";
            currentPage.value = undefined;
            sortParams.value = "1"; 
            freelances.value = [];
            numberOfPage.value = 1;
            fetchFreelances();
        }
    },
    { immediate: true }
);

// Pagination function
const switchPage = (page) => {
    if (page >= 1 && page <= numberOfPage.value) {
        currentPage.value = page;
    }
};

// Debounce function for search
const fetchFreelancesDebounced = debounce(fetchFreelances, 500);

// API call
async function fetchFreelances() {
    try {
        const response = await api.get("/freelances", { 
            params: { 
                query: queryParams.value || undefined, 
                page: currentPage.value || 1, // Ensure it never sends undefined
                sort: sortParams.value || undefined,
            } 
        });
        freelances.value = response.data.data || [];
        const totalItems = response.data.meta.total || 0;
        numberOfPage.value = Math.max(1, Math.ceil(totalItems / 10));
    } catch (error) {
        console.error("Error fetching freelances:", error);
    }
};
</script>

<template>

    <div v-if="isLoading" class="flex items-center justify-center gap-4 mt-60">
        <clip-loader color="#2b7fff"></clip-loader>
    </div>

    <div v-else>
        <div class="relative my-12">
            <InputBox class="w-full h-12 px-8" placeholder="Search..." v-model="queryParams"/>
            <Search class="absolute right-4 top-3 cursor-pointer" @click="fetchFreelancesDebounced(queryParams)"></Search>
        </div>

        <div v-if="freelances.length === 0" class="text-center text-gray-500 my-6">
            No results found.
        </div>

        <div v-else>
            <div class="flex justify-between items-center">
                <div class="my-6">
                    <select class="bg-gray-100 w-full h-8 px-2 rounded-lg cursor-pointer" @change="sortParams = $event.target.value">
                        <option value="1">Latest</option> <!-- LATEST ORDER BY DESC > 1 --> 
                        <option value="2">Oldest</option> <!-- OLDEST ORDER BY ASC > 2 -->
                        <option value="3">Low to High (Rate)</option> <!-- CHEAPEST ORDER BY ASC 3 -->
                        <option value="4">High to Low (Rate)</option> <!-- HIGHEST ORDER BY DESC 4 -->
                    </select>
                </div>
                <div class="flex my-6 gap-2">
                    <div v-for="(n, index) in numberOfPage" :key="index"
                        :class="{
                            'h-8 w-8 bg-gray-100 flex items-center justify-center cursor-pointer': true,
                            'bg-gray-300': parseInt(currentPage) === index + 1
                        }"
                        @click="switchPage(index + 1, queryParams)">
                        {{ index + 1 }}
                    </div>
                </div>
            </div>

        </div>

        <router-link :to="{name: 'freelance-details' , params: {slug:freelance?.freelance_project_details?.slug}}" v-for="(freelance, index) in freelances" :key="freelance?.id"
            :class="{
                'border-b border-t border-gray-300 w-full h-auto flex flex-col justify-center mx-auto gap-2 px-2 py-4 hover:opacity-90': true,
                'bg-gray-100': index % 2 != 0
            }">
            
                <div class="flex items-center justify-between">
                    <p class="text-bold text-xl">{{ freelance?.freelance_project_details?.title }}</p>
                    <p class="text-xs text-gray-500">{{new Date(freelance?.freelance_project_details?.date_posted).toLocaleDateString("en-US", { year:'numeric',month:'long',day:'numeric' })}}</p>
                </div>
                
                <p class="text-xs text-gray-500">Rate: Php {{ freelance?.freelance_project_details?.rate }}</p>
                <p>{{ freelance?.description }}</p>
                <span class="text-white py-1 px-10 rounded-full bg-blue-500 text-xs w-fit">
                    {{ freelance?.freelance_project_details?.category }}
                </span>
                <p class="text-xs text-gray-500">Number of proposals: {{ freelance?.freelance_project_details?.number_of_total_proposals }}</p>
            
        </router-link>  
        
        <div class="flex my-12 float-right gap-2">
            <div v-for="(n, index) in numberOfPage" :key="index"
                :class="{
                    'h-8 w-8 bg-gray-100 flex items-center justify-center cursor-pointer': true,
                    'bg-gray-300': parseInt(currentPage) === index + 1
                }"
                @click="switchPage(index + 1, queryParams)">
                {{ index + 1 }}
            </div>
        </div>
    </div>



</template>
