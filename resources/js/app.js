import './bootstrap';
import { createApp } from 'vue';
import App from './components/App.vue';
import router from './router';

const app = createApp(App);
app.use(router);
app.mount('#app');
// Fsheh fallback-in e shportës (HTML) vetëm nëse Vue navbar ka link shportë (version i ri); përndryshe mbaj fallback-in që të shfaqet në arontrade.net
setTimeout(function () {
  const vueHasCart = document.querySelector('#app a[href="/shporta"]');
  if (vueHasCart) {
    document.getElementById('fallback-nav-cart')?.style.setProperty('display', 'none');
  }
}, 150);
