import './bootstrap';
import { createApp } from 'vue';
import App from './components/App.vue';
import router from './router';

const app = createApp(App);
app.use(router);
app.mount('#app');
setTimeout(function(){if(document.querySelector('#app a[href="/shporta"]')){var e=document.getElementById('nav-cart-fallback');e&&(e.style.setProperty('display','none','important'));}},350);
