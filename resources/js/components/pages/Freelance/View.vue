<script setup>
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../../api';
import { useAuthStore } from '../../../authStore';
import { Tag,CircleUserRound,CircleX,Save } from 'lucide-vue-next';
import moment from 'moment';
import { ClipLoader } from 'vue-spinner/dist/vue-spinner.min.js'
import Swal from 'sweetalert2';

const route = useRoute();
const slug = ref(route.params.slug);
const authStore = useAuthStore();

const isLoading = ref(true);
const isFreelanceValid = ref(false);

const freelanceDetails = ref('');
const clientDetails = ref('');

const proposals = ref([]);

const activeProposals = ref([]);
const acceptedProposals = ref([]);
const declinedProposals = ref([]);
const withdrawnProposals = ref([]);
const isTypeOfProposal = ref('Pending');

const isLoadingProposal = ref(false);

const status = ['Pending','Accepted','Declined','Withdrawn'];

const isFreelancer = ref(false);
const canApply = ref(false);

const clientCanProcess = ref(false);

// modal
const isModalOpen = ref(false);
const actionToDo = ref();
const indexToEdit = ref();
const proposalToEdit = ref();
const remarks = ref();

const savingMessage = ref();
const isError = ref(false);

onMounted(async() => {
    await fetchFreelance();
    await fetchProposals();
    await test();
    isLoading.value = false;
});

const switchProposalType = (type) => {
    isTypeOfProposal.value = type;
    isLoadingProposal.value = true;
    setTimeout(() => {
        switch(type){
            case 'Pending':
                proposals.value = activeProposals.value;
                break;
            case 'Accepted':
                proposals.value = acceptedProposals.value;
                break;
            case 'Declined':
                proposals.value = declinedProposals.value;
                break;
            case 'Withdrawn':
                proposals.value = withdrawnProposals.value;
                break;
            default:
                break;
        }
        isLoadingProposal.value = false;
    },200);

}

function formatProposalTypeLength(type){
    const temp = ref(null);
    switch(type){
        case 'Pending':
            temp.value = activeProposals.value.length;
            break;
        case 'Accepted':
            temp.value = acceptedProposals.value.length;
            break;
        case 'Declined':
            temp.value = declinedProposals.value.length;
            break;
        case 'Withdrawn':
            temp.value = withdrawnProposals.value.length;
            break;
        default:
            break;
    }
    return temp;
}

const toggleModal = (type,action,index) => {
    if (type == 'Open') {
        isModalOpen.value = true;
        actionToDo.value = action;
        indexToEdit.value = index;
        proposalToEdit.value = proposals.value[index];
    } else {
        isModalOpen.value = false;
        actionToDo.value = null
        indexToEdit.value = null;
        proposalToEdit.value = null;
    }
}

const confirmAndProcess = async () => {
    switch(actionToDo.value){
        case 'Accept':
            savingMessage.value = "Accept the proposal?";
            break;
        case 'Decline':
            savingMessage.value = "Decline the proposal?";
            break;
        default:
            break;
    }

    const result = await Swal.fire({ //swal confirmation modal 
        text: savingMessage.value,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        confirmButtonColor: '#22c55e',
        cancelButtonText: 'Cancel',
        cancelButtonColor: "#ef4444",
    });

    if (result.isConfirmed){
        switch(actionToDo.value){
            case 'Accept':
                savingMessage.value = "Accepting the proposal ...";
                break;
            case 'Decline':
                savingMessage.value = "Declining the proposal ...";
                break;
            default:
                break;
        }

        Swal.fire({ //swal loading
            text: savingMessage.value,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        })

        await processProposal();

        Swal.fire({ //swal result
            text: savingMessage.value,
            icon: isError.value == true ? 'error' : 'success',
            timer: 1500,
            showConfirmButton: false
        });
    }


}

