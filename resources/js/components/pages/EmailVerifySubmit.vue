<script setup>
import { onMounted,ref } from 'vue';
import { useRoute,useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter(); 
const status = ref(false);

onMounted(async ()=> {
    const token = route.query.token;
    // console.log("token is ", token);
    if (token){
        //implement try catch
        const response = await axios.post("http://127.0.0.1:8000/api/email/verify", {token:token});
        status.value = true;
        // console.log(response);
        router.push("/login");
    } else {
        status.value = true;
        // alert("Invalid token");
        router.push("/login");
    }
    
});
</script>

<template>
    <div v-if="status">
        <!-- if the token is being verified -->
         <p>Email verification in process...</p>
    </div>
    <div v-else>
        <p>Email verification done... Redirecting</p>
    </div>
</template>