import { createApp } from 'vue';
import { createPinia } from 'pinia';
import "../css/app.css";
import route from './router/route';
import App from './components/layout/App.vue';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    authEndpoint: 'http://127.0.0.1:8000/api/broadcasting/auth',
    auth: {
        withCredentials: true
    }
});

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate); //for data persistency of auth

const app = createApp(App);
app.use(pinia);  // Pinia should be registered first
app.use(route);          // Then Vue Router
app.mount('#app');
