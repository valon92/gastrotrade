<template>
  <footer class="bg-gray-800 text-white">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Company Info -->
        <div>
          <h3 class="text-lg font-semibold mb-4">GastroTrade</h3>
          <p class="text-gray-300 mb-4">
            Zgjidhja juaj për furnizimet profesionale të kuzhinës. 
            Cilësi e lartë dhe shërbim i besueshëm.
          </p>
        </div>
        
        <!-- Quick Links -->
        <div>
          <h3 class="text-lg font-semibold mb-4">Lidhje të Shpejta</h3>
          <ul class="space-y-2">
            <li>
              <router-link to="/" class="text-gray-300 hover:text-white transition-colors duration-200">
                Ballina
              </router-link>
            </li>
            <li>
              <router-link to="/produktet" class="text-gray-300 hover:text-white transition-colors duration-200">
                Produktet
              </router-link>
            </li>
            <li>
              <router-link to="/rreth-nesh" class="text-gray-300 hover:text-white transition-colors duration-200">
                Rreth Nesh
              </router-link>
            </li>
            <li>
              <router-link to="/kontakt" class="text-gray-300 hover:text-white transition-colors duration-200">
                Kontakt
              </router-link>
            </li>
          </ul>
        </div>
        
        <!-- Contact Info -->
        <div>
          <h3 class="text-lg font-semibold mb-4">Kontakt</h3>
          <div class="space-y-2 text-gray-300">
            <p>📧 svalon95@gmail.com</p>
            <p>📞 048 75 66 46</p>
            <p>📞 044 82 43 14</p>
            <p>📍 Ferizaj, Kosovë</p>
            <p>📍 Rruga Lidhja E Prizerent</p>
          </div>
        </div>
      </div>
      
      <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
        <p>&copy; {{ currentYear }} GastroTrade. Të gjitha të drejtat e rezervuara.</p>
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