async function fetchFreelance(){
    try{
        const response = await api.get(`/freelances/${slug.value}`);
        freelanceDetails.value = response.data.data.freelance_project_details;
        clientDetails.value = response.data.data.client_details;

        if (authStore.isAuthenticated && authStore.getUserRole == 'freelancer'){
            isFreelancer.value = true;
            checkIfUserCanApply();
        }

        isFreelanceValid.value = true;
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

async function fetchProposals(){ 
    try {
        const response = await api.get(`/freelances/${slug.value}/proposals`);
        const allProposals = ref(response.data.data);

        allProposals.value.forEach((proposal) => {
            switch(proposal.status){
                case 'Pending':
                    activeProposals.value.push(proposal);
                    break;
                case "Accepted":
                    acceptedProposals.value.push(proposal);
                    break;
                case 'Declined':
                    declinedProposals.value.push(proposal);
                    break;
                case 'Withdrawn':
                    withdrawnProposals.value.push(proposal);
                    break;
                default:
                    break;
            }
        });
        proposals.value = activeProposals.value;
    } catch (error){
        console.error(error);
    }
}

async function processProposal(){
    const formData = new FormData();
    formData.append('type',actionToDo.value);
    formData.append('remarks',remarks.value);
    formData.append('_method','PUT');
    return new Promise((resolve) => {
        setTimeout(async() => {
            try {
                const response = await api.post(`/freelances/${proposalToEdit?.value.id}`,
                    formData,
                    {
                        withCredentials:true
                    }
                );
                savingMessage.value = "Proposal has been " + actionToDo.value.toLowerCase() + "ed.";
                activeProposals.value.splice(indexToEdit.value,1);
                if (actionToDo.value == 'Accept'){
                    proposalToEdit.value.status = 'Accepted';
                    acceptedProposals.value.unshift(proposalToEdit.value);
                } else {
                    proposalToEdit.value.status = 'Declined';
                    declinedProposals.value.unshift(proposalToEdit.value);
                }
                await fetchFreelance();
            } catch (error){
                savingMessage.value = 'Error';
                isError.value = true;
                console.error(error);
            } finally {
                resolve();
                isModalOpen.value = false;
                actionToDo.value = null;
                indexToEdit.value = null;
                proposalToEdit.value = null;
                remarks.value = null;
            }
        }, 2000)
    });

}

async function test(){
    try{
        const response = await api.get(`/freelances/client/${slug.value}/checkIfClientCanApprove`,{withCredentials:true});
        clientCanProcess.value = true;
        console.log("response from test: ", response);
    } catch (error){
        clientCanProcess.value = false;
        console.error(error);
    }
}
</script>

<template>


    <div v-if="isLoading" class="flex items-center justify-center gap-4 mt-60">
        <clip-loader color="#2b7fff"></clip-loader>
    </div>

    <div v-else>
        <div v-if="isFreelanceValid">
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
                        <p class="text-sm text-gray-500">Accepted Proposals: {{ freelanceDetails?.number_of_accepted_proposals }}</p>
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
                        <p class="px-4 md:mt-0 mt-4">Project Status</p>
                    </div> 
                    <div class="px-4">
                        <span class="text-white py-1 px-10 rounded-full"
                            :class="{
                            'bg-blue-500' : freelanceDetails?.status == 'Active',
                            'bg-yellow-500' : freelanceDetails?.status == 'In Progress',
                            'bg-gray-500' : freelanceDetails?.status == 'Inactive',
                            'bg-green-500' : freelanceDetails?.status == 'Done'
                        }">
                            {{ freelanceDetails?.status }}
                        </span>
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

            <div class="px-4 mb-40">
                <p class="text-2xl font-bold mb-12">Proposals</p>

                <div class="shadow-lg">

                    <div class="flex flex-wrap items-center md:justify-start justify-center bg-gray-100 min-h-12 max-h-auto gap-8">
                        <div v-for="(proposalStatus,index) in status" class="h-full flex items-center gap-1 p-4 cursor-pointer" :key="index"
                            :class="{
                                'ms-4' : index === 0,
                                'bg-white' : isTypeOfProposal == proposalStatus
                            }"
                            @click="switchProposalType(proposalStatus)">
                                {{ proposalStatus }} <span class="text-blue-500">({{ formatProposalTypeLength(proposalStatus) }})</span>
                        </div>
                    </div>
                    
                    <div v-if="proposals.length > 0" v-for="(proposal,index) in proposals" :key="proposal?.id" 
                        class="border-b border-gray-300 p-12 min-h-60"
                        :class="{'flex items-center justify-center': isLoadingProposal}">
                        
                        <div v-if="!isLoadingProposal" class="flex gap-4">
                            <div><CircleUserRound class="w-12 h-12"/></div>
                            <div class="flex flex-col mt-2 gap-4">
                                <router-link class="font-bold" :to="{name:'view-profile', params:{username:proposal?.freelancer_username}}">
                                    {{proposal?.freelancer_username}}
                                </router-link>
                                <div>
                                    {{ proposal?.description }}
                                </div>
                                <div v-if="clientCanProcess && proposal?.status == 'Pending'" class="flex text-gray-500 gap-4 text-sm ml-auto">
                                    <div class="cursor-pointer" @click="toggleModal('Open','Accept',index)">
                                        Accept Proposal
                                    </div>
                                    <div class="cursor-pointer" @click="toggleModal('Open','Decline',index)">
                                        Reject Proposal
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col ml-auto text-sm text-gray-300">
                                <div>{{moment(proposal?.created_at).format('MMMM DD, YYYY')}}</div>
                            </div>
                        </div>
                        <div v-else>
                            <clip-loader color="#2b7fff"></clip-loader>
                        </div>


                    </div> 

                    <div v-else class="min-h-60 flex items-center justify-center">
                        <p class="text-gray-300">No {{ isTypeOfProposal }} proposals yet.</p>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center gap-4 mt-60">
            <p class="text-4xl font-bold">Freelance project does not exist.</p>
            <p class="text-gray-500">Click here to go back to <router-link class="text-blue-500" :to="{name:'home'}">Home</router-link> </p>
        </div>
    </div>

    <div v-if="isModalOpen">
        <div class="fixed inset-0 bg-black opacity-70 z-40"></div> 
        <div class="fixed inset-0 flex justify-center items-center z-40" @click.self="toggleModal('Close',null,null)">
            <div class="bg-white w-4/5 h-5/6 p-6 rounded-lg shadow-lg flex flex-col overflow-auto">
                <p class="font-bold text-xl">{{actionToDo}} proposal</p>
                <hr class="my-4"/>
                <div class="flex flex-col">
                    <label class="font-semibold">Proposal Description by {{ proposalToEdit.freelancer_username }}</label>
                    {{ proposalToEdit.description }}
                </div>
                <br/>
                <div class="flex-1 flex-col gap-2">
                    <label class="font-semibold">Add remarks here</label>
                    <textarea v-model="remarks"  class="border border-gray-300 rounded w-full h-full mb-4"/>
                </div>
                <div class="flex gap-4 ml-auto mt-12">
                    <button @click="confirmAndProcess()"
                        class="bg-green-500 cursor-pointer text-white w-24 h-12 rounded-xl hover:opacity-80 ml-auto
                            flex items-center justify-center gap-2">
                        <span><Save class="w-4 h-4"/></span> Submit
                    </button>
                    <button @click="toggleModal('Close',null,null)"       
                        class="bg-red-500 cursor-pointer text-white w-24 h-12 rounded-xl hover:opacity-80 ml-auto
                            flex items-center justify-center gap-2">
                            <span><CircleX class="w-4 h-4"/></span> Close  
                    </button>
                </div>

            </div>
        </div>
    </div>

</template>