<script setup>
import { ref, onMounted, onBeforeUnmount,watch, onUnmounted } from "vue";
import { useRoute,useRouter } from "vue-router";
import api from "../../api";
import { useAuthStore } from "../../authStore";
import { House,ArrowLeft,CircleUserRound,SendHorizonal,SquarePen } from "lucide-vue-next";
import { ClipLoader } from 'vue-spinner/dist/vue-spinner.min.js'
import moment from 'moment';

//conversation variables 
const authStore = useAuthStore();
const route = useRoute(); //url params
const router = useRouter(); //url re-writing
const sender = ref(authStore.getUser);
const receiver = ref('');

//frontend logic and variables 
const now = ref(new Date());
let interval;
const isMobile = ref(window.innerWidth < 768);
const isToggled = ref(false);
const isLoading = ref(false);  //loader

//backend fetches and variables
const inbox = ref([null]);
const messages = ref([null]);
const username = ref(route.params.username || null);
const recentContacts = ref([]);
const isRecentContactsClicked = ref(false);

//form variables
const messageInput = ref('');
const messageInputRef = ref(null);
const isNewMessage = ref(false);
const sendNewMessage = ref(false);

//error variables
const isInboxError = ref(false); //in case of server errors 5xx

onMounted(async () => {
  await fetchInbox();
 
  if (isMobile.value == true){
    router.push({name:route.name});
  } else { 
    if (route.name === 'new-message'){ 
      username.value = null;
      isNewMessage.value = true;
    } else { 
      if (inbox.value.length > 0) {
        if (route.params.username) {
          username.value = route.params.username;
          if (!hasConversationWith(route.params.username)){
            isNewMessage.value = true;
          }
        } else {
          username.value = inbox.value?.[0]?.username;
        }
          router.replace({
              params: {
                  username: username.value
              }
          });
        await fetchMessages(username.value);
      } else {
        if (route.params.username) {
          username.value = route.params.username;
          isNewMessage.value = true;
        }
      }

    }
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

watch(isMobile, (newVal) => { //watch for checking the changes in variable isMobile
  if (!newVal) {  
    isToggled.value = false;
  } else {
    isToggled.value = true;
  }
});

watch(() => authStore.getUser?.id, (id) => { //watch for new messages sent to update inbox 
  if (id) {
    window.Echo.private(`inbox.${id}`)
      .listen('MessageSent', async (e) => {
        await fetchInbox();
      });
  }
}, { immediate: true });

watch(() => route.params.username, (newUsername) => { //watch for username change to update messages
  if (newUsername && isNewMessage.value == false) {
    fetchMessages(newUsername);
  }
}, {immediate:true});

watch(receiver,(newVal) => { //monitoring the conversation partner
  const ids = [sender?.value.id, receiver?.value.id].sort((a, b) => a - b);
  const channelName = ref(null);
  channelName.value = `chat.${ids[0]}.${ids[1]}`;

  window.Echo.private(channelName.value)
  .listen('MessageSent', (e) => {
      messages.value.push(e);
  });
});

function backToInbox(){ //for mobile view
  isToggled.value = false;
  username.value = "";
  isNewMessage.value = false;
  recentContacts.value = [];
  isRecentContactsClicked.value = false;
  messages.value = [];
  router.push({name:'inbox'});
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

const writeNewMessage = () => { //write new message to open "to" panel right
  username.value = null;
  isNewMessage.value = true;
  sendNewMessage.value = true;
  router.push('/inbox/new');
  if (isMobile) {
    isToggled.value = true;
  }
}

const sendMessage = async () => { //form method to send api post to send new message
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
    isNewMessage.value = false;
    sendNewMessage.value = false;
    messageInput.value = null;
    // fetchMessages(username.value);
  }
};

const handleSelectRecentContacts = async() => { //opens the box for recent contacts
  if (isRecentContactsClicked.value == false){
    isRecentContactsClicked.value = true;
    await fetchRecentContacts();
  } else {
    isRecentContactsClicked.value = false;
    recentContacts.value = [];
  }
}
 
const recentContactsClick = (usernameInput) => { //click user within recent contacts
  username.value = usernameInput;
  isRecentContactsClicked.value = false;
  fetchMessages(usernameInput);
  sendNewMessage.value = false;
}

const switchMessages = (usernameInput) => {//switching of messages through inbox
  messages.value = [];
  isNewMessage.value = false;
  recentContacts.value = [];
  isRecentContactsClicked.value = false;
  username.value = usernameInput;
  router.push({ name: 'inbox', params: { username: usernameInput } });
}

const hasConversationWith = (usernameInput) => { //checks if logged user has existing convo with user
  return inbox.value.some(conv => conv.username === usernameInput);
}

async function fetchInbox() { //fetches the logged in user's inbox
  try {
    const response = await api.get('/message/inbox',{withCredentials:true});
    inbox.value = response.data.data;
  } catch (error){
    isInboxError.value = true;
    console.error(error);
  } finally {
    if (inbox.value.length <= 0 ) {
      isInboxError.value = true;
    }
  }
}

async function fetchMessages(user){ //fetches the message between logged user and username
  messages.value = [];
  isLoading.value = true;
  var isUsernameInvalid = ref(true);
  try {
    const response = await api.get(`/message/inbox/${user}`,{withCredentials:true});
    messages.value = response.data.data;
    username.value = user;
    fetchUser(user);
    isUsernameInvalid.value = false;
  } catch (error) {
    console.error(error);
  } finally {
    if (isUsernameInvalid.value == false) {
      isLoading.value = false;
    }
    isToggled.value = isMobile ? true : false;
  }
}

async function fetchUser(user){ //fetches the recipient 
  try{
    const response = await api.get(`/profile/${user}`);
    receiver.value = response.data.data;
  } catch(error){
    console.error(error);
  }
}

async function fetchRecentContacts(){ //fetches recent contacts of the logged in user
  try{
    const response = await api.get('/message/recent-contacts');
    recentContacts.value = response.data.data;
  } catch (error){
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
      <div class="ml-auto">
        <SquarePen @click="writeNewMessage" class="cursor-pointer" />
      </div>
    </div>
   
    <ul class="overflow-y-auto max-h-[calc(100vh-112px)]">

      <li v-if="isNewMessage"
        class="p-4 border-b border-gray-300 cursor-pointer flex gap-3 bg-gray-100">
        <CircleUserRound class="w-12 h-12"/>
        <div class="flex flex-col mt-2 w-full">
          <div class="font-bold">New Message to {{ username }}</div>
        </div>
      </li>
      
      <li v-if="inbox.length > 0" v-for="(inboxMessage,index) in inbox" @click="switchMessages(inboxMessage?.username)"
        class="p-4 border-b border-gray-300 cursor-pointer flex gap-3 hover:bg-gray-100"
        :class="{'bg-gray-100' : username == inboxMessage?.username && !isNewMessage }">
        <CircleUserRound class="w-12 h-12"/>
        <div class="flex flex-col mt-2 w-full">
          <div class="font-bold">{{ inboxMessage?.username ?? '' }}</div>
          <div class="flex justify-between text-xs text-gray-500">
            <div>{{ inboxMessage?.last_message ?? '' }}</div>
            <div>{{ formatInboxTime(inboxMessage?.last_updated_at) }}</div>
          </div>
        </div>
      </li>

      <li v-else> 
        <p v-if="!isNewMessage" class="text-gray-300 text-center mt-4">No messages yet.</p>
      </li>
    </ul>

  </div>

  <!-- Right: Chat window -->
  <div class="rounded-xl border border-gray-300 flex flex-col flex-1 z-10"
    :class="{
      'hidden': isMobile && !isToggled,
      'w-full': isMobile && isToggled,
    }">
    <div class="flex gap-3 p-4 border-b border-gray-300">
      <ArrowLeft class="cursor-pointer" @click="backToInbox"
      :class="{
        'block': isMobile && isToggled,
        'hidden': !isMobile || !isToggled,
      }"/>
 
      <div v-if="!isInboxError || isNewMessage || username">
        <div v-if="isNewMessage">
          <div class="flex justify-content-center gap-5">
            <div class="font-bold">To:</div> 
            <div v-if="!username" class="border border-gray-300 w-60 h-8 cursor-pointer rounded" @click="handleSelectRecentContacts"></div>
            <div v-else class="cursor-pointer" @click="handleSelectRecentContacts">{{ username }}</div>

            <div v-if="isRecentContactsClicked" 
              class="absolute border border-gray-300 w-60 min-h-auto max-h-60 bg-gray-50 mt-7.5 z-40 text-xs flex flex-col overflow-y-auto space-y-1 ml-10.5">
              <div class="font-bold p-2">Recent contacts</div>
              <div v-for="(contact,index) in recentContacts" class="border-t border-gray-300 p-2 hover:bg-gray-300" @click="recentContactsClick(contact?.username)">
                {{ contact?.username }}
              </div>
            </div>
          </div>
        </div>

        <div v-else class="flex items-center gap-2">
          <CircleUserRound class="w-8 h-8"/> {{username}}
        </div> 
      </div>

    </div>

    <div v-if="!isLoading" class="flex flex-col flex-1 z-10">
      <!-- Scrollable messages -->
      <div v-if="username" class="relative flex-1 p-4 overflow-y-auto space-y-2 max-h-[calc(100vh-160px)]">

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
      <div v-if="username" class="flex items-center gap-2 p-4">
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
    <div v-else class="w-full h-full flex justify-center items-center">
      <clip-loader :loading="loading" color="#2b7fff" :size="size"></clip-loader>
    </div>

  </div>
  
</div>
</template>
