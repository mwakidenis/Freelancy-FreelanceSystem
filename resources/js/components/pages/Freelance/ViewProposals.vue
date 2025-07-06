<script setup>
import { onMounted,ref, watch } from 'vue';
import { useRoute,useRouter } from 'vue-router';
import { useAuthStore } from '../../../authStore';
import api from "../../../api";
import InputBox from '../../component/InputBox.vue';
import { Search,Eraser,ArrowUp,ArrowDown } from 'lucide-vue-next';
import moment from 'moment'; 
import Swal from 'sweetalert2';
import { debounce } from 'lodash';
import { ClipLoader } from 'vue-spinner/dist/vue-spinner.min.js'

const route = useRoute();
const router = useRouter();

const authStore = useAuthStore();
const isFreelancer = ref(false);
const isLoading = ref(true);

const proposals = ref([]);
const proposalsPaginated = ref([]);

//dropdown and edit
const proposalId = ref(null);

//search and sort 
const queryParams = ref(route.query.q || undefined);
const dateParams = ref();
const alphabeticalParams = ref();

//page
const totalItems = ref();
const itemsPerPage = 10;
const numberOfPage = ref();
const currentPage = ref(parseInt(route.query.page) || 1);
const startCurrentPage = ref(0);
const endCurrentPage = ref(itemsPerPage);

//swal and post api params
const savingMessage = ref();
const isError = ref(false);

onMounted(async() => {
    if (authStore.getUserRole == 'freelancer'){
        await fetchProposals();
        isFreelancer.value = true;
    }
    isLoading.value = false;
});

watch([currentPage,queryParams,dateParams,alphabeticalParams], 
    ([newCurrentPage,newQueryParams,newDateParams,newAlphabeticalParams],
    [oldCurrentPage,oldQueryParams,oldDateParams,oldAlphabeticalParams]) => {

    if (newCurrentPage !== oldCurrentPage){
        proposalsPaginated.value = proposals.value.slice(startCurrentPage.value,endCurrentPage.value);
    }

    if (newQueryParams !== oldQueryParams){
        currentPage.value = 1;
        alphabeticalParams.value = '';
        dateParams.value = '';
        fetchProposalsDebounced();
    }

    if (newDateParams !== oldDateParams) {
        currentPage.value = 1;
        alphabeticalParams.value = ''; 
        proposalsPaginated.value.reverse();
    }

    if (newAlphabeticalParams == 'ASC'){
        currentPage.value = 1;
        dateParams.value = '';
        proposalsPaginated.value.sort((a,b) => a.freelance_project_details.title.localeCompare(b.freelance_project_details.title));
    }

    if (newAlphabeticalParams == 'DESC'){
        currentPage.value = 1;
        dateParams.value = '';
        proposalsPaginated.value.sort((a,b) => b.freelance_project_details.title.localeCompare(a.freelance_project_details.title));
    }

    router.replace({
        query: {
            page: currentPage.value || undefined,
            q: queryParams.value || undefined,
        }
    })
});

const toggleSortDate = (type) => {
    dateParams.value = type;
}

const toggleSortAlphabetical = (type) => {
    alphabeticalParams.value = type;
}

const switchPage = (page) => {
    if (page >= 1 && page <= numberOfPage.value){
        endCurrentPage.value = page * itemsPerPage;
        startCurrentPage.value = endCurrentPage.value - itemsPerPage;
        currentPage.value = page;
    }
}

const toggleDropdown = (id) => {
    proposalId.value = proposalId.value === id ? null : id;
};

const fetchProposalsDebounced = debounce(fetchProposals,500);

const confirmAndProcess = async (index,type) => {
    switch(type){
        case 'Complete':
            savingMessage.value = 'Complete the project?';
            break;
        case 'Withdraw':
            savingMessage.value = 'Withdraw project proposal?';
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
  })

  if (result.isConfirmed) { 
    switch(type){
        case 'Complete':
            savingMessage.value = "Completing the project ...";
            break;
        case 'Withdraw':
            savingMessage.value = "Withdrawing project proposal ...";
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

    await updateProposalStatus(index,type);

    Swal.fire({ //swal result
      text: savingMessage.value,
      icon: isError.value == true ? 'error' : 'success',
      timer: 1500,
      showConfirmButton: false
    })
    
  }
}

async function fetchProposals(){
    try {
        const response = await api.get('/proposals/freelancer', 
            {
                params: {
                    query: queryParams.value || null,
                }
            }, 
            {withCredentials:true}
        );
        proposals.value = response.data.data;
        totalItems.value = proposals.value.length;
        numberOfPage.value = Math.max(1,Math.ceil(totalItems.value/itemsPerPage));
        if (route.query.page){
            switchPage(parseInt(route.query.page));
        }
        proposalsPaginated.value = proposals.value.slice(startCurrentPage.value,endCurrentPage.value);
    } catch (error) {
        console.error(error);
    }
}

async function updateProposalStatus(index,type){
    const formData = new FormData();
    formData.append('type',type);
    formData.append('_method', 'PUT');
    return new Promise((resolve) => {
        setTimeout(async() => {
            try {
                const response = await api.post(`/proposals/freelancer/${proposalId.value}`,
                    formData,
                    {withCredentials:true}
                );
                proposalsPaginated.value[index] = response.data.data;
                savingMessage.value = type == 'Withdraw' 
                        ? 'Project proposal has been withdrawn.'
                        : 'Project has been completed.';
            } catch (error){
                isError.value = true;
                savingMessage.value = "Error.";
                console.error(error);
            } finally {
                resolve();
                proposalId.value = null;
            }
        }, 2000);
    });

}
</script>

