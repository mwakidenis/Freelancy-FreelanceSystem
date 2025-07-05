<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../authStore';
import api from '../../api';
import Button from '../component/Button.vue';
import InputBox from '../component/InputBox.vue';
import SelectBox from '../component/SelectBox.vue';
import { Eye } from 'lucide-vue-next';
import { EyeOff } from 'lucide-vue-next';
import { ClipLoader } from 'vue-spinner/dist/vue-spinner.min.js'

const authStore = useAuthStore();
const router = useRouter();

const isLogin = ref(true);
const loginEmail = ref(null);
const loginPassword = ref(null);
const isLoginWrong = ref(false);
const isLoginPasswordToggled = ref(false);

const registerName = ref(null);
const registerEmail = ref(null);
const registerRole = ref(null);
const registerPassword = ref(null);
const registerConfirmPassword = ref(null);
const isRegisterPasswordToggled = ref(false);
const isRegisterConfirmPasswordToggled = ref(false);
const isConfirmPasswordWrong = ref(false); 
const registerErrorMessage = ref(null);
const isRegisterWrong = ref(false);
const isRegisterSuccess = ref(false);

const isLoading = ref(false);

function emptyLoginInput() {
    loginEmail.value = null;
    loginPassword.value = null;
    isLoginWrong.value = false;
    isLoginPasswordToggled.value = false;
}

function emptyRegisterInput(){
    registerName.value = null;
    registerEmail.value = null;
    registerRole.value = null;
    registerPassword.value = null;
    registerConfirmPassword.value = null;
    isRegisterPasswordToggled.value = false;
    isRegisterConfirmPasswordToggled.value = false;
    isConfirmPasswordWrong.value = false;
    registerErrorMessage.value = null;
    isRegisterWrong.value = false;
    isRegisterSuccess.value = false;
}

const toggleLogin = () => {
    isLogin.value = !isLogin.value;
    emptyLoginInput();
    emptyRegisterInput();
}

const login = async () => {
    isLoading.value = true;
    setTimeout(async() => {
        try {
            await authStore.login(loginEmail, loginPassword);
            if (authStore.isAuthenticated) {
                router.push('/');
            } else {
                isLoginWrong.value = true;
            }
        } catch (error) {
            isLoginWrong.value = true;
        } finally{
            loginEmail.value = null;
            loginPassword.value = null;
            isLoading.value = false;
        }
    },1500);
   
}

const register = async () => {
    isLoading.value = true;
    setTimeout(async() => {
        if (registerPassword.value == registerConfirmPassword.value){
        try {
            const response = await api.post('/auth/register', {
                name:registerName.value,
                email:registerEmail.value,
                role:registerRole.value,
                password:registerPassword.value
            });
            if (response.status === 201){
                isRegisterSuccess.value = true;
            }
        } catch (error) {
            isRegisterWrong.value = true;
            if (error.status === 409){
                registerErrorMessage.value = "Email address already used.";
            } else if (error.status === 400){
                registerErrorMessage.value = "Input error.";
            } else {
                registerErrorMessage.value = "Server error.";
            }
        }
    } else {
        isConfirmPasswordWrong.value = true;
    }
        isLoading.value=false;
    },1500)

}
</script>

