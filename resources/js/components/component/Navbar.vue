<script setup>
import { useAuthStore } from '../../authStore';
import { MessageSquareMore } from 'lucide-vue-next';

const authStore = useAuthStore();
</script>

<template>
<div class="w-full border-b border-gray-300 py-4">
    <div class="max-w-[1300px] w-full flex items-center justify-between mx-auto px-2">
        <router-link to="/" class="text-5xl uppercase font-extrabold text-blue-500 ">Freelancy</router-link>
        <div v-if="authStore.isAuthenticated" class="flex gap-4">
            <router-link :to="{name:'inbox'}">
                <MessageSquareMore/>
            </router-link>
            <div>
                <router-link :to="{name:'client-view-projects'}">Projects</router-link>
            </div>
            <div>
                <router-link :to="{name:'freelancer-view-proposals'}">Proposals</router-link>
            </div>
            <router-link :to="{name:'edit-profile'}">
                Profile
            </router-link>
            <p>Hi {{ authStore.getUser.name }}</p>
            <p class="cursor-pointer" @click="authStore.logout">Logout</p>
        </div>
        <div v-else>
            <router-link to="/login" class="">Login</router-link>
        </div>
        
    </div>
</div>
</template>