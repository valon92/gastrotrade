import './bootstrap';
import { createApp } from 'vue';
import App from './components/App.vue';
import router from './router';

const app = createApp(App);
app.use(router);
app.mount('#app');
// Nëse Vue navbar ka shportë (version i ri), fsheh shiritin fallback që vjen nga HTML (mobile)
setTimeout(function () {
  if (document.querySelector('#app a[href="/shporta"]')) {
    const bar = document.getElementById('fallback-cart-bar');
    if (bar) bar.style.setProperty('display', 'none', 'important');
  }
}, 200);
