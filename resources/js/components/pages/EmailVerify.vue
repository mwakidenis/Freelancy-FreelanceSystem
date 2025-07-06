<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from '../../authStore';

const authStore = useAuthStore();

onMounted(async() => {
    await authStore.logout();
});
</script>

<template>
    <body class="bg-blue-300">
        <div class="h-screen w-full flex justify-center items-center">
            <div class="w-md h-72 bg-white rounded-lg flex flex-col items-center text-center p-12">
                <p class="text-6xl text-blue-500 uppercase font-extrabold mb-12">Freelancy</p>
                Kindly check your email for verification purposes.
                <p class="text-gray-500 mt-2">Click <router-link class="text-blue-500" :to="{name:'login'}">here</router-link> to login again after verification.</p>
            </div>
        </div>
    </body>
</template>