<template>
    <div v-if="isLoading" class="flex items-center justify-center gap-4 mt-60">
        <clip-loader color="#2b7fff"></clip-loader>
    </div>

    <div v-else-if="isFreelancer">
        <div class="flex items-center gap-4 my-4">
            <div class="flex-1">
                <div class="relative">
                    <InputBox class="w-full h-12 px-8" placeholder="Search..." v-model="queryParams"/>
                    <Search class="absolute right-4 top-3 cursor-pointer"></Search>
                </div>
            </div>
            <div class="flex-none">
                <button class="bg-green-500 cursor-pointer text-white h-12 rounded-xl 
                    hover:opacity-80 ml-auto flex items-center justify-center gap-2 my-4 p-4">
                    <Eraser/>
                    Clear filter
                </button>
            </div>
        </div>
        
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg min-h-96 max-h-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            <div class="flex items-center gap-1 ">
                                Date Applied
                                <ArrowDown v-if="dateParams == '' || dateParams == 'DESC'" 
                                    class="h-3 w-3 cursor-pointer" @click="toggleSortDate('ASC')"/>
                                <ArrowUp v-else class="h-3 w-3 cursor-pointer" @click="toggleSortDate('DESC')"/>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <div class="flex items-center gap-1 ">
                                Project Title
                                <ArrowDown v-if="alphabeticalParams == '' || alphabeticalParams == 'DESC'" 
                                    class="h-3 w-3 cursor-pointer" @click="toggleSortAlphabetical('ASC')"/>
                                <ArrowUp v-else class="h-3 w-3 cursor-pointer" @click="toggleSortAlphabetical('DESC')"/>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Application Status
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="proposalsPaginated.length > 0" v-for="(proposal,index) in proposalsPaginated" :key="proposal?.proposal_details.id"
                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td scope="row" class="p-6 font-medium text-gray-900 whitespace-nowrap dark:text-white w-2/12">
                            {{ moment(proposal?.proposal_details.created_at).format('MMM DD, YYYY') }}
                        </td>
                        <td class="p-6 w-4/12">
                            {{ proposal?.freelance_project_details.title }}
                        </td>
                        <td class="p-6 w-4/12">
                            <span class="text-white py-1 px-10 rounded-full"
                                :class="{
                                    'bg-blue-500' : proposal?.proposal_details.status == 'Pending',
                                    'bg-yellow-500' : proposal?.proposal_details.status == 'In Progress',
                                    'bg-red-500' : proposal?.proposal_details.status == 'Declined',
                                    'bg-gray-500' : proposal?.proposal_details.status == 'Withdrawn',
                                    'bg-green-500' : proposal?.proposal_details.status == 'Done'
                                }">
                                {{ proposal?.proposal_details.status }}
                            </span>
                        </td>
                        <td class="p-6 relative w-2/12">
                            <!-- Dropdown Button -->
                            <button 
                            @click="toggleDropdown(proposal?.proposal_details.id)" 
                            class="bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300 z-40 cursor-pointer"
                            >
                            Actions ▼
                            </button>

                            <!-- Dropdown Menu -->
                            <div 
                            v-show="proposalId === proposal?.proposal_details.id"
                            class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50"
                            >
                            <ul class="p-2 text-sm text-gray-700">
                                <router-link :to="{name:'freelance-details', params:{slug:proposal?.freelance_project_details?.slug}}">
                                    <li class="p-2 hover:bg-gray-100 cursor-pointer">
                                        View Project
                                    </li>
                                </router-link>
                                <li v-if="proposal?.proposal_details.status == 'In Progress'" @click="confirmAndProcess(index,'Complete')" class="p-2 hover:bg-gray-100 cursor-pointer text-blue-600">
                                    Complete Project
                                </li>
                                <li v-if="proposal?.proposal_details.status == 'Pending'" @click="confirmAndProcess(index,'Withdraw')" class="p-2 hover:bg-gray-100 cursor-pointer text-blue-600">
                                    Withdraw Proposal
                                </li>
                            </ul>
                            </div>
                        </td>
                    </tr>
                    <tr v-else>
                        <td colspan="4" class="text-center">No project proposals yet.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4 p-5" aria-label="Table navigation">
            <span class="text-sm font-normal text-gray-500 dark:text-gray-400 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                Showing <span class="font-semibold text-gray-900 dark:text-white">{{startCurrentPage+1}}-{{endCurrentPage}}</span>
                of 
                <span class="font-semibold text-gray-900 dark:text-white">{{ totalItems }}</span>
            </span>
            <ul v-if="proposalsPaginated.length > 0" class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                <li v-for="(page,index) in numberOfPage" :key="index" class="cursor-pointer">
                    <a @click="switchPage(page)" :class="{'bg-gray-300' : parseInt(currentPage) === page}"
                        class="flex items-center justify-center px-3 h-8 border border-gray-300 bg-blue-50 hover:opacity-70">
                        {{page}}
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <div v-else class="flex flex-col items-center justify-center gap-4 mt-60">
        <p class="text-4xl font-bold">Unauthorized page</p>
        <p class="text-gray-500">Click here to go back to <router-link class="text-blue-500" :to="{name:'home'}">Home</router-link> </p>
    </div>
</template>