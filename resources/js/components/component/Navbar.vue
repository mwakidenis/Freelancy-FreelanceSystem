<script setup>
import { ref,watch} from 'vue';
import { useRoute,useRouter } from 'vue-router';
import { useAuthStore } from '../../authStore';
import { MessageSquareMore, ChevronDown, Send } from 'lucide-vue-next';
import Swal from 'sweetalert2';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const isDropdownOpen = ref(false);

watch(() => route.fullPath, () => {
  isDropdownOpen.value = false;
});

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
}

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
    router.push("/");

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
        <router-link to="/" class="text-4xl uppercase font-extrabold text-blue-500 ">Freelancy</router-link> 
        <div v-if="authStore.isAuthenticated" class="flex flex gap-4 relative">
            <router-link :to="{name:'inbox'}" class="mt-1">
                <MessageSquareMore class="text-blue-500 h-6 w-6"/>
            </router-link>
            <p class="cursor-pointer flex items-center gap-1 text-sm" @click="toggleDropdown()">
              {{authStore?.getUser.name}} <span><ChevronDown class="w-3 h-3 mt-1"/></span>
            </p>
            <div v-if="isDropdownOpen" 
              class="absolute right-0 top-10 z-50 bg-white border border-gray-300 rounded w-40 max-h-auto text-center text-xs">
              <div class="border-b border-gray-300 cursor-pointer hover:bg-gray-100 py-2">
                <router-link :to="{name:'edit-profile'}">
                  Profile
                </router-link>
              </div>
              <div v-if="authStore.getUser.user_type == 'freelancer'" class="border-b border-gray-300 cursor-pointer hover:bg-gray-100 py-2">
                <router-link :to="{name:'freelancer-view-proposals'}">
                  Proposals
                </router-link>
              </div>
              <div v-if="authStore.getUser.user_type == 'client'" class="border-b border-gray-300 cursor-pointer hover:bg-gray-100 py-2">
                <router-link :to="{name:'client-view-projects'}">
                  Projects
                </router-link>
              </div>    
              <div class="cursor-pointer cursor-pointer hover:bg-gray-100 py-2 text-red-500" @click="logout()">Logout</div>
            </div>
        </div>
        <div v-else>
            <router-link to="/login" class="">Login</router-link>
        </div>

    </div>
      
</div>
</template>