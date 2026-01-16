import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import '../css/app.css';
import 'sweetalert2/dist/sweetalert2.min.css';
import 'toastr/build/toastr.min.css';
import router from './router';
import App from './App.vue';

const app = createApp(App);

app.use(createPinia());
app.use(router);

import { useAuthStore } from './stores/auth';
app.config.globalProperties.$can = (permission) => {
    const auth = useAuthStore();
    return auth.can(permission);
};

app.mount('#app');
