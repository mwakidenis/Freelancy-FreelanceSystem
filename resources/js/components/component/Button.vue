<script setup>
const props = defineProps(['name']);
</script>

<template>
    <button type="submit" class="bg-blue-500 cursor-pointer text-white w-32 h-12 rounded-xl hover:opacity-80">
        {{ props.name }}
    </button>
</template>