<template>
  <nav class="bg-white shadow-lg sticky top-0 z-50 overflow-x-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full min-w-0">
      <div class="flex justify-between items-center h-16 gap-2 min-w-0">
        <div class="flex items-center min-w-0 flex-shrink-0">
          <router-link to="/" class="flex-shrink-0 flex items-center gap-3">
            <!-- AT Logo - Aron Trade -->
            <svg class="w-10 h-10 flex-shrink-0 text-primary-600" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <rect width="40" height="40" rx="8" fill="currentColor"/>
              <text x="20" y="26" text-anchor="middle" fill="white" font-family="system-ui, sans-serif" font-weight="700" font-size="16">AT</text>
            </svg>
            <h1 class="text-2xl font-bold text-primary-600">AronTrade</h1>
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
            to="/kycu" 
            class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200"
            active-class="text-primary-600"
          >
            KYÇU
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
        
        <!-- Mobile: shporta në navbar, brenda margjinave -->
        <div class="md:hidden flex items-center gap-2 flex-shrink-0 min-w-0">
          <router-link
            to="/shporta"
            class="relative p-2 rounded-full text-gray-700 hover:text-primary-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors"
            aria-label="Shporta"
          >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span
              v-if="cartItemCount > 0"
              class="absolute -top-0.5 -right-0.5 bg-primary-600 text-white text-xs font-bold rounded-full min-w-[1.25rem] h-5 px-1 flex items-center justify-center"
            >
              {{ cartItemCount > 99 ? '99+' : cartItemCount }}
            </span>
          </router-link>
          <button
            @click="toggleMobileMenu"
            class="p-2 rounded-full text-gray-700 hover:text-primary-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors"
            aria-label="Hap menynë"
          >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
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
          to="/kycu" 
          class="text-gray-700 hover:text-primary-600 block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200"
          @click="closeMobileMenu"
        >
          KYÇU
        </router-link>
        <router-link 
          to="/shporta" 
          class="flex items-center justify-between text-gray-700 hover:text-primary-600 block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200"
          @click="closeMobileMenu"
        >
          <span>🛒 SHPORTA</span>
          <span v-if="cartItemCount > 0" class="bg-primary-600 text-white text-xs font-bold rounded-full min-w-[1.25rem] h-5 px-1.5 flex items-center justify-center">{{ cartItemCount > 99 ? '99+' : cartItemCount }}</span>
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
