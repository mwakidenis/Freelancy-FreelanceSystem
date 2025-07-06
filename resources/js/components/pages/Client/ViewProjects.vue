<script setup>
import { ref,onMounted,watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../../authStore';
import { debounce } from 'lodash';
import api from '../../../api';
import {Plus,Save,CircleX,Search,ArrowUp,ArrowDown,Eraser } from 'lucide-vue-next';
import InputBox from '../../component/InputBox.vue';
import Swal from 'sweetalert2';
import { ClipLoader } from 'vue-spinner/dist/vue-spinner.min.js';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const freelances = ref([]);
const freelancesPaginated = ref([]);

const isLoading = ref(true);

//for dropdown
const openDropdownId = ref(null);

//form edit variables
const objectToEdit = ref();
const mainArrayIndexFreelanceIndex = ref();
const freelanceIndexToEdit = ref();

//form create variables
const title = ref();
const description = ref();
const category = ref();
const rate = ref();

//for modal
const isModalOpen = ref(false);
const actionToDo = ref(false);

//for confirmation modal
const savingMessage = ref(null);
const isError = ref(false);

//for pagination
const totalItems = ref();
const itemsPerPage = 10;
const numberOfPage = ref();
const currentPage = ref(parseInt(route.query.page)  || 1); //add url tracking
const startCurrentPage = ref(0);
const endCurrentPage = ref(itemsPerPage);

//for search
const queryParams = ref(route.query.q || undefined);
const dateParams = ref('');
const alphabeticalParams = ref('');

onMounted(async () => {
    if (authStore.isAuthenticated && authStore.getUser.user_type == 'client'){
        await fetchFreelances();
    }
    isLoading.value = false;
});

watch([currentPage,queryParams,dateParams,alphabeticalParams], 
    ([newPage,newQuery,newDate,newAlphabet] , [oldPage,oldQuery,oldDate,oldAlphabet]) => {
    //make statements if > else if 
    if (newPage !== oldPage) {
        freelancesPaginated.value = freelances.value.slice(startCurrentPage.value,endCurrentPage.value);
    }

    if (newQuery !== oldQuery) {
        currentPage.value = 1;
        alphabeticalParams.value = '';
        dateParams.value = '';
        fetchFreelancesDebounced();
    }

    if (newDate !== oldDate) {
        currentPage.value = 1;
        alphabeticalParams.value = ''; 
        freelancesPaginated.value.reverse();
    }

    if (newAlphabet == 'ASC') {
        currentPage.value = 1;
        dateParams.value = '';
        freelancesPaginated.value.sort((a,b) => a.title.localeCompare(b.title));
    } 
    
    if (newAlphabet == 'DESC') {
        currentPage.value = 1;
        dateParams.value = '';
        freelancesPaginated.value.sort((a,b) => b.title.localeCompare(a.title));
    }

    router.replace({
        query:{
            page: currentPage.value || undefined,
            q: queryParams.value || undefined,
        }
    })
});

watch(freelanceIndexToEdit, () => { //watches the freelance object and re-assigns value to v-model
    if (freelanceIndexToEdit.value != null){
        mainArrayIndexFreelanceIndex.value = freelanceIndexToEdit.value + ((currentPage.value - 1) * itemsPerPage);
        objectToEdit.value = freelancesPaginated.value[freelanceIndexToEdit.value];
        title.value = objectToEdit.value.title;
        description.value = objectToEdit.value.description;
        category.value = objectToEdit.value.category;
        rate.value = objectToEdit.value.rate;
    }
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

const clearFilter = () => {
    currentPage.value = 1;
    queryParams.value = undefined;
    dateParams.value = '';
    alphabeticalParams.value = '';

    switchPage(1);

    router.replace({
        query:{
            page: undefined,
            q: undefined,
        }
    })
}

// Function to toggle dropdown for each row
const toggleDropdown = (id) => {
  openDropdownId.value = openDropdownId.value === id ? null : id;
};

// Function to handle dropdown actions
const handleAction = (action, value) => {
    switch(action){
        case 'View':
            openDropdownId.value = null;
            router.push({
                name:'freelance-details',
                params: {slug:value}
            });
            break;
        case 'Edit':
            openDropdownId.value = null;
            freelanceIndexToEdit.value = value;
            toggleProjectModal("Edit");
            break;
        case 'Status':
            openDropdownId.value = null;
            freelanceIndexToEdit.value = value;
            confirmAndProcess(action);
            break;
        case 'default':
            openDropdownId.value = null;
            break;
    }
};

const toggleProjectModal = (action) => {
    switch(action){
        case 'Create':
            isModalOpen.value = true;
            actionToDo.value = "Create";
            break;
        case 'Edit':
            isModalOpen.value = true;
            actionToDo.value = 'Edit';
            break;
        case 'Close':
            isModalOpen.value = false;
            actionToDo.value = null;
            emptyEditInput();
            break;
        default:
            isModalOpen.value = false;
            actionToDo.value = null;
            emptyEditInput();
            break;
    }
}

const confirmAndProcess = async (type) => {
    switch(type){
        case 'Create':
            savingMessage.value = 'Create a project?';
            break;
        case 'Edit':
            savingMessage.value = 'Update project information?';
            break;
        case 'Status':
            savingMessage.value = 'Update project status?';
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
        case 'Create':
            savingMessage.value = "Creating a project ...";
            break;
        case 'Edit':
            savingMessage.value = "Updating project information ...";
            break;
        case 'Status':
            savingMessage.value = 'Updating project status ...';
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

    switch(type){
        case 'Create':
            await createNewFreelance();
            toggleProjectModal('Close');
            break;
        case 'Edit':
            await updateInfoFreelance();
            toggleProjectModal('Close');
            break;
        case 'Status':
            await updateStatusFreelance();
            break;
        default:
            break;
    }

    Swal.fire({ //swal result
      text: savingMessage.value,
      icon: isError.value == true ? 'error' : 'success',
      timer: 1500,
      showConfirmButton: false
    })
    
  }
}

function emptyEditInput(){ //empty the freelance object and nulls the form values
    objectToEdit.value = null;
    freelanceIndexToEdit.value = null;
    mainArrayIndexFreelanceIndex.value = null;
    title.value = null;
    description.value = null;
    category.value = null;
    rate.value = null;
}

const fetchFreelancesDebounced = debounce(fetchFreelances, 500);

async function fetchFreelances(){
    try{
        const response = await api.get('/freelances/client',
        {
            params: {
                query: queryParams.value || null,
            }
        },
        {
            withCredentials:true
        });
        freelances.value = response.data.data|| [];
        totalItems.value = freelances.value.length || 0;
        numberOfPage.value = Math.max(1, Math.ceil(totalItems.value / itemsPerPage));
        if (route.query.page) {
            switchPage(parseInt(route.query.page));
        } 
        freelancesPaginated.value = freelances.value.length > 0 ? freelances.value.slice(startCurrentPage.value,endCurrentPage.value) : [] ;
    } catch (error){
        console.error(error);
    }
}

async function createNewFreelance() {
    const formData = new FormData();
    formData.append('title', title.value);
    formData.append('description', description.value);
    formData.append('category', category.value);
    formData.append('rate', rate.value);

    return new Promise((resolve) => {
        setTimeout(async () => {
            try {
                const response = await api.post('/freelances/client', formData, { withCredentials: true });
                freelances.value.unshift(response.data.data);
                freelancesPaginated.value.unshift(response.data.data);
                savingMessage.value = "Successfully created a project!";
                isError.value = false;
            } catch (error) {
                savingMessage.value = "Error";
                isError.value = true;
                console.error(error);
            } finally {
                emptyEditInput();
                resolve(); // resolves the promise so it can be awaited
            }
        }, 2000);
    });
}

async function updateInfoFreelance() {
    const formData = new FormData();
    formData.append('title', title.value);
    formData.append('description', description.value);
    formData.append('category', category.value);
    formData.append('rate', rate.value);
    formData.append('type', 'information');
    formData.append('_method', 'PUT');

    return new Promise((resolve) => {
        setTimeout(async () => {
            try {
                const response = await api.post(`freelances/client/${objectToEdit.value.slug}`, formData, { withCredentials: true });
                const updatedObject = response.data.data;
                title.value = updatedObject.title;
                description.value = updatedObject.description;
                category.value = updatedObject.category;
                rate.value = updatedObject.rate;
                freelances.value[mainArrayIndexFreelanceIndex.value] = updatedObject;
                freelancesPaginated.value[freelanceIndexToEdit.value] = updatedObject;
                savingMessage.value = "Successfully updated project information!";
                isError.value = false;
            } catch (error) {
                savingMessage.value = "Error";
                isError.value = true;
                console.error(error);
            } finally {
                emptyEditInput();
                resolve(); // resolves the promise so it can be awaited
            }
        }, 2000);
    });
}

async function updateStatusFreelance() {
    const formData = new FormData();
    formData.append('type', 'status');
    formData.append('_method', 'PUT');

    return new Promise((resolve) => {
        setTimeout(async () => {
            try {
                const response = await api.post(`freelances/client/${objectToEdit.value.slug}`, formData, { withCredentials: true });
                const updatedObject = response.data.data;
                freelances.value[mainArrayIndexFreelanceIndex.value] = updatedObject;
                freelancesPaginated.value[freelanceIndexToEdit.value] = updatedObject;
                savingMessage.value = "Successfully updated project status!";
                isError.value = false;
            } catch (error) {
                savingMessage.value = "Error";
                isError.value = true;
                console.error(error);
            } finally {
                emptyEditInput();
                resolve();
            }
        }, 2000);
    });
}
</script>

<template>
    <div v-if="isLoading" class="flex items-center justify-center gap-4 mt-60">
        <clip-loader color="#2b7fff"></clip-loader>
    </div>

    <div v-else-if="authStore.isAuthenticated && authStore.getUser.user_type == 'client'">
        <div class="flex items-center gap-4 my-4">
            <div class="flex-1">
                <div class="relative">
                    <InputBox class="w-full h-12 px-8" placeholder="Search..." v-model="queryParams"/>
                    <Search class="absolute right-4 top-3 cursor-pointer"></Search>
                </div>
            </div>
            <div class="flex-none">
                <button @click="clearFilter()" class="text-xs bg-red-500 cursor-pointer text-white h-12 rounded-xl 
                    hover:opacity-80 ml-auto flex items-center justify-center gap-2 my-4 p-4">
                    <Eraser class="h-4 w-4"/>
                    Clear filter
                </button>
            </div>
        </div>

        <div class="ml-auto my-12">
            <button @click="toggleProjectModal('Create')" class="text-xs bg-blue-500 cursor-pointer text-white h-10 rounded-xl 
                hover:opacity-80 ml-auto flex items-center justify-center gap-2 my-4 p-4">
                <Plus class="h-4 w-4"/>
                Add new project
            </button>
        </div>
        
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg h-auto">
            <table class="w-full h-auto text-sm text-left rtl:text-right text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            <div class="flex items-center gap-1 ">
                                Date Posted
                                <ArrowDown v-if="dateParams == '' || dateParams == 'DESC'" 
                                    class="h-3 w-3 cursor-pointer" @click="toggleSortDate('ASC')"/>
                                <ArrowUp v-else class="h-3 w-3 cursor-pointer" @click="toggleSortDate('DESC')"/>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <div class="flex items-center gap-1 ">
                                Title
                                <ArrowDown v-if="alphabeticalParams == '' || alphabeticalParams == 'DESC'" 
                                    class="h-3 w-3 cursor-pointer" @click="toggleSortAlphabetical('ASC')"/>
                                <ArrowUp v-else class="h-3 w-3 cursor-pointer" @click="toggleSortAlphabetical('DESC')"/>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="freelancesPaginated.length > 0" v-for="(freelance,index) in freelancesPaginated" :key="freelance?.id"
                        class="text-xs bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <th scope="row" class="px-6 py-4">
                            {{new Date(freelance?.date_posted).toLocaleDateString("en-US", { year:'numeric',month:'long',day:'numeric' })}}
                        </th>
                        <td class="px-6 py-4">
                            <p class="font-bold">{{ freelance?.title }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-white py-1 px-10 rounded-full"
                                :class="{
                                    'bg-blue-500' : freelance?.status == 'Active',
                                    'bg-yellow-500' : freelance?.status == 'In Progress',
                                    'bg-gray-500' : freelance?.status == 'Inactive',
                                    'bg-green-500' : freelance?.status == 'Done'
                                }">
                                {{ freelance?.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 relative">
                            <!-- Dropdown Button -->
                            <button 
                            @click="toggleDropdown(freelance?.id)" 
                            class="bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300 z-40 cursor-pointer"
                            >
                            Actions ▼
                            </button>

                            <!-- Dropdown Menu -->
                            <div 
                            v-show="openDropdownId === freelance?.id"
                            class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50"
                            >
                            <ul class="p-2 text-sm text-gray-700">
                                <li @click="handleAction('View', freelance?.slug)" class="p-2 hover:bg-gray-100 cursor-pointer">
                                    View
                                </li>
                                <li v-if="freelance?.status == 'Active' || freelance?.status == 'Inactive'" @click="handleAction('Edit', index)" class="p-2 hover:bg-gray-100 cursor-pointer">
                                    Edit
                                </li>
                                <li v-if="freelance?.status == 'Active' || freelance?.status == 'Inactive'" @click="handleAction('Status', index)" class="p-2 hover:bg-gray-100 cursor-pointer text-blue-600">
                                    Update Status
                                </li>
                            </ul>
                            </div>
                        </td>
                    </tr>
                    <tr v-else>
                        <td colspan="5" class="text-center">No projects yet.</td>
                    </tr>
                </tbody>
            </table>
            <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4 p-5" aria-label="Table navigation">
                <span class="text-sm font-normal text-gray-500 dark:text-gray-400 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                    Showing <span class="font-semibold text-gray-900 dark:text-white">{{startCurrentPage+1}}-{{endCurrentPage}}</span>
                    of 
                    <span class="font-semibold text-gray-900 dark:text-white">{{ totalItems }}</span>
                </span>
                <ul v-if="freelancesPaginated.length > 0" class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                    <li v-for="(page,index) in numberOfPage" :key="index" class="cursor-pointer">
                        <a @click="switchPage(page)" :class="{'bg-gray-300' : parseInt(currentPage) === page}"
                            class="flex items-center justify-center px-3 h-8 border border-gray-300 bg-blue-50 hover:opacity-70">
                            {{page}}
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- ADD AND EDIT MODAL -->
        <div v-if="isModalOpen && actionToDo">
            <div class="fixed inset-0 bg-black opacity-70 z-40"></div> 
            <div class="fixed inset-0 flex justify-center items-center z-40" @click.self="toggleProjectModal('Close')">
                <div class="bg-white w-4/5 h-10/11 p-6 rounded-lg shadow-lg flex flex-col overflow-auto">
                    <p class="font-bold text-xl">{{actionToDo}} Freelance project</p>
                    <hr class="my-4"/>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold">Title</label>
                        <input v-model="title" class="border border-gray-300 rounded w-full h-8 mb-4"/>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold">Category</label>
                        <input v-model="category" class="border border-gray-300 rounded w-full h-8 mb-4"/>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold">Rate</label>
                        <input v-model="rate" class="border border-gray-300 rounded w-full h-8 mb-4"/>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold">Description</label>
                        <textarea v-model="description" rows="10" class="border border-gray-300 rounded w-full mb-4"/>
                    </div>
                    <div class="ml-auto flex gap-4  relative fixed bottom-0 ">
                        <button @click="confirmAndProcess(actionToDo)"
                            class="bg-green-500 cursor-pointer text-white w-24 h-12 rounded-xl hover:opacity-80 ml-auto
                                flex items-center justify-center gap-2">
                            <span><Save class="w-4 h-4"/></span> {{ actionToDo }}
                        </button>
                        <button @click="toggleProjectModal('Close')"             
                            class="bg-red-500 cursor-pointer text-white w-24 h-12 rounded-xl hover:opacity-80 ml-auto
                                flex items-center justify-center gap-2">
                                <span><CircleX class="w-4 h-4"/></span> Close  
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div v-else class="flex flex-col items-center justify-center gap-4 mt-60">
        <p class="text-4xl font-bold">Unauthorized page</p>
        <p class="text-gray-500">Click here to go back to <router-link class="text-blue-500" :to="{name:'home'}">Home</router-link> </p>
    </div>

</template>