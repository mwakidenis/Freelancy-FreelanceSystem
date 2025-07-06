<script setup>
import { ref,onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../../api';
import { useAuthStore } from '../../../authStore';
import Button from '../../component/Button.vue';
import { CircleUserRound,Send } from 'lucide-vue-next';
import { ClipLoader } from 'vue-spinner/dist/vue-spinner.min.js';

const route = useRoute();
const authStore = useAuthStore();
const username = ref(route.params.username);
const user = ref('');
const isError = ref(false);
const isLoading = ref(true);

onMounted(async() => {
    await fetchProfile();
    isLoading.value = false;
});

async function fetchProfile(){
    try {
        const response = await api.get(`/profile/${username.value}`);
        user.value = response.data.data;
    } catch (error){
        isError.value = true;
        console.error(error);
    }
}
</script>

<template>
    <div v-if="isLoading" class="flex items-center justify-center gap-4 mt-60">
        <clip-loader color="#2b7fff"></clip-loader>
    </div>

    <div v-else-if="!isError">
        <div class="w-full h-full flex flex-col justify-center mx-auto border-1 border-gray-300 my-12">
            <div class="grid grid-cols-12">
                <div class="md:col-span-2 col-span-12 md:border-r border-gray-300 flex flex-col gap-3 px-4 py-8 text-center">
                    <div class="">
                        <CircleUserRound class="text-blue-500 w-24 h-24 mx-auto"/>
                    </div>
                    <div v-if="authStore.isAuthenticated" class="mx-4">
                        <router-link :to="{name:'inbox', params: {username:user?.username}}">
                            <button 
                                class="bg-green-500 cursor-pointer text-white w-auto h-8 rounded-xl hover:opacity-80
                                    flex items-center justify-center gap-2 mx-auto px-4 text-sm">
                                <span><Send class="h-3 w-3 mt-1"/></span> Message
                            </button>
                        </router-link>
                    </div>
                    <div class="font-bold">
                        <p class="text-lg">{{user?.name}}</p>
                        <p class="text-xs text-gray-500">@{{user?.username}}</p>
                    </div>
                    <div class="">
                        <p class="text-xs font-bold text-gray-500">
                            <div v-if="user?.user_type == 'freelancer'">
                                Number of proposals: {{ user?.number_of_freelances }}
                            </div>
                            <div v-else>
                                Number of projects: {{ user?.number_of_projects }}
                            </div>
                        </p>
                    </div>
                    <div class="">
                        <p class="text-xs text-gray-500">Member since {{new Date(user?.date_registered).toLocaleDateString("en-US", { year:'numeric',month:'long',day:'numeric' })}}</p>
                    </div> 
                </div>
                <div class="md:col-span-10 col-span-12">
                    <div class="border-t md:border-t-0 border-b border-gray-300 flex flex-col gap-4 p-4">
                        <p class="font-bold text-xl">Description</p>
                        <p class="text-sm text-gray-500">
                            {{ user?.brief_description ? user?.brief_description : "No information yet." }}
                        </p>
                    </div>
                    <div class="border-b border-gray-300 flex flex-col gap-4 p-4">
                        <p class="font-bold text-xl">Skills</p>
                        <div v-if="user?.skills" class="flex gap-4">
                            <Button name="Sample 1"/> 
                            <Button name="Sample 2"/>
                        </div>
                        <div v-else>
                            <p class="text-sm text-gray-500">No information yet.</p>
                        </div>       
                    </div>
                    <div class="border-b border-gray-300 flex flex-col gap-4 p-4">
                        <p class="font-bold text-xl">Educational attainment</p>
                        <p class="text-sm text-gray-500">
                            {{ user?.educational_attainment ? user?.educational_attainment : "No information yet." }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-4 p-4">
                        <p class="font-bold text-xl">Work experience</p>
                        <p class="text-sm text-gray-500">
                            {{ user?.work_experience ? user?.work_experience : "No information yet." }}
                        </p>
                    </div>
                </div>

            </div>
        </div>
        
    </div>

    <div v-else class="flex flex-col items-center justify-center gap-4 mt-60">
        <p class="text-4xl font-bold">Profile does not exist.</p>
        <p class="text-gray-500">Click here to go back to <router-link class="text-blue-500" :to="{name:'home'}">Home</router-link> </p>
    </div>
</template>