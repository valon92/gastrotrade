<template>
  <nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex items-center">
          <router-link to="/" class="flex-shrink-0 flex items-center">
            <h1 class="text-2xl font-bold text-primary-600">GastroTrade</h1>
          </router-link>
        </div>
        
        <div class="hidden md:flex items-center space-x-8">
          <router-link 
            to="/" 
            class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            active-class="text-primary-600"
          >
            HOME
          </router-link>
          <router-link 
            to="/produktet" 
            class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            active-class="text-primary-600"
          >
            PRODUKTET
          </router-link>
          <router-link 
            to="/rreth-nesh" 
            class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            active-class="text-primary-600"
          >
            RRETH NESH
          </router-link>
          <router-link 
            to="/kontakt" 
            class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            active-class="text-primary-600"
          >
            KONTAKT
          </router-link>
          <router-link 
            to="/shporta" 
            class="relative text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            active-class="text-primary-600"
          >
            🛒 SHPORTA
            <span 
              v-if="cartItemCount > 0"
              class="absolute -top-1 -right-1 bg-primary-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"
            >
              {{ cartItemCount }}
            </span>
          </router-link>
        </div>
        
        <!-- Mobile menu button -->
        <div class="md:hidden flex items-center">
          <button 
            @click="toggleMobileMenu"
            class="text-gray-700 hover:text-primary-600 focus:outline-none focus:text-primary-600"
          >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>
    
    <!-- Mobile menu -->
    <div v-show="mobileMenuOpen" class="md:hidden">
      <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-white border-t">
        <router-link 
          to="/" 
          class="text-gray-700 hover:text-primary-600 block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200"
          @click="closeMobileMenu"
        >
          HOME
        </router-link>
        <router-link 
          to="/produktet" 
          class="text-gray-700 hover:text-primary-600 block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200"
          @click="closeMobileMenu"
        >
          PRODUKTET
        </router-link>
        <router-link 
          to="/rreth-nesh" 
          class="text-gray-700 hover:text-primary-600 block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200"
          @click="closeMobileMenu"
        >
          RRETH NESH
        </router-link>
        <router-link 
          to="/kontakt" 
          class="text-gray-700 hover:text-primary-600 block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200"
          @click="closeMobileMenu"
        >
          KONTAKT
        </router-link>
        <router-link 
          to="/shporta" 
          class="relative text-gray-700 hover:text-primary-600 block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200"
          @click="closeMobileMenu"
        >
          🛒 SHPORTA
          <span 
            v-if="cartItemCount > 0"
            class="ml-2 bg-primary-600 text-white text-xs font-bold rounded-full h-5 w-5 inline-flex items-center justify-center"
          >
            {{ cartItemCount }}
          </span>
        </router-link>
      </div>
    </div>
  </nav>
</template>

<script>
import cartStore from '../store/cart'

export default {
  name: 'Navbar',
  data() {
    return {
      mobileMenuOpen: false,
      cartStore: cartStore
    }
  },
  computed: {
    cartItemCount() {
      return this.cartStore.totalItems
    }
  },
  methods: {
    toggleMobileMenu() {
      this.mobileMenuOpen = !this.mobileMenuOpen
    },
    closeMobileMenu() {
      this.mobileMenuOpen = false
    }
  }
}
</script>
