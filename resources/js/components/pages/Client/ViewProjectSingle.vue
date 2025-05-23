<script setup>
import { onMounted,ref } from "vue";
import { useRoute } from "vue-router";
import api from "../../../api";
import Button from "../../component/Button.vue";
import { CircleUserRound, Tag } from 'lucide-vue-next';

const route = useRoute();
const freelance = ref('');
const proposals = ref(['']);
const slug = ref(route.params.slug);
const canViewProposals = ref(false); 

onMounted(() => {
    fetchProposals();
    fetchFreelance();
})

async function fetchFreelance(){
    try {
        const response = await api.get(`/freelances/${slug.value}`);
        freelance.value = response.data.data.freelance_project_details;
    } catch (error){
        console.error(error);
    }
}

async function fetchProposals() {
    try{
        const response = await api.get(`/users/freelances/${slug.value}/proposals`);
        if (response.status === 200 || response.status === 201) {
            proposals.value = response.data.data;
            canViewProposals.value = true;
        }
        if (response.status === 401) alert("unauthorized"); 
    } catch (error) {
        if (error.status === 401) alert("unauthorized");
        console.error(error);
    }
}
</script>

<template>
    <div class="max-w-[1300px] w-full mx-auto md:px-0 px-4">
        <div v-if="canViewProposals">
            <div class="max-w-[1300px] border-1 border-gray-300 w-full h-auto flex flex-col justify-center mx-auto my-12 p-4">
                <div class="grid grid-cols-12">
                    <div class="col-span-12">
                        <p class="font-bold text-2xl">Project Details</p>
                        <div class="border-b border-gray-300 p-4">
                            <div class="flex gap-4">
                                <p class="font-bold text-xl">{{ freelance?.title }}</p>
                                <div class="flex items-center text-sm text-gray-500 mt-1 gap-2">
                                    <Tag class="w-4 h-4"/> Php {{ freelance?.rate }}
                                </div>
                            </div>
                             
                            <p class="text-sm text-gray-500 mt-2">{{new Date(freelance?.date_posted).toLocaleDateString("en-US", { year:'numeric',month:'long',day:'numeric' })}}</p>
                        </div>
                        <div class="border-b border-gray-300 p-4">
                            <p>{{ freelance?.description }}</p>
                        </div>
                        <div class="border-b border-gray-300 p-4">
                            <p class="font-bold text-xl">Category</p>
                            <div class="bg-blue-500 text-white text-center rounded-xl p-4 w-fit h-8 flex items-center mt-4">
                                {{ freelance?.category }}
                            </div>
                        </div>
                        <div class="flex flex-col gap-4 p-4">
                            <p class="font-bold text-xl">Activity</p>
                            <p class="text-sm text-gray-500">Total Proposals: {{ freelance?.number_of_total_proposals }}</p>
                            <p class="text-sm text-gray-500">Pending Proposals: {{ freelance?.number_of_pending_proposals }}</p>
                            <p class="text-sm text-gray-500">Declined Proposals: {{ freelance?.number_of_declined_proposals }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <p class="font-bold text-2xl">Active proposals</p>
            <div v-for="(proposal,index) in proposals" :key="proposal?.proposal_details?.id"
                :class="{
                'border-b border-t border-gray-300 w-full h-auto flex flex-col justify-center mx-auto gap-2 px-2 py-4 my-4': true,
                'bg-gray-100': index % 2 != 0
                }">
                <div class="flex gap-4">
                    <div>
                        <CircleUserRound class="w-16 h-16"/>
                    </div>
                    <div>
                        <router-link :to="{name:'view-profile',params:{username:proposal?.proposal_details?.freelancer_username}}" class="font-bold text-xl mt-1 text-blue-500">
                            {{ proposal?.proposal_details?.freelancer_name }}
                        </router-link>
                        <p class="text-sm text-gray-500">{{new Date(proposal?.proposal_details?.created_at).toLocaleDateString("en-US", { year:'numeric',month:'long',day:'numeric' })}}</p>
                        <p class="mt-5">{{ proposal?.proposal_details?.description }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-4">
                    <Button name="Accept" class="bg-green-500"></Button>
                    <Button name="Reject" class="bg-red-500"></Button>
                </div>
            </div>
            <br/><br/><br/><br/><br/><br/>
        </div>
        <div class="flex justify-center items-center" v-else>
            Unauthorized
        </div>
    </div>
</template>