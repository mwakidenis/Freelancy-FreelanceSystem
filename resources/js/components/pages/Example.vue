<script setup> 
import { onMounted } from 'vue';
import { useAuthStore } from '../../authStore';

const authStore = useAuthStore();
// onMounted(() => {
//     if(authStore.isAuthenticated){
//         // console.log("The email verified value is: ", authStore.isEmailVerified);
//         console.log("HOWEVER the true value is: ", authStore.user['email_verified_at']);
//     }
// });

function test(){
    alert("i am clicked");
}
</script>


<template>
    <div style="display:flex; justify-content: space-evenly;">
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
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="text-3xl">Example Component</div>
                    <p @click="test">Click me</p>
                    <div v-if="authStore.isAuthenticated">
                        The user is logged in, the user is: {{ authStore.user['name'] }}
                    </div>
                    <div v-else>
                        The user is not logged in
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full h-20 border-b border-gray-300 mt-40">
        <div class="max-w-[1300px] w-full flex items-center justify-between mx-auto px-2">
            <router-link to="/example" class="text-6xl uppercase font-extrabold text-blue-500 ">Freelancy</router-link>
            <router-link to="/login" class="">Login</router-link>
        </div>
    </div>
</template>

