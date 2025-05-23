<script setup>
import { useAuthStore } from '../../authStore';

const authStore = useAuthStore();
</script>

<template>
<div class="w-full border-b border-gray-300 py-4">
    <div class="max-w-[1300px] w-full flex items-center justify-between mx-auto px-2">
        <router-link to="/" class="text-5xl uppercase font-extrabold text-blue-500 ">Freelancy</router-link>
        <div v-if="authStore.isAuthenticated" class="flex gap-4">
            <div v-if="authStore.isClient">
                <router-link :to="{name:'client-view-projects'}">Projects</router-link>
            </div>
            <p>Hi {{ authStore.getUserName }}</p>
            <p class="cursor-pointer" @click="authStore.logout">Logout</p>
        </div>
        <div v-else>
            <router-link to="/login" class="">Login</router-link>
        </div>
        
    </div>
</div>
</template>