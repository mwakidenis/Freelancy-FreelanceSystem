<script setup>
import { ref,onMounted,watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../../authStore';
import api from '../../../api';
import {Plus,Save,CircleX } from 'lucide-vue-next';
import Swal from 'sweetalert2';

const router = useRouter();
const authStore = useAuthStore(); //to implement
const isClient = ref(false); //to implement
const freelances = ref([]);

//for dropdown
const openDropdownId = ref(null);

//form edit variables
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

onMounted(async () => {
    // if (authStore.isAuthenticated && authStore.isClient){
    //     isClient.value = true;
    //     fetchFreelances();
    // }
    await fetchFreelances();
});

watch(freelanceIndexToEdit, () => { //watches the freelance object and re-assigns value to v-model
    if (freelanceIndexToEdit.value != null){
        const objectToEdit = freelances.value[freelanceIndexToEdit.value];
        title.value = objectToEdit.title;
        description.value = objectToEdit.description;
        category.value = objectToEdit.category;
        rate.value = objectToEdit.rate;
    }
});

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
                name:'client-view-single-project',
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
    freelanceIndexToEdit.value = null;
    title.value = null;
    description.value = null;
    category.value = null;
    rate.value = null;
}

async function fetchFreelances(){
    try{
        const response = await api.get('/freelances/client',{
            withCredentials:true
        });
        freelances.value = response.data.data|| [];
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
            savingMessage.value = "Successfully created a project!";
            isError.value = false;
            } catch (error) {
            savingMessage.value = "Error";
            isError.value = true;
            console.error(error);
            } finally {
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

    const objectToEdit = freelances.value[freelanceIndexToEdit.value];

    return new Promise((resolve) => {
        setTimeout(async () => {
            try {
            const response = await api.post(`freelances/client/${objectToEdit.slug}`, formData, { withCredentials: true });
            const updatedObject = response.data.data;
            title.value = updatedObject.title;
            description.value = updatedObject.description;
            category.value = updatedObject.category;
            rate.value = updatedObject.rate;
            freelances.value[freelanceIndexToEdit.value] = updatedObject;
            savingMessage.value = "Successfully updated project information!";
            isError.value = false;
            } catch (error) {
            savingMessage.value = "Error";
            isError.value = true;
            console.error(error);
            } finally {
            resolve(); // resolves the promise so it can be awaited
            }
        }, 2000);
    });
}

async function updateStatusFreelance() {
    const formData = new FormData();
    formData.append('type', 'status');
    formData.append('_method', 'PUT');

    const objectToEdit = freelances.value[freelanceIndexToEdit.value];

    return new Promise((resolve) => {
        setTimeout(async () => {
            try {
            const response = await api.post(`freelances/client/${objectToEdit.slug}`, formData, { withCredentials: true });
            const updatedObject = response.data.data;
            freelances.value[freelanceIndexToEdit.value] = updatedObject;
            savingMessage.value = "Successfully updated project status!";
            isError.value = false;
            } catch (error) {
            savingMessage.value = "Error";
            isError.value = true;
            console.error(error);
            } finally {
            resolve();
            }
        }, 2000);
    });
}
</script>

<template>

    <button @click="toggleProjectModal('Create')" class="bg-blue-500 cursor-pointer text-white h-12 rounded-xl 
        hover:opacity-80 ml-auto flex items-center justify-center gap-2 my-4 p-4">
        <Plus/>
        Add new project
    </button>
    
    <!-- <button id="dropdownRadioButton" data-dropdown-toggle="dropdownRadio" class="w-40 inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700" type="button">
        <svg class="w-3 h-3 text-gray-500 dark:text-gray-400 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm3.982 13.982a1 1 0 0 1-1.414 0l-3.274-3.274A1.012 1.012 0 0 1 9 10V6a1 1 0 0 1 2 0v3.586l2.982 2.982a1 1 0 0 1 0 1.414Z"/>
            </svg>
        Last 30 days
        <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
        </svg>
    </button>

    <div id="dropdownRadio" class="z-10 hidden w-48 bg-white divide-y divide-gray-100 rounded-lg shadow-sm dark:bg-gray-700 dark:divide-gray-600" data-popper-reference-hidden="" data-popper-escaped="" data-popper-placement="top" style="position: absolute; inset: auto auto 0px 0px; margin: 0px; transform: translate3d(522.5px, 3847.5px, 0px);">
        <ul class="p-3 space-y-1 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownRadioButton">
            <li>
                <div class="flex items-center p-2 rounded-sm hover:bg-gray-100 dark:hover:bg-gray-600">
                    <input id="filter-radio-example-1" type="radio" value="" name="filter-radio" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="filter-radio-example-1" class="w-full ms-2 text-sm font-medium text-gray-900 rounded-sm dark:text-gray-300">Last day</label>
                </div>
            </li>
            <li>
                <div class="flex items-center p-2 rounded-sm hover:bg-gray-100 dark:hover:bg-gray-600">
                    <input checked="" id="filter-radio-example-2" type="radio" value="" name="filter-radio" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="filter-radio-example-2" class="w-full ms-2 text-sm font-medium text-gray-900 rounded-sm dark:text-gray-300">Last 7 days</label>
                </div>
            </li>
            <li>
                <div class="flex items-center p-2 rounded-sm hover:bg-gray-100 dark:hover:bg-gray-600">
                    <input id="filter-radio-example-3" type="radio" value="" name="filter-radio" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="filter-radio-example-3" class="w-full ms-2 text-sm font-medium text-gray-900 rounded-sm dark:text-gray-300">Last 30 days</label>
                </div>
            </li>
            <li>
                <div class="flex items-center p-2 rounded-sm hover:bg-gray-100 dark:hover:bg-gray-600">
                    <input id="filter-radio-example-4" type="radio" value="" name="filter-radio" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="filter-radio-example-4" class="w-full ms-2 text-sm font-medium text-gray-900 rounded-sm dark:text-gray-300">Last month</label>
                </div>
            </li>
            <li>
                <div class="flex items-center p-2 rounded-sm hover:bg-gray-100 dark:hover:bg-gray-600">
                    <input id="filter-radio-example-5" type="radio" value="" name="filter-radio" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="filter-radio-example-5" class="w-full ms-2 text-sm font-medium text-gray-900 rounded-sm dark:text-gray-300">Last year</label>
                </div>
            </li>
        </ul>
    </div> -->
    <br><br>

    <div class="relative overflow-x-auto shadow-md sm:rounded-lg min-h-screen">
        <table class="w-full min-h-96 text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        Date Posted
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Title
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Number of proposals
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
                <!-- ADD V IF LENGTH -->
                <tr v-if="freelances.length > 0" v-for="(freelance,index) in freelances" :key="freelance?.id"
                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        {{new Date(freelance?.date_posted).toLocaleDateString("en-US", { year:'numeric',month:'long',day:'numeric' })}}
                    </th>
                    <td class="px-6 py-4">
                        {{ freelance?.title }}
                    </td>
                    <td class="px-6 py-4 flex flex-col">
                        <p>Total: {{ freelance?.number_of_total_proposals }}</p>
                        <p>Pending: {{ freelance?.number_of_pending_proposals }}</p>
                        <p>Declined: {{ freelance?.number_of_declined_proposals }}</p>
                    </td>
                    <td class="px-6 py-4">
                        {{ freelance?.status }}
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
                            <li @click="handleAction('Edit', index)" class="p-2 hover:bg-gray-100 cursor-pointer">
                            Edit
                            </li>
                            <li @click="handleAction('Status', index)" class="p-2 hover:bg-gray-100 cursor-pointer text-blue-600">
                            Update Status
                            </li>
                        </ul>
                        </div>
                    </td>
                </tr>
                <tr v-else>
                    <td colspan="5" class="text-center">No projects yet.</td>
                </tr>
                <!-- ADD V ELSE LENGTH -->
            </tbody>
        </table>
        <!-- <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4 p-5" aria-label="Table navigation">
            <span class="text-sm font-normal text-gray-500 dark:text-gray-400 mb-4 md:mb-0 block w-full md:inline md:w-auto">Showing <span class="font-semibold text-gray-900 dark:text-white">1-10</span> of <span class="font-semibold text-gray-900 dark:text-white">1000</span></span>
            <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                <li>
                    <a href="#" class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Previous</a>
                </li>
                <li>
                    <a href="#" class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">1</a>
                </li>
                <li>
                    <a href="#" class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">2</a>
                </li>
                <li>
                    <a href="#" aria-current="page" class="flex items-center justify-center px-3 h-8 text-blue-600 border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white">3</a>
                </li>
                <li>
                    <a href="#" class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">4</a>
                </li>
                <li>
                    <a href="#" class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">5</a>
                </li>
                <li>
                        <a href="#" class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Next</a>
                </li>
            </ul>
        </nav> -->
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
</template>