<script setup>
import { useAuthStore } from '../../authStore';

function logoutTest(){
    alert("logout button is clicked");
}

const authStore = useAuthStore();
</script>

<template>
    <!-- <div style="display:flex; justify-content: space-evenly;">
        <router-link to="/example">Example</router-link>
        <router-link to="/test">Test</router-link>
        <router-link to="/freelance/create">Create Freelance</router-link>
        <div v-if="authStore.isAuthenticated">
            <button class="bg-gray-500 cursor-pointer" @click="authStore.logout">Logout</button>
        </div>
        <div v-else>
            <router-link to="/login" style="margin:20px;">Login</router-link>
            <router-link to="/register" style="margin:20px;">Register</router-link>
        </div>
    </div> -->
    <main>
        <router-view/>
    </main>
</template>



