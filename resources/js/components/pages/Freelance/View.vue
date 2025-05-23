<script setup>
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../../api';
import { useAuthStore } from '../../../authStore';
import { Tag } from 'lucide-vue-next';

const route = useRoute();
const slug = ref(route.params.slug);
const authStore = useAuthStore();

const freelanceDetails = ref('');
const clientDetails = ref('');

const isFreelancer = ref(false);
const canApply = ref(false);

onMounted(() => {
    fetchFreelance();
});

async function fetchFreelance(){
    try{
        const response = await api.get(`/freelances/${slug.value}`);
        freelanceDetails.value = response.data.data.freelance_project_details;
        clientDetails.value = response.data.data.client_details;

        if (authStore.isAuthenticated && authStore.isFreelancer){
            isFreelancer.value = true;
            checkIfUserCanApply();
        }
    } catch (error){
        console.error(error);
    }
}

async function checkIfUserCanApply() {
    try {
        const response = await api.get(`/proposals/${slug.value}/check`);
        if (response.status === 200) canApply.value = true;
    } catch (error){
        console.error(error);
    }
}

</script>

<template>

    <div class="max-w-[1300px] w-full h-auto flex flex-col justify-center mx-auto my-12 px-4">
        <div class="grid grid-cols-12">
            <div class="md:col-span-10 col-span-12">
                <div class="border-b border-gray-300 p-4">
                    <p class="font-bold text-xl">{{ freelanceDetails?.title }}</p>
                    <p class="text-sm text-gray-500 mt-2">{{new Date(freelanceDetails?.date_posted).toLocaleDateString("en-US", { year:'numeric',month:'long',day:'numeric' })}}</p>
                </div>
                <div class="border-b border-gray-300 p-4">
                    <p>{{ freelanceDetails?.description }}</p>
                </div>
                <div class="border-b border-gray-300 p-4">
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <Tag/> Php {{ freelanceDetails?.rate }}
                    </div>
                </div>
                <div class="border-b border-gray-300 p-4">
                    <p class="font-bold text-xl">Category</p>
                    <div class="bg-blue-500 text-white text-center rounded-xl p-4 w-fit h-8 flex items-center mt-4">
                        {{ freelanceDetails?.category }}
                    </div>
                </div>
                <div class="flex flex-col gap-4 p-4">
                    <p class="font-bold text-xl">Activity</p>
                    <p class="text-sm text-gray-500">Total Proposals: {{ freelanceDetails?.number_of_total_proposals }}</p>
                    <p class="text-sm text-gray-500">Pending Proposals: {{ freelanceDetails?.number_of_pending_proposals }}</p>
                    <p class="text-sm text-gray-500">Declined Proposals: {{ freelanceDetails?.number_of_declined_proposals }}</p>
                </div>
            </div>
            <div class="md:col-span-2 col-span-12 md:border-l border-gray-300 flex flex-col gap-4 ">
                <div class="p-4">
                    <router-link v-if="canApply" :to="{ name:'freelance-apply', params: { slug: freelanceDetails?.slug } }" 
                        class="bg-blue-500 text-white text-center rounded-xl p-4 w-full h-12 flex items-center justify-center mt-4 cursor-pointer">
                        Apply now
                    </router-link>
                </div>
                <div class="font-bold text-lg md:border-none border-t border-gray-300">
                    <p class="px-4 md:mt-0 mt-4">About the client</p>
                </div>
                <div class="px-4">
                    <p class="text-sm font-bold text-gray-500">{{ clientDetails?.client_name }}</p>
                </div>
                <div class="px-4">
                    <p class="text-sm font-bold text-gray-500">{{ clientDetails?.number_of_total_projects }} job/s posted</p>
                </div>
                <div class="px-4">
                    <p class="text-xs text-gray-500">Member since {{new Date(clientDetails?.date_registered).toLocaleDateString("en-US", { year:'numeric',month:'long',day:'numeric' })}}</p>
                </div> 
            </div>
        </div>


    </div>

</template>