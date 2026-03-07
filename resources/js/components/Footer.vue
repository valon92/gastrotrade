<template>
  <!-- Footer AronTrade - ngjyrë e zezë -->
  <footer class="bg-black text-white">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Company Info -->
        <div>
          <h3 class="text-lg font-semibold mb-4">AronTrade</h3>
          <p class="text-gray-400 mb-4">
            Zgjidhja juaj për furnizimet profesionale të biznesit. 
            Cilësi e lartë dhe shërbim i besueshëm.
          </p>
        </div>
        
        <!-- Quick Links -->
        <div>
          <h3 class="text-lg font-semibold mb-4">Lidhje të Shpejta</h3>
          <ul class="space-y-2">
            <li>
              <router-link to="/" class="text-gray-400 hover:text-white transition-colors duration-200">
                Ballina
              </router-link>
            </li>
            <li>
              <router-link to="/produktet" class="text-gray-400 hover:text-white transition-colors duration-200">
                Produktet
              </router-link>
            </li>
            <li>
              <router-link to="/rreth-nesh" class="text-gray-400 hover:text-white transition-colors duration-200">
                Rreth Nesh
              </router-link>
            </li>
            <li>
              <router-link to="/kontakt" class="text-gray-400 hover:text-white transition-colors duration-200">
                Kontakt
              </router-link>
            </li>
          </ul>
        </div>
        
        <!-- Contact Info -->
        <div>
          <h3 class="text-lg font-semibold mb-4">Kontakt</h3>
          <div class="space-y-2 text-gray-400">
            <p>📧 svalon95@gmail.com</p>
            <p>📞 048 75 66 46</p>
            <p>📞 044 82 43 14</p>
            <p>📍 Ferizaj, Kosovë</p>
            <p>📍 Rruga Lidhja E Prizerent</p>
          </div>
        </div>
      </div>
      
      <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
        <p>&copy; {{ currentYear }} AronTrade. Të gjitha të drejtat e rezervuara.</p>
        <div class="mt-4">
          <router-link 
            to="/admin/login" 
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-300 hover:text-white bg-gray-800 hover:bg-gray-700 rounded-md transition-colors duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            Admin Panel
          </router-link>
        </div>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'Footer',
  computed: {
    currentYear() {
      return new Date().getFullYear()
    }
  }
}
</script>

<style scoped>
footer {
  background-color: #000000;
}
</style>
