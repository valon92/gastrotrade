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

export default {
  name: 'App',
  components: {
    Navbar,
    Footer,
    BackToTop
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