<template>
    <body class="bg-blue-300 relative">
        <!-- Overlay -->
        <div v-if="isLoading" class="fixed inset-0 bg-black opacity-70 z-50"></div>

        <!-- Centered Content Box -->
        <div v-if="isLoading" class="fixed inset-0 flex justify-center items-center z-50">
            <div class="bg-white w-32 h-20 p-6 rounded-lg shadow-lg flex justify-center items-center">
                <clip-loader color="#2b7fff"></clip-loader>
            </div>
        </div>

        <div class="h-screen w-full flex justify-center items-center">
            <div class="h-10/12 w-11/12 lg:w-8/12 bg-white rounded-lg grid grid-cols-1 lg:grid-cols-2">
                
                <!-- LEFT COLUMN: LOGIN -->
                <div v-if="isLogin" class="flex items-center justify-center p-6 px-20">
                    <form @submit.prevent="login" class="w-full flex flex-col items-center">
                        <div class="flex flex-col gap-6">
                            <router-link to="/"
                                class="text-6xl text-blue-500 uppercase font-extrabold mb-6">
                                Freelancy
                            </router-link>
                            <p v-if="isLoginWrong" class="text-sm text-center text-red-500">Incorrect credentials.</p>
                            
                            <div class="flex flex-col gap-4">
                                <!-- Email Field -->
                                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-center">
                                    <p class="col-span-1">Email</p>
                                    <InputBox class="col-span-3 w-full" :class="{ 'border border-red-500': isLoginWrong }" type="text" v-model="loginEmail" />
                                </div>

                                <!-- Password Field -->
                                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-center">
                                    <p class="col-span-1">Password</p>
                                    <div class="col-span-3 relative"> 
                                        <InputBox 
                                            class="w-full pr-10" 
                                            :class="{ 'border border-red-500': isLoginWrong }" 
                                            :type="isLoginPasswordToggled ? 'text' : 'password'" 
                                            v-model="loginPassword" 
                                            id="loginPassword"
                                        />
                                        <EyeOff v-if="isLoginPasswordToggled" class="absolute top-1/2 right-3 transform -translate-y-1/2 cursor-pointer text-gray-500" @click="isLoginPasswordToggled = !isLoginPasswordToggled" />
                                        <Eye v-else class="absolute top-1/2 right-3 transform -translate-y-1/2 cursor-pointer text-gray-500" @click="isLoginPasswordToggled = !isLoginPasswordToggled"/>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-center">
                                Don't have an account? 
                                <a class="text-blue-500 cursor-pointer" @click="toggleLogin">Sign up here.</a>
                            </p>
                            
                            <Button type="submit" name="Login" class="ml-auto" />
                        </div>
                    </form>
                </div>

                <!-- RIGHT COLUMN: IMAGE -->
                <div class="object-cover h-full w-full hidden lg:block">
                    <img src="/img/admin.jpg" class="h-full w-full object-cover rounded-r-lg" alt="Admin">
                </div>

                <!-- REGISTER COLUMN -->
                <div v-if="!isLogin" class="flex items-center justify-center p-6 px-20">
                    <form @submit.prevent="register" class="w-full flex flex-col items-center">
                        <div class="flex flex-col gap-6">
                            <router-link to="/"
                                class="text-6xl text-blue-500 uppercase font-extrabold mb-6 mx-auto">
                                Freelancy
                            </router-link>
                            <p v-if="isRegisterWrong" class="text-sm text-center text-red-500">{{registerErrorMessage}}</p>
                            <p v-if="isRegisterSuccess" class="text-sm text-center text-green-500">Successfully registered! Please try to log in.</p>
                            <div class="flex flex-col gap-6">
                                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-center">
                                    <p class="col-span-1">Name</p>
                                    <InputBox type="text" v-model="registerName"
                                        :class="{
                                            'col-span-3 w-full': true,
                                            'border border-red-500' : isRegisterWrong}"/>
                                </div>

                                <!-- Email Field -->
                                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-center">
                                    <p class="col-span-1">Email</p>
                                    <InputBox type="text" v-model="registerEmail" 
                                        :class="{
                                        'col-span-3 w-full': true,
                                        'border border-red-500': isRegisterWrong}"/>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-center">
                                    <p class="col-span-1">Type</p>
                                    <SelectBox v-model="registerRole" :options="['Client', 'Freelancer']" placeholder="Choose your user type"
                                        :class="{
                                            'col-span-3 w-full' : true,
                                            'border border-red-500': isRegisterWrong}"/>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-center">
                                    <p class="col-span-1">Password</p>
                                    <div class="col-span-3 relative"> 
                                        <InputBox 
                                            class="w-full pr-10" 
                                            :class="isConfirmPasswordWrong || isRegisterWrong ? 'border border-red-500' : ''"
                                            :type="isRegisterPasswordToggled ? 'text' : 'password'" 
                                            v-model="registerPassword"/>
                                        <EyeOff v-if="isRegisterPasswordToggled" class="absolute top-1/2 right-3 transform -translate-y-1/2 cursor-pointer text-gray-500" @click="isRegisterPasswordToggled = !isRegisterPasswordToggled" />
                                        <Eye v-else class="absolute top-1/2 right-3 transform -translate-y-1/2 cursor-pointer text-gray-500" @click="isRegisterPasswordToggled = !isRegisterPasswordToggled"/>
                                    </div>
                                </div>

                                <div v-if="isConfirmPasswordWrong" class="text-sm text-right text-red-500">
                                    Password mismatch.
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-center">
                                    <p class="col-span-1">Confirm Password</p>
                                    <div class="col-span-3 relative"> 
                                        <InputBox 
                                            class="w-full pr-10" 
                                            :class="isConfirmPasswordWrong || isRegisterWrong? 'border border-red-500' : ''"
                                            :type="isRegisterConfirmPasswordToggled ? 'text' : 'password'" 
                                            v-model="registerConfirmPassword"/>
                                        <EyeOff v-if="isRegisterConfirmPasswordToggled" class="absolute top-1/2 right-3 transform -translate-y-1/2 cursor-pointer text-gray-500" @click="isRegisterConfirmPasswordToggled = !isRegisterConfirmPasswordToggled" />
                                        <Eye v-else class="absolute top-1/2 right-3 transform -translate-y-1/2 cursor-pointer text-gray-500" @click="isRegisterConfirmPasswordToggled = !isRegisterConfirmPasswordToggled"/>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-center">
                                Already have an account? 
                                <a class="text-blue-500 cursor-pointer" @click="toggleLogin">Login here.</a>
                            </p>
                            
                            <Button type="submit" name="Register" class="ml-auto" />
                        </div>
                    </form>
                </div>
                
            </div>
        </div>
    </body>
</template>
