<template>
    <p>Hello this is test</p>
    <p v-if="loading">Loading message</p>
    <p v-else>The message is "{{ data }}"</p>
</template>

<script>
import axios from 'axios';

export default {
    data(){ //returns the component state when the component is created (mainly for state management) just like useState in react
        return {
            data: [], //holds the data/payload from the api
            loading: true,
        };
    },
    async created() { //lifecycle hook that runs after the component is created
        try {
            const response = await axios.get('http://127.0.0.1:8000/api/test');
            this.data = response.data;
        } catch (error) {
            console.log(error);
        } finally {
            this.loading = false;
        }
    }
}
</script>