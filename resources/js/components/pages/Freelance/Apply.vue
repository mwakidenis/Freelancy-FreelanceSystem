<script setup>
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../../../authStore';
import api from '../../../api';
import { Tag } from 'lucide-vue-next';
import Button from "../../component/Button.vue";

const route = useRoute();
const authStore = useAuthStore();
const slug = ref(route.params.slug);

const isFreelancer = ref(false);
const canApply = ref(false);

const freelanceDetails = ref('');
const clientDetails = ref('');

const description = ref('');

onMounted(() => {
    if (authStore.isAuthenticated && authStore.getUserRole == 'freelancer'){
        isFreelancer.value = true;
        checkIfUserCanApply();
        if (canApply) fetchFreelance();
    }
});

async function checkIfUserCanApply() {
    try {
        const response = await api.get(`/proposals/${slug.value}/check`);
        if (response.status === 200) canApply.value = true;
    } catch (error){
        console.error(error);
    }
}

async function fetchFreelance(){
    try{
        const response = await api.get(`/freelances/${slug.value}`);
        freelanceDetails.value = response.data.data.freelance_project_details;
        clientDetails.value = response.data.data.client_details;
    } catch (error){
        console.error(error);
    }
}

const applyFreelance = async() => {
    try{
        const response = await api.post(`/proposals/${slug.value}`,
            {
                description:description.value
            },
            {withCredentials:true});
        console.log("response is:",response);
        if (response.status === 409){
            alert("you have already applied for this job!");
        }
        else if (response.status === 201) alert("successfully applied");
        else alert ("error");
        //create loading state & router push to forward page after applying 
    } catch (error){
        console.error(error);
    }
}
</script>

<template>
    <div class="flex items-center justify-center h-screen" v-if="!isFreelancer">
        Unauthorized
    </div>
    <div v-else>
        <div class="flex items-center justify-center h-screen" v-if="!canApply">
            You have pending application to this project
        </div>
        <div v-else>
            <div class="max-w-[1300px] border-1 border-gray-300 w-full h-auto flex flex-col justify-center mx-auto my-12 p-4">
                <div class="grid grid-cols-12">
                    <div class="col-span-12">
                        <p class="font-bold text-2xl">Job Details</p>
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
                        <div class="border-b border-gray-300 flex flex-col gap-4 p-4">
                            <p class="font-bold text-xl">Activity</p>
                            <p class="text-sm text-gray-500">Total Proposals: {{ freelanceDetails?.number_of_total_proposals }}</p>
                            <p class="text-sm text-gray-500">Pending Proposals: {{ freelanceDetails?.number_of_pending_proposals }}</p>
                            <p class="text-sm text-gray-500">Declined Proposals: {{ freelanceDetails?.number_of_declined_proposals }}</p>
                        </div>
                        <p class="font-bold text-2xl mt-4">Client Details</p>
                        <div class="px-4 mt-4">
                            <p class="text-xl font-bold text-gray-500">{{ clientDetails?.client_name }}</p>
                        </div>
                        <div class="px-4 mt-4">
                            <p class="text-sm font-bold text-gray-500">{{ clientDetails?.number_of_total_projects }} job/s posted</p>
                        </div>
                        <div class="px-4 mt-4">
                            <p class="text-xs text-gray-500">Member since {{new Date(clientDetails?.date_registered).toLocaleDateString("en-US", { year:'numeric',month:'long',day:'numeric' })}}</p>
                        </div> 
                    </div>
                </div>
            </div>

            <div class="max-w-[1300px] border-1 border-gray-300 w-full h-auto flex flex-col justify-center mx-auto my-12 p-4 gap-4">
                <p class="font-bold text-sm">Please input your proposal description below</p>
                <form @submit.prevent="applyFreelance" class="flex flex-col justify-center gap-4">
                    <textarea v-model="description" id="message" rows="12" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                    placeholder="Write your thoughts here..."></textarea>
                    <Button type="submit" name="Apply" class="ml-auto"></Button>
                </form>
            </div>
        </div>
    </div>

</template>