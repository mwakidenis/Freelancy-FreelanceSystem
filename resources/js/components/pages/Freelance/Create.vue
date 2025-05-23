<script setup>
import { ref } from 'vue';
import axios from 'axios';

const title = ref(null);
const description = ref(null);
const category = ref(null);
const rate = ref(null);

const create = async() =>{
    try{
        const response = await axios.post("http://127.0.0.1:8000/api/freelance/create",
            {
                title:title.value,
                description:description.value,
                category:category.value,
                rate:rate.value
            },
            {withCredentials:true}
        )
        console.log("response: ",response);
    } catch (error){
        console.log(error);
    } finally {
        title.value = null;
        description.value = null;
        category.value = null;
        rate.value = null;
    }
}
</script>

<template>
    <form @submit.prevent="create">
        <div>
            <p>Title</p>
            <input type="text" v-model="title"/>
        </div>
        <div>
            <p>Description</p>
            <input type="text" v-model="description"/>
        </div>
        <div>
            <p>Category</p>
            <input type="text" v-model="category"/>
        </div>
        <div>
            <p>Rate</p>
            <input type="text" v-model="rate"/>
        </div>
        <button type="submit">Create</button>
    </form>
</template>