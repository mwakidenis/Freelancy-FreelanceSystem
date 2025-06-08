<script setup>
import Navbar from '../component/Navbar.vue';
</script>
<template>
  <div class="flex flex-col h-screen">
    <Navbar />
    <div class="flex-1 overflow-auto">
      <div class="max-w-[1300px] mx-auto md:px-0 px-4 py-4">
        <router-view />
      </div>
    </div>
  </div>
</template>