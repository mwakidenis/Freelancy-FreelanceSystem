<script setup>
import { ref,onMounted } from 'vue';
import api from '../../../api';
import { useAuthStore } from '../../../authStore';
import { CircleUserRound,Pencil, Save, CircleX, CircleMinus, Plus} from 'lucide-vue-next';
import moment from 'moment';
import Swal from 'sweetalert2';

const authStore = useAuthStore();

const user = ref(null);
const objectToEdit = ref(null);
const isEditOpen = ref(false);

//initial value holders
const description = ref('');
const skills = ref (['']);
const educationalAttainment = ref(['']);
const workExperiences = ref(['']); 

const tempEditInput = ref(['']); //edit value holders for description, educational attainment, work experience, and array of skill
const skillInput = ref(''); //edit value for individual skill

//modal config
const savingMessage = ref('Save changes?');
const isError = ref(false);

onMounted(async () => {
    await fetchProfile(); 
});

const toggleModal = (edit) => {
    savingMessage.value = "Save changes?";
    isEditOpen.value = edit == 'close' ? false : true;
    objectToEdit.value = edit;

    if (edit == 'description'){
        tempEditInput.value = user.value.brief_description;
    } else if (edit == 'educational_attainment'){
        tempEditInput.value = user.value.educational_attainment ?? [];
    } else if (edit == 'work_experience'){
        tempEditInput.value = user.value.work_experience ?? [];
    } else if (edit == 'skills'){
        var temp = user.value.skills;
        if (temp) tempEditInput.value = JSON.parse(temp);
        else tempEditInput.value = []; 
    } else {
        tempEditInput.value = '';
    }
}

const addSkill = () => {
    tempEditInput.value.push(skillInput.value);
    skillInput.value = null;
}

const removeSkill = (index) => {
    tempEditInput.value.splice(index,1);
}

const addEducationalAttainmentInput = () => {
    tempEditInput.value.push({"university":"","program":"","year_graduated":null});   
}

const removeEducationalAttainmentInput = (index) => {
    tempEditInput.value.splice(index['index'],1);
}

const addWorkExperienceInput = () => {
    tempEditInput.value.push({"company":"","position":"","year_start":null,"year_end":null});   
}

const removeExperienceInput = (index) => {
    tempEditInput.value.splice(index['index'],1);
}

const confirmAndProcess = async () => {
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
    Swal.fire({ //swal loading
      text: "Saving changes ...",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading()
      }
    })

    await updateProfile();

    Swal.fire({ //swal result
      text: savingMessage.value,
      icon: isError.value == true ? 'error' : 'success',
      timer: 1500,
      showConfirmButton: false
    })
    
  }
}

async function fetchProfile(){
    try {
        const response = await api.get(`/profile/${authStore?.getUser?.username}`);
        user.value = response.data.data;

        description.value = response.data.data.brief_description;
        skills.value = JSON.parse(response.data.data.skills) ?? [];
        educationalAttainment.value = response.data.data.educational_attainment ?? [];
        workExperiences.value = response.data.data.work_experience ?? [];
    } catch (error){
        console.error(error);
    }
}

async function updateProfile() {
    const formData = new FormData();
    formData.append('type',objectToEdit.value);
    if (objectToEdit.value == 'description'){
        formData.append('tempEditInput',tempEditInput.value);
    } else {
        formData.append('tempEditInput',JSON.stringify(tempEditInput.value));
    }
    
    return new Promise((resolve) => {
        setTimeout(async() => {
            try {
                const response = await api.post(`profile/edit/update_profile`,formData,{withCredentials:true});
                savingMessage.value = "Successfully saved the changes!";
                switch (objectToEdit.value){
                    case "description":
                        tempEditInput.value = response.data.data;
                        description.value = response.data.data; 
                        user.value.brief_description = response.data.data;  
                        break;
                    case "educational_attainment":
                        tempEditInput.value = response.data.data;
                        educationalAttainment.value = response.data.data; 
                        user.value.educational_attainment = response.data.data;  
                        break;
                    case "work_experience":
                        tempEditInput.value = response.data.data;
                        workExperiences.value = response.data.data; 
                        user.value.work_experience = response.data.data;  
                        break;
                    case "skills":
                        tempEditInput.value = JSON.parse(response.data.data);
                        skills.value = JSON.parse(response.data.data); 
                        user.value.skills = response.data.data;  
                        break;
                    default:
                        break;
                }
                isError.value = false;
            } catch (error){
                savingMessage.value  = "Error";
                isError.value = true;
                console.error(error);
            } finally {
                resolve();
                isEditOpen.value = false;
                objectToEdit.value = null;
                tempEditInput.value = null;
                skillInput.value = null;
            }
        }, 2000);
    });
}
</script>

