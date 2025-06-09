<script setup>
import { ref,onMounted,onUnmounted } from 'vue';
import { useAuthStore } from '../../../authStore';
import { CircleUserRound,Pencil, Save, CircleX, CircleMinus, Plus} from 'lucide-vue-next';
import moment from 'moment';

const authStore = useAuthStore();

const user = ref(null);
const objectToEdit = ref(null);
const objectInput = ref(''); 
const objectInputRef = ref(null);
const isEditOpen = ref(false);

const workExperiences = ref(['']);

onMounted(async () => {
    user.value = authStore.getUser;
    //message input box
    autoResize();


});

// onUnmounted(() => {
//     window.removeEventListener('keydown', escapeKeyEvent)
// });


function autoResize() {
  const el = objectInputRef.value
  el.style.height = 'auto'
  
  const newHeight = el.scrollHeight
  const maxHeight = 400
  
  el.style.height = Math.min(newHeight, maxHeight) + 'px'
}

// function escapeKeyEvent(event){
//     alert("i am pressed");
// }

const toggleModal = (edit) => {
    isEditOpen.value = edit == 'close' ? false : true;
    objectToEdit.value = edit;
    if (edit == 'workExperience'){
        //
    }
}

const addWorkExperienceInput = () => {
    workExperiences.value.push('');
}

const removeExperienceInput = (index) => {
    console.log("before remove array value: ", workExperiences.value);
    console.log("index to remove: ", index);
    workExperiences.value.splice(index,1);
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
                        {{ user?.brief_description ?? "No information yet." }}
                    </p>
                </div>
                <div class="border-b border-gray-300 flex flex-col gap-4 p-4">
                    <p class="font-bold text-xl flex items-center gap-2">
                        Skills <span><Pencil class="w-3 cursor-pointer text-gray-500" @click="toggleModal('skills')"/></span> 
                    </p>
                    <p class="text-sm text-gray-500">
                        {{ user?.skills ?? "No information yet." }}
                    </p>  
                </div>
                <div class="border-b border-gray-300 flex flex-col gap-4 p-4">
                    <p class="font-bold text-xl flex items-center gap-2">
                        Educational attainment <span><Pencil class="w-3 cursor-pointer text-gray-500" @click="toggleModal('educational attainment')"/></span>
                    </p>
                    <p class="text-sm text-gray-500">
                        {{ user?.educational_attainment ?? "No information yet." }}
                    </p>  
                </div>
                <div class="flex flex-col gap-4 p-4">
                    <p class="font-bold text-xl flex items-center gap-2">
                        Work experience <span><Pencil class="w-3 cursor-pointer text-gray-500" @click="toggleModal('work experience')"/></span>
                    </p>
                    <p class="text-sm text-gray-500">
                        {{ user?.work_experience ?? "No information yet." }}
                    </p>  
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
                        <div v-for="(count,index) in workExperiences" :key="{index}" class="grid grid-cols-12 gap-4 text-center my-2">
                            {{ index }}
                            <div class="col-span-4 flex flex-col items-start gap-2">
                                <p>Company</p>
                                <input type="text" class="border border-gray-300 rounded w-full" />
                            </div>
                            <div class="col-span-3 flex flex-col items-start gap-2">
                                <p>Position</p>
                                <input type="text" class="border border-gray-300 rounded w-full" />
                            </div>
                            <div class="col-span-2 flex flex-col items-start gap-2">
                                <p>Year started</p>
                                <input type="text" class="border border-gray-300 rounded w-full" />
                            </div>
                            <div class="col-span-2 flex flex-col items-start gap-2">
                                <p>Year ended</p>
                                <input type="text" class="border border-gray-300 rounded w-full" />
                            </div>
                            <div class="col-span-1 flex items-center mt-7 justify-center">
                                <CircleMinus v-if="index != 0" @click="removeExperienceInput({index})" class="text-red-500 cursor-pointer hover:opacity-70"/>
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
                    <button 
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