<script setup>
import { ref, onMounted, onBeforeUnmount,watch, onUnmounted } from "vue";
import { useRoute,useRouter } from "vue-router";
import api from "../../api";
import { useAuthStore } from "../../authStore";
import { House,ArrowLeft,CircleUserRound,SendHorizonal } from "lucide-vue-next";
import moment from 'moment';

//conversation variables 
const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();
const sender = ref(authStore.getUser);
const receiver = ref('');

//frontend logic and variables 
const now = ref(new Date());
let interval;
const isMobile = ref(window.innerWidth < 768);
const isToggled = ref(false);

//backend fetches and variables
const inbox = ref([null]);
const messages = ref([null]);
var username = ref(null);

//form variables
const messageInput = ref('');
const messageInputRef = ref(null)

onMounted(async () => {
  await fetchInbox();

  if(isMobile.value == false){
    await fetchMessages(inbox.value?.[0]?.username);
  }

  //ui resize
  window.addEventListener("resize", handleResize);
  handleResize();

  //message input box
  autoResize();
  
  //live time elapsed for inbox
  interval = setInterval(() => {
    now.value = new Date() 
  }, 60000)
});

onBeforeUnmount(() => {
  window.removeEventListener("resize", handleResize);
});

onUnmounted(() => {
  clearInterval(interval);
});

watch(receiver,(newVal) => { //monitoring the conversation partner
  const ids = [sender?.value.id, receiver?.value.id].sort((a, b) => a - b);
  const channelName = ref(null);
  channelName.value = `chat.${ids[0]}.${ids[1]}`;

  window.Echo.private(channelName.value)
  .listen('MessageSent', (e) => {
      messages.value.push(e);
  });
});

watch(isMobile, (newVal) => { //watch for checking the changes in variable isMobile
  if (!newVal) { 
    isToggled.value = false;
  }
});

function backToInbox(){ //for mobile view
  isToggled.value = false;
}

function handleResize() { //ui 2 column resize
  isMobile.value = window.innerWidth < 768;
}

function autoResize() { //for textbox
  const el = messageInputRef.value
  el.style.height = 'auto'
  el.style.height = el.scrollHeight + 'px'
}

function formatInboxTime(sent) { //formats time elapsed when the message was sent in inbox
  now.value
  return sent ? moment.utc(sent).local().fromNow() : ''
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
  } catch (error) {
    console.error("Error sending message:", error);
  } finally {
    messageInput.value = null;
  }
};

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
    fetchUser(user);
  } catch (error) {
    console.error(error);
  } finally {
    isToggled.value = isMobile ? true : false;
  }
}

async function fetchUser(user){
  try{
    const response = await api.get(`/profile/${user}`);
    receiver.value = response.data.data;
  } catch(error){
    console.error(error);
  }
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
      <li v-for="(inboxMessage,index) in inbox" @click="fetchMessages(inboxMessage?.username);"
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

    </div>

    <!-- Chat input fixed at bottom -->
    <div class="flex items-center gap-2 p-4">
      <textarea
        class="border border-gray-300 p-2 rounded w-full min-h-[3rem] resize-none overflow-hidden"
        rows="1"
        @input="autoResize"
        ref="messageInputRef"
        v-model="messageInput"
      ></textarea>
      <SendHorizonal class="text-blue-500 cursor-pointer" @click = "sendMessage"/>
    </div>

  </div>
</div>
</template>