<template>
    <div class="w-full h-full flex flex-col justify-center mx-auto border-1 border-gray-300 my-12">
        <div class="grid grid-cols-12">
            <div class="md:col-span-2 col-span-12 md:border-r border-gray-300 flex flex-col gap-3 px-4 py-8 text-center">
                <div class="">
                    <CircleUserRound class="w-24 h-24 mx-auto"/>
                </div>
                
                <div class="font-bold">
                    <p class="text-xl">{{user?.name}}</p>
                    <p class="text-sm text-gray-500">@{{user?.username}}</p>
                </div>
                <div v-if="user?.user_type != 'admin'" class="">
                    <p class="text-sm font-bold text-gray-500">
                        <span v-if="user?.user_type =='freelancer'">
                            Number of proposals: {{ user?.number_of_freelances }}
                        </span>
                        <span v-else>
                            Number of projects: {{ user?.number_of_projects }}
                        </span>
                    </p>
                </div>
                <div class="">
                    <p class="text-xs text-gray-500">Member since {{ moment(user?.date_registered).format('ll') }}</p>
                </div> 
            </div>
            <div class="md:col-span-10 col-span-12">
                <div class="border-t md:border-t-0 border-b border-gray-300 flex flex-col gap-4 p-4">
                    <p class="font-bold text-xl flex items-center gap-2">
                        Description <span><Pencil class="w-3 cursor-pointer text-gray-500" @click="toggleModal('description')"/></span> 
                    </p>
                    <p class="text-sm text-gray-500">
                        {{ description ?? "No information yet." }}
                    </p>
                </div>
                <div class="border-b border-gray-300 flex flex-col gap-4 p-4">
                    <p class="font-bold text-xl flex items-center gap-2">
                        Skills <span><Pencil class="w-3 cursor-pointer text-gray-500" @click="toggleModal('skills')"/></span> 
                    </p>
                    <div v-if="skills.length > 0" class="flex flex-wrap gap-4">
                        <div v-for="(skill) in skills">
                            <div class="bg-blue-500 text-white min-w-32 max-w-auto h-8 rounded-xl
                                flex items-center justify-center text-sm text-center p-4">
                                {{ skill }}
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-500">No information yet.</div> 
                </div>
                <div class="border-b border-gray-300 flex flex-col gap-4 p-4">
                    <p class="font-bold text-xl flex items-center gap-2">
                        Educational attainment <span><Pencil class="w-3 cursor-pointer text-gray-500" @click="toggleModal('educational_attainment')"/></span>
                    </p>

                    <div v-if="educationalAttainment.length > 0" class="w-full h-32 mx-auto overflow-x-auto overflow-y-hidden">
                        <!-- Scrollable container -->
                        <div class="relative min-w-max h-full">
                            <!-- Horizontal line centered vertically -->
                            <div class="absolute top-1/2 left-0 w-full border-t border-gray-500 z-0"></div>

                            <!-- Scrollable flex content -->
                            <div class="relative flex gap-20 px-8 h-full z-10 text-center">
                                <div
                                    v-for="(education, index) in educationalAttainment"
                                    :key="index"
                                    class="min-w-32"
                                    :class="index % 2 === 0 ? 'flex flex-col mt-2' : 'flex flex-col-reverse mb-2'"
                                    >
                                    <span class="font-semibold text-sm">{{ education.university }}</span>
                                    <span class="text-gray-500 italic text-xs">{{ education.program }}</span>
                                    <span class="z-50 bg-blue-500 rounded-xl text-white text-sm">{{ education.year_graduated }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-sm text-gray-500">No information yet.</div>
                </div>
                <div class="flex flex-col gap-4 p-4">
                    <p class="font-bold text-xl flex items-center gap-2">
                        Work experience <span><Pencil class="w-3 cursor-pointer text-gray-500" @click="toggleModal('work_experience')"/></span>
                    </p>

                    <div v-if="workExperiences.length > 0" class="w-full h-32 mx-auto overflow-x-auto overflow-y-hidden">
                        <!-- Scrollable container -->
                        <div class="relative min-w-max h-full">
                            <!-- Horizontal line centered vertically -->
                            <div class="absolute top-1/2 left-0 w-full border-t border-gray-500 z-0"></div>

                            <!-- Scrollable flex content -->
                            <div class="relative flex gap-20 px-8 h-full z-10 text-center">
                                <div
                                    v-for="(work, index) in workExperiences"
                                    :key="index"
                                    class="min-w-32"
                                    :class="index % 2 === 0 ? 'flex flex-col-reverse mb-4' : 'flex flex-col mt-4'"
                                    >
                                    <span class="font-semibold text-sm">{{ work.company }}</span>
                                    <span class="text-gray-500 italic text-xs">{{ work.position }}</span>
                                    <span class="z-50 bg-blue-500 rounded-xl text-white text-sm">{{ work.year_start }} - {{ work.year_end }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-sm text-gray-500">No information yet.</div>
                </div>
            </div>

        </div>
    </div>

    <!-- EDIT MODAL -->
    <div v-if="isEditOpen == true">
        <div class="fixed inset-0 bg-black opacity-70 z-40"></div> 

        <div class="fixed inset-0 flex justify-center items-center z-40" @click.self="toggleModal('close')">
            <div class="bg-white md:w-3/5 w-4/5 h-4/5 p-6 rounded-lg shadow-lg flex flex-col gap-4 overflow-auto">
                <div class="flex flex-col flex-1 gap-4">
                    <p class="capitalize font-bold text-xl">Edit {{ objectToEdit.replace("_"," ") }}</p>
                    <hr/>
                    <div v-if="objectToEdit == 'description'">
                        <textarea
                        class="border border-gray-300 p-2 max-h-[400px] rounded w-full resize-none overflow-auto"
                        rows="14"
                        v-model="tempEditInput"
                        />
                    </div>
                    <div v-else-if="objectToEdit == 'skills'">
                        <div class="flex gap-4">
                            <div class="flex flex-1">
                                <input v-model="skillInput" type="text" class="border border-gray-300 rounded w-full h-8 mb-4"/>
                            </div>
                            <div class="w-12">
                                <button @click="addSkill()"
                                    class="border border-gray-300 text-gray-300 rounded flex items-center justify-center w-full h-8 cursor-pointer">
                                    <Plus/>
                                </button>
                            </div>
                        </div>
                        
                        <!-- v if check if skills array is empty or not -->
                        <div v-if="tempEditInput.length > 0" class="flex flex-wrap gap-4">
                            <!-- v for here -->
                            <div v-for="(skill,index) in tempEditInput" :key="index" 
                                class="bg-blue-500 text-white w-40 h-12 rounded-xl flex justify-center items-center text-center p-4"> 
                                <span class="mx-auto">{{tempEditInput[index]}}</span>
                                <span class="ml-auto"><CircleX class="w-4 h-4 cursor-pointer" @click="removeSkill(index)"/></span>
                            </div>
                        </div>
                        <!-- if empty display No skills yet.  -->
                         <div v-else class="text-gray-500 text-center">No skills yet.</div>
                    </div>
                    <div v-else-if="objectToEdit == 'educational_attainment'">
                        <div v-for="(work,index) in tempEditInput" :key="index" class="grid grid-cols-12 gap-4 text-center border-b border-gray-300 mb-1 py-6">
                            <div class="md:col-span-4 col-span-12 flex flex-col items-start gap-2">
                                <p class="font-medium">University</p>
                                <input type="text" v-model="tempEditInput[index].university" class="border border-gray-300 rounded w-full" />
                            </div>
                            <div class="md:col-span-4 col-span-6 flex flex-col items-start gap-2">
                                <p class="font-medium">Program</p>
                                <input type="text" v-model="tempEditInput[index].program" class="border border-gray-300 rounded w-full" />
                            </div>
                            <div class="md:col-span-3 col-span-3 flex flex-col items-start gap-2">
                                <p class="font-medium">Graduated</p>
                                <input type="number" v-model="tempEditInput[index].year_graduated" class="border border-gray-300 rounded w-full" />
                            </div>
                            <div class="md:col-span-1 col-span-3 flex items-center mt-7 justify-center">
                                <CircleMinus v-if="tempEditInput.length > 1" @click="removeEducationalAttainmentInput({index})" class="text-red-500 cursor-pointer hover:opacity-70"/>
                            </div>
                        </div>
                        <div class="w-full mt-8">
                            <button @click="addEducationalAttainmentInput"
                                class="border border-gray-300 text-gray-300 flex items-center justify-center w-full h-12 cursor-pointer">
                                <Plus/>
                            </button>
                        </div>
                    </div>
                    <div v-else-if="objectToEdit == 'work_experience'">
                        <div v-for="(work,index) in tempEditInput" :key="index" class="grid grid-cols-12 gap-4 text-center border-b border-gray-300 mb-1 py-6">
                            <div class="md:col-span-4 col-span-6 flex flex-col items-start gap-2">
                                <p class="font-medium">Company</p>
                                <input type="text" v-model="tempEditInput[index].company" class="border border-gray-300 rounded w-full" />
                            </div>
                            <div class="md:col-span-3 col-span-6 flex flex-col items-start gap-2">
                                <p class="font-medium">Position</p>
                                <input type="text" v-model="tempEditInput[index].position" class="border border-gray-300 rounded w-full" />
                            </div>
                            <div class="md:col-span-2 col-span-5 flex flex-col items-start gap-2">
                                <p class="font-medium">Year started</p>
                                <input type="number" v-model="tempEditInput[index].year_start" class="border border-gray-300 rounded w-full" />
                            </div>
                            <div class="md:col-span-2 col-span-5 flex flex-col items-start gap-2">
                                <p class="font-medium">Year ended</p>
                                <input type="number" v-model="tempEditInput[index].year_end" class="border border-gray-300 rounded w-full" />
                            </div>
                            <div class="md:col-span-1 col-span-2 flex items-center mt-7 justify-center">
                                <CircleMinus v-if="tempEditInput.length > 1" @click="removeExperienceInput({index})" class="text-red-500 cursor-pointer hover:opacity-70"/>
                            </div>
                        </div>
                        <div class="w-full mt-8">
                            <button @click="addWorkExperienceInput"
                                class="border border-gray-300 text-gray-300 flex items-center justify-center w-full h-12 cursor-pointer">
                                <Plus/>
                            </button>
                        </div>
                    </div>
                    <div v-else>
                        Error
                    </div>
                </div>

                <div class="ml-auto flex gap-4  relative fixed bottom-0 ">
                    <button @click="confirmAndProcess()"
                        class="bg-green-500 cursor-pointer text-white w-24 h-12 rounded-xl hover:opacity-80 ml-auto
                            flex items-center justify-center gap-2">
                        <span><Save class="w-4 h-4"/></span> Save
                    </button>
                    <button @click="toggleModal('close')"
                        class="bg-red-500 cursor-pointer text-white w-24 h-12 rounded-xl hover:opacity-80 ml-auto
                            flex items-center justify-center gap-2">
                            <span><CircleX class="w-4 h-4"/></span> Close  
                    </button>
                </div>
            </div>
        </div>
    </div>
</template> 