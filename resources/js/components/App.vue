<template>
  <div id="app">
    <Navbar />
    <main>
      <router-view />
    </main>
    <Footer />
    <BackToTop />
  </div>
</template>

<script>
import Navbar from './Navbar.vue'
import Footer from './Footer.vue'
import BackToTop from './BackToTop.vue'
import cartStore from '../store/cart'
import axios from 'axios'

export default {
  name: 'App',
  components: {
    Navbar,
    Footer,
    BackToTop
  },
  async mounted() {
    // Rikupero sesionin e klientit në të gjithë platformën nëse ka client_token (pas refresh ose hapje të re)
    const clientToken = localStorage.getItem('client_token')
    const adminToken = localStorage.getItem('admin_token')
    if (clientToken && !adminToken) {
      try {
        const res = await axios.get('/api/client/me')
        if (res.data.success && res.data.data) {
          await cartStore.setClient(res.data.data)
        }
      } catch (e) {
        if (e.response?.status === 401 || e.response?.status === 403) {
          localStorage.removeItem('client_token')
          cartStore.clearClient()
          if (axios.defaults?.headers?.common?.Authorization) {
            delete axios.defaults.headers.common['Authorization']
          }
        }
      }
    }
  }
}
</script>

<style>
#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

main {
  flex: 1;
}
</style>

