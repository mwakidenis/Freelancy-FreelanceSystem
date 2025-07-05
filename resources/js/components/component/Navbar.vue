<script setup>
import { useAuthStore } from '../../authStore';
import { MessageSquareMore } from 'lucide-vue-next';
import Swal from 'sweetalert2';

const authStore = useAuthStore();

const logout = async () => {
  const result = await Swal.fire({ //swal confirmation modal 
    text: "Do you want to logout?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    confirmButtonColor: '#22c55e',
    cancelButtonText: 'Cancel',
    cancelButtonColor: "#ef4444",
  })

  if (result.isConfirmed) { 
    Swal.fire({ //swal loading
      text: 'Logging out ...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading()
      }
    })

    await authStore.logout();

    Swal.fire({ //swal result
      text: "Logout successfully!",
      icon: 'success',
      timer: 1500,
      showConfirmButton: false
    })
    
  }
}
</script>

<template>
<div class="w-full border-b border-gray-300 py-4">
    <div class="max-w-[1300px] w-full flex items-center justify-between mx-auto px-2">
        <router-link to="/" class="text-5xl uppercase font-extrabold text-blue-500 ">Freelancy</router-link>
        <div v-if="authStore.isAuthenticated" class="flex gap-4">
            <router-link :to="{name:'inbox'}">
                <MessageSquareMore/>
            </router-link>
            <div v-if="authStore.getUserRole == 'client'">
                <router-link :to="{name:'client-view-projects'}">Projects</router-link>
            </div>
            <div v-if="authStore.getUserRole == 'freelancer'">
                <router-link :to="{name:'freelancer-view-proposals'}">Proposals</router-link>
            </div>
            <router-link :to="{name:'edit-profile'}">
                Profile
            </router-link>
            <p>Hi {{ authStore.getUser.name }}</p>
            <p class="cursor-pointer" @click="logout()">Logout</p>
        </div>
        <div v-else>
            <router-link to="/login" class="">Login</router-link>
        </div>
        
    </div>
</div>
</template>