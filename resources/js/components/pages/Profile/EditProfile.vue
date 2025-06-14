<script setup>
import { ref,onMounted,watch } from 'vue';
import api from '../../../api';
import { useAuthStore } from '../../../authStore';
import { CircleUserRound,Pencil, Save, CircleX, CircleMinus, Plus} from 'lucide-vue-next';
import moment from 'moment';

const authStore = useAuthStore();

const user = ref(null);
const objectToEdit = ref(null);
const objectInput = ref(''); 
const objectInputRef = ref(null);
const isEditOpen = ref(false);

//initial value holders
const description = ref('');
const skills = ref (['']);
const educationalAttainment = ref(['']);
const workExperiences = ref(['']); 

const tempEditInput = ref(['']); //edit value holders

onMounted(async () => {
    //message input box
    // autoResize();
    await fetchProfile(); 
});

// onUnmounted(() => {
//     window.removeEventListener('keydown', escapeKeyEvent)
// });

// watch(() => workExperiences.value.length,
//         (length) => {
//     console.log("work experiences input value: ", workExperienceInput.value);
//     if (length) {
//         console.log("work experiences length updated");4
//     }
// });


function autoResize() {
  const el = objectInputRef.value
  el.style.height = 'auto'
  
  const newHeight = el.scrollHeight
  const maxHeight = 400
  
  el.style.height = Math.min(newHeight, maxHeight) + 'px'
}

const toggleModal = (edit) => {
    isEditOpen.value = edit == 'close' ? false : true;
    objectToEdit.value = edit;

    if (edit == 'work experience'){
        var temp = user.value.work_experience;
        tempEditInput.value = JSON.parse(temp);
    } 
    if (edit == 'close'){
        tempEditInput.value = '';
    }
}

const saveChanges = async (edit) => {
    //REFACTOR JUST A SINGLE METHOD AND API CALL WITHOUT MULTIPLE SWITCH CASE STATEMENTS
    switch(edit){
        case "work experience":
            const formData = new FormData();
            formData.append('work_experience',JSON.stringify(tempEditInput.value));
            try{
            const response = await api.post("/profile/edit/work-experience", formData, {withCredentials:true});
            tempEditInput.value = JSON.parse(response.data.data); //for updating the value in modal
            workExperiences.value = JSON.parse(response.data.data);  //for updating the values outside
            user.value.work_experience = response.data.data; //for updating the profile value 
            } catch (error){
                console.error();
            }
            break;
        default:
            break;
    }
}

const addWorkExperienceInput = () => {
    tempEditInput.value.push({"company":"","position":"","year_start":null,"year_end":null});   
}

const removeExperienceInput = (index) => {
    tempEditInput.value.splice(index['index'],1);
}

async function fetchProfile(){
    try {
        const response = await api.get(`/profile/${authStore?.getUser?.username}`);
        user.value = response.data.data;

        description.value = response.data.data.brief_description;
        skills.value = response.data.data.skills;
        educationalAttainment.value = response.data.data.educational_attainment;
        workExperiences.value = JSON.parse(response.data.data.work_experience);
    } catch (error){
        console.error(error);
    }
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
                    <p class="text-sm text-gray-500">
                        {{ skills ?? "No information yet." }}
                    </p>  
                </div>
                <div class="border-b border-gray-300 flex flex-col gap-4 p-4">
                    <p class="font-bold text-xl flex items-center gap-2">
                        Educational attainment <span><Pencil class="w-3 cursor-pointer text-gray-500" @click="toggleModal('educational attainment')"/></span>
                    </p>
                    <p class="text-sm text-gray-500">
                        {{ educationalAttainment ?? "No information yet." }}
                    </p>  
                </div>
                <div class="flex flex-col gap-4 p-4">
                    <p class="font-bold text-xl flex items-center gap-2">
                        Work experience <span><Pencil class="w-3 cursor-pointer text-gray-500" @click="toggleModal('work experience')"/></span>
                    </p>
                    <div v-if="workExperiences.length > 0" class="text-sm text-gray-500">
                        <div v-for="(work) in workExperiences">
                            <span>{{ work.company }}</span> - <span>{{ work.position }}</span> ({{ work.year_start }} - {{ work.year_end }})
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-500">nahwp</div>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL -->
    <div v-if="isEditOpen == true">
        <div class="fixed inset-0 bg-black opacity-70 z-50"></div> 

        <div class="fixed inset-0 flex justify-center items-center z-50" @click.self="toggleModal('close')">
            <div class="bg-white w-3/5 h-4/5 p-6 rounded-lg shadow-lg flex flex-col gap-4 overflow-auto">
                <div class="flex flex-col flex-1 gap-4">
                    <p class="capitalize font-bold text-xl">{{ objectToEdit }}</p>
                    <!-- <input type="text" -->
                    <hr/>
                    <div v-if="objectToEdit == 'description'">
                        <textarea
                        class="border border-gray-300 p-2 max-h-[400px] rounded w-full resize-none overflow-auto"
                        rows="10"
                        @input="autoResize"
                        ref="objectInputRef"
                        v-model="objectInput"
                        />
                    </div>
                    <div v-else-if="objectToEdit == 'skills'">
                        <input type="text" class="border border-gray-300 rounded w-full h-8 mb-4"/>
                        <div class="flex flex-wrap gap-4">
                            <div class="bg-blue-500 text-white w-32 h-12 rounded-xl flex justify-center items-center p-4">
                                <span class="mx-auto">Skill 1</span>
                                <span class="ml-auto"><CircleX class="w-4 h-4 cursor-pointer"/></span>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="objectToEdit == 'educational attainment'">
                        <div class="flex justify-evenly gap-20">
                            <div class="flex flex-col w-full gap-2">
                                <p>University</p>
                                <input type="text" class="border border-gray-300 rounded"/>
                            </div>
                            <div class="flex flex-col w-full gap-2">
                                <p>Program</p>
                                <input type="text" class="border border-gray-300 rounded"/>
                            </div>
                            <div class="flex flex-col w-full gap-2">
                                <p>Year graduated</p>
                                <input type="text" class="border border-gray-300 rounded"/>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="objectToEdit == 'work experience'">
                        <div v-for="(work,index) in tempEditInput" :key="index" class="grid grid-cols-12 gap-4 text-center my-2">
                            <div class="col-span-4 flex flex-col items-start gap-2">
                                <p class="font-medium">Company</p>
                                <input type="text" v-model="tempEditInput[index].company" class="border border-gray-300 rounded w-full" />
                            </div>
                            <div class="col-span-3 flex flex-col items-start gap-2">
                                <p class="font-medium">Position</p>
                                <input type="text" v-model="tempEditInput[index].position" class="border border-gray-300 rounded w-full" />
                            </div>
                            <div class="col-span-2 flex flex-col items-start gap-2">
                                <p class="font-medium">Year started</p>
                                <input type="number" v-model="tempEditInput[index].year_start" class="border border-gray-300 rounded w-full" />
                            </div>
                            <div class="col-span-2 flex flex-col items-start gap-2">
                                <p class="font-medium">Year ended</p>
                                <input type="number" v-model="tempEditInput[index].year_end" class="border border-gray-300 rounded w-full" />
                            </div>
                            <div class="col-span-1 flex items-center mt-7 justify-center">
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
                    <button @click="saveChanges(objectToEdit)"
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