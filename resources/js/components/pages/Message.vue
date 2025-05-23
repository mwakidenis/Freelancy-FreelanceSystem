<script setup>
import { ref, onMounted, onBeforeUnmount,watch, computed, onUnmounted } from "vue";
import { useRoute } from "vue-router";
import api from "../../api";
import { useAuthStore } from "../../authStore";
import { House,ArrowLeft,CircleUserRound,SendHorizonal } from "lucide-vue-next";
import moment from 'moment';

// const route = useRoute();
// const authStore = useAuthStore();

// const username = ref(route.params.username);
// const messages = ref([]);

// const channelName = ref(null);

// const sender = ref(authStore.getUser);
// const receiver = ref('');

// const messageDescription = ref(null);

// const ids = [sender?.id, receiver?.id].sort((a, b) => a - b);
// channelName.value = `chat.${ids[0]}.${ids[1]}`;

// window.Echo.private(channelName.value)
// .listen('MessageSent', (e) => {
//     console.log("Got message:", e); 
//     messages.value.push(e);
// });

// async function fetchMessages() {
//   try {
//     const response = await api.get(`/message/${username.value}`, {
//       withCredentials: true
//     });
//     messages.value = response.data.data;
//   } catch (error) {
//     console.error("Error fetching messages:", error);
//   }
// }

// async function fetchReceiver(){
//   try {
//     const response = await api.get(`/${username.value}`);
//     receiver.value = response.data.data;
//   } catch (error){
//     console.error(error);
//   }
// }


  
//frontend logic and variables 
const now = ref(new Date());
let interval;
const isMobile = ref(window.innerWidth < 768);
const isToggled = ref(false);

function handleResize() {
  isMobile.value = window.innerWidth < 768;
}


onMounted(async () => {
  await fetchInbox(); //fetch inbox
  await fetchMessages(inbox.value?.[0]?.username);
  //ui resize
  window.addEventListener("resize", handleResize);
  handleResize();

  //message input box
  autoResize();
  
  //live time elapsed for inbox
  interval = setInterval(() => {
    now.value = new Date() 
  }, 60000)
  

  // console.log("first value is: ", inbox.value?.[0]?.username);
});

onBeforeUnmount(() => {
  window.removeEventListener("resize", handleResize);
});

onUnmounted(() => {
  clearInterval(interval);
});

watch(isMobile, (newVal) => {
  console.log("Change in mobile:", newVal);
  if (!newVal) {
    isToggled.value = false; // Reset toggle on large screen
  }
});

// watch(() => )

function viewMessage(){ //i will fetch messages depending on which conversation is opened
  isToggled.value = true;
  //fetch method
  console.log("is toggled is clicked! is togggled value is: ",isToggled.value);
  console.log("is toggled is clicked! is mobile value is: ",isToggled.value);
}

function backToInbox(){
  isToggled.value = false;
}

function autoResize() {
  const el = messageInput.value
  el.style.height = 'auto'
  el.style.height = el.scrollHeight + 'px'
}


//backend fetches and variables
const inbox = ref([null]);
const messages = ref([null]);

var username = ref(null);
const messageInput = ref(null);

async function fetchInbox() {
  try {
    const response = await api.get('/message/inbox',{withCredentials:true});
    inbox.value = response.data.data;
  } catch (error){
    console.error(error);
  }
}

async function fetchMessages(user){
  try {
    const response = await api.get(`/message/inbox/${user}`,{withCredentials:true});
    messages.value = response.data.data;
    username.value = user;
  } catch (error) {
    console.error(error);
  }
}

const sendMessage = async () => {
  try {
    const response = await api.post(`/message/inbox/${username.value}`,
      {
        message: messageInput.value
      },
      {
        withCredentials: true
      }
    );
    console.log("Response after sending message:", response);
  } catch (error) {
    console.error("Error sending message:", error);
  } finally {
    messageInput.value = null;
  }
};

function formatInboxTime(sent) {
  // re-evaluates every time `now` changes
  now.value // dependency tracking
  return sent ? moment.utc(sent).local().fromNow() : ''
}

</script>

<template>

<div class="flex gap-4 p-4 h-screen overflow-hidden">
  <!-- Left: Inbox -->
  <div class="rounded-xl border border-gray-300 flex flex-col md:w-1/3 w-full md:max-w-sm"
    :class="{
      'w-full': isMobile && !isToggled,
      'hidden': isMobile && isToggled, 
    }">
    <div class="flex  items-center border-b border-gray-300 px-4 gap-2">
      <router-link to="/">
        <House class="text-blue-500"/>
      </router-link>
      <div class="p-4 font-bold">Inbox</div>
    </div>
    
    <ul class="overflow-y-auto max-h-[calc(100vh-112px)]">
      <li v-for="(inboxMessage,index) in inbox" @click="fetchMessages(inboxMessage?.username); viewMessage()"
        class="p-4 border-b border-gray-300 cursor-pointer flex gap-3 hover:bg-gray-100"
        :class="{'bg-gray-100' : username == inboxMessage?.username}">
        <CircleUserRound class="w-12 h-12"/>
        <div class="flex flex-col mt-2 w-full">
          <div class="font-bold">{{ inboxMessage?.username ?? '' }}</div>
          <div class="flex justify-between text-xs text-gray-500">
            <div>{{ inboxMessage?.last_message ?? '' }}</div>
            <div>{{ formatInboxTime(inboxMessage?.last_updated_at) }}</div>
          </div>
        </div>
      </li>
    </ul>
  </div>

  <!-- Right: Chat window -->
  <div class="rounded-xl border border-gray-300 flex flex-col flex-1"
    :class="{
      'hidden': isMobile && !isToggled,
      'w-full': isMobile && isToggled,
    }">
    <div class="flex gap-3 p-4 font-bold border-b border-gray-300">
      <ArrowLeft class="cursor-pointer" @click="backToInbox"
      :class="{
        'block': isMobile && isToggled,
        'hidden': !isMobile || !isToggled,
      }"/>
      <div class="flex items-center gap-2"><CircleUserRound class="w-8 h-8"/> {{username}}</div>
    </div>

    <!-- Scrollable messages -->
    <div class="flex-1 overflow-y-auto p-4 space-y-2 max-h-[calc(100vh-160px)]">
      <div v-for="(message,index) in messages">
        <div v-if="message?.sender == username">
          <!-- User 1 -->
          <div class="bg-blue-100 p-2 rounded max-w-sm">{{message?.message}}</div>
        </div>
        <div v-else class="ml-auto text-right">
          <!-- User 2 -->
          <div class="bg-gray-200 p-2 rounded max-w-sm ml-auto text-right">{{message?.message}}</div>
        </div>
      </div>

      <!-- Add more to test scroll -->
       
          <!-- {{ message?.message }}
       </div> -->
    </div>

    <!-- Chat input fixed at bottom -->
    <div class="flex items-center gap-2 p-4">
      <textarea
        class="border border-gray-300 p-2 rounded w-full min-h-[3rem] resize-none overflow-hidden"
        rows="1"
        @input="autoResize"
        ref="messageInput"
      ></textarea>
      <SendHorizonal class="text-blue-500 cursor-pointer"/>
    </div>

  </div>
</div>

</template>
