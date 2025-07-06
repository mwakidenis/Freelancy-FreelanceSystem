<script setup>
import { onMounted, ref } from 'vue';
import { useRoute,useRouter } from 'vue-router';
import { useAuthStore } from '../../../authStore';
import api from '../../../api';
import { Tag } from 'lucide-vue-next';
import Button from "../../component/Button.vue";
import { ClipLoader } from 'vue-spinner/dist/vue-spinner.min.js';
import Swal from 'sweetalert2';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const slug = ref(route.params.slug);

const freelanceDetails = ref('');
const clientDetails = ref('');

const isLoading = ref(true);

const canApply = ref(false);
const description = ref('');

const savingMessage = ref('');
const isError = ref(false);

onMounted(async () => {
    if (authStore.isAuthenticated && authStore.getUser.user_type == 'freelancer'){
        await checkIfUserCanApply();
        if (canApply) await fetchFreelance();
    }
    isLoading.value = false;
});

const confirmAndProcess = async () => {
  const result = await Swal.fire({ //swal confirmation modal 
    text: "Submit the proposal?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    confirmButtonColor: '#22c55e',
    cancelButtonText: 'Cancel',
    cancelButtonColor: "#ef4444",
  })

  if (result.isConfirmed) { 
    Swal.fire({ //swal loading
      text: "Submitting ...",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading()
      }
    })

    await postApplyFreelance();

    Swal.fire({ //swal result
      text: savingMessage.value,
      icon: isError.value == true ? 'error' : 'success',
      timer: 1500,
      showConfirmButton: false
    });

    router.push("/proposals");
    
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

async function fetchFreelance(){
    try{
        const response = await api.get(`/freelances/${slug.value}`);
        freelanceDetails.value = response.data.data.freelance_project_details;
        clientDetails.value = response.data.data.client_details;
    } catch (error){
        console.error(error);
    }
}

async function postApplyFreelance(){
    return new Promise((resolve) => {
        setTimeout(async() => {
            try {
                const response = await api.post(`/proposals/${slug.value}`, 
                    {
                        description:description.value
                    },
                    {withCredentials:true});
                savingMessage.value = "Proposal submitted successfully. Redirecting ...";
                isError.value = false;
            } catch (error){
                isError.value = false;
                console.error(error);
            } finally {
                description.value = null;
                resolve();
            }
        },2000)
    });
}
</script>

<template>
    <div v-if="isLoading" class="flex items-center justify-center gap-4 mt-60">
        <clip-loader color="#2b7fff"></clip-loader>
    </div>

    <div v-else-if="authStore.getUser.user_type == 'freelancer' && canApply">
        <div class="border-1 border-gray-300 w-full h-auto flex flex-col justify-center mx-auto my-12 p-4">
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

        <div class="border-1 border-gray-300 w-full h-auto flex flex-col justify-center mx-auto my-12 p-4 gap-4">
            <p class="font-bold text-sm">Please input your proposal description below</p>
            <form @submit.prevent="applyFreelance" class="flex flex-col justify-center gap-4">
                <textarea v-model="description" id="message" rows="12" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                placeholder="Write your thoughts here..."></textarea>
                <Button @click="confirmAndProcess()" type="submit" name="Apply" class="ml-auto"></Button>
            </form>
        </div>
    </div>

    <div v-else class="flex flex-col items-center justify-center gap-4 mt-60">
        <p class="text-4xl font-bold">Unauthorized</p>
        <p class="text-gray-500">Click here to go back to <router-link class="text-blue-500" :to="{name:'home'}">Home</router-link> </p>
    </div>
</template>