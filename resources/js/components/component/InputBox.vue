<script setup>
import { defineProps,defineEmits } from 'vue';
const props = defineProps(['type','modelValue']);
const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <input class="bg-gray-100 h-8 rounded-lg" 
        :type="props.type"
        :value="props.modelValue"
        @input="$emit('update:modelValue',$event.target.value)" />
</template>