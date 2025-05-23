<script setup>
import { defineProps,defineEmits } from 'vue';

const props = defineProps(['options','modelValue','placeholder']);
const emit = defineEmits(['update:modelValue']);
</script>

<template>
<select class="bg-gray-100 h-8 rounded-lg" 
    :value="props.modelValue"
    @input="$emit('update:modelValue',$event.target.value)">
    <option class="text-center" v-if="!modelValue" value="" disabled hidden>{{ placeholder }}</option>
    <option v-for="(option,index) in props.options" :key="index" :value=option>{{option}}</option>  <!-- LOOP -->
</select>
</template>

