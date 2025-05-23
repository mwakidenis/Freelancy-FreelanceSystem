<template>
    <form v-on:submit.prevent="createNews">
        <div>
            <p>Title</p>
            <input type="text" v-model="title"/>
        </div>
        <div>
            <p>Description</p>
            <input type="text" v-model="description"/>
        </div>
        <button type="submit">Submit</button>
    </form>
    <p v-if="isDone">The message is: {{ message }}</p>
</template>

<script>
import axios from 'axios';

export default{
    data() { //similar to use state in react (to access the variable, use the keyword this)
        return {
            title: null,
            description: null,
            message:null,
            isDone:false,
        }
    },
    methods:{ //where you will create your methods
        async createNews() {
            try {
                const response = await axios.post('http://127.0.0.1:8000/api/test/create',
                {title: this.title,
                description:this.description}
                ); 
                this.message = "Successfully created a news";
                console.log("response is",response);
            } catch (error) {
                this.message = "Error creating a news";
                console.log(error);
            } finally {
                this.title = null;
                this.description = null;
                this.isDone = true;
            }
        }
    }
}
</script>