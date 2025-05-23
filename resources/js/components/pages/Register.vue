<script setup>
import { ref } from 'vue';
import axios from 'axios';
// import { useAuthStore } from '../../authStore';

const name = ref(null);
const email = ref(null);
const role = ref(null);
const password = ref(null);
const confirm_password = ref(null);

const register = async() => {
    if (password.value != confirm_password.value){
        console.log("password not equal");
    } else {
        try {
            const response = await axios.post('http://127.0.0.1:8000/api/auth/register', {
                name:name.value,
                email:email.value,
                role:role.value,
                password:password.value
            });
            console.log("response: ",response);
        } catch (error){
            console.log("error: ",error);
        }
    }

}
</script>

<template>
    <form @submit.prevent="register">
        <div>
            Name 
            <input type="text" v-model="name">
        </div>
        <div>
            Email 
            <input type="text" v-model="email">
        </div>
        <div>
            Role 
            <input type="text" v-model="role">
        </div>
        <div>
            Password 
            <input type="text" v-model="password">
        </div>
        <div>
            Confirm Password 
            <input type="text" v-model="confirm_password">
        </div>
        <button type="submit">Register</button>
    </form>
</template>