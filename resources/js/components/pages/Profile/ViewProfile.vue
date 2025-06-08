<script setup>
import { ref,watchEffect } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../../api';
import { useAuthStore } from '../../../authStore';
import Button from '../../component/Button.vue';
import { CircleUserRound } from 'lucide-vue-next';

const route = useRoute();
const authStore = useAuthStore();
const username = ref('');
const user = ref('');
const isError = ref(false);

watchEffect(() => {
    if (route.params.username){
        username.value = route.params.username;
        fetchProfile();
    }
});

async function fetchProfile(){
    try {
        const response = await api.get(`/profile/${username.value}`);
        user.value = response.data.data;
        if (response.status === 404) {
            isError.value = true;
        }
    } catch (error){
        if (error.response && error.response.status === 404) {
            isError.value = true;
        }
    }
}
</script>

<template>
    <div class="max-w-[1300px] w-full min-h-screen h-auto mx-auto md:px-0 px-4">
        <div v-if="!isError">
            <div class="w-full h-full flex flex-col justify-center mx-auto border-1 border-gray-300 my-12">
                <div class="grid grid-cols-12">
                    <div class="md:col-span-2 col-span-12 md:border-r border-gray-300 flex flex-col gap-2 px-4 py-8 text-center">
                        <div class="">
                            <CircleUserRound class="w-24 h-24 mx-auto"/>
                        </div>
                        <div v-if="authStore.isAuthenticated" class="mx-4">
                            <router-link :to="{name:'inbox', params: {username:user?.username}}">
                                <Button class="w-full" name="Message"/>
                            </router-link>
                        </div>
                        <div class="font-bold">
                            <p class="text-xl">{{user?.name}}</p>
                            <p class="text-sm text-gray-500">@{{user?.username}}</p>
                        </div>
                        <div class="">
                            <p class="text-sm font-bold text-gray-500">
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
                                {{ user?.brief_description ? user?.brief_description : "No profile information yet." }}
                            </p>
                        </div>
                        <div class="border-b border-gray-300 flex flex-col gap-4 p-4">
                            <p class="font-bold text-xl">Skills</p>
                            <div v-if="user?.skills" class="flex gap-4">
                                <Button name="Sample 1"/> 
                                <Button name="Sample 2"/>
                            </div>
                            <div v-else>
                                <p class="text-sm text-gray-500">No profile information yet.</p>
                            </div>       
                        </div>
                        <div class="border-b border-gray-300 flex flex-col gap-4 p-4">
                            <p class="font-bold text-xl">Educational attainment</p>
                            <p class="text-sm text-gray-500">
                                {{ user?.educational_attainment ? user?.educational_attainment : "No profile information yet." }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-4 p-4">
                            <p class="font-bold text-xl">Work experience</p>
                            <p class="text-sm text-gray-500">
                                {{ user?.work_experience ? user?.work_experience : "No profile information yet." }}
                            </p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
        <div v-else>
            Not found
        </div>
    </div>
</template>