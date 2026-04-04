<template>
  <nav ref="navRef" class="sticky top-0 z-[1000] overflow-visible relative border-b border-slate-200/80 bg-white/85 shadow-[0_8px_30px_rgba(2,6,23,0.06)] backdrop-blur-xl">
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[linear-gradient(120deg,rgba(20,184,166,0.09),transparent_28%,transparent_72%,rgba(20,184,166,0.07))]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full min-w-0 relative">
      <div class="flex justify-between items-center h-16 gap-2 min-w-0">
        <div class="flex items-center min-w-0 flex-shrink-0">
          <router-link to="/" class="flex-shrink-0 flex items-center gap-3">
            <!-- AT Logo - Aron Trade -->
            <svg class="w-10 h-10 flex-shrink-0 text-primary-600 drop-shadow-sm" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <rect width="40" height="40" rx="11" fill="currentColor"/>
              <text x="20" y="26" text-anchor="middle" fill="white" font-family="system-ui, sans-serif" font-weight="700" font-size="16">AT</text>
            </svg>
            <div class="min-w-0">
              <h1 class="text-2xl leading-none font-extrabold tracking-tight text-primary-700">AronTrade</h1>
              <p class="hidden lg:flex items-center gap-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500 mt-1">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                B2B Furnizime Profesionale
              </p>
            </div>
          </router-link>
        </div>

        <div class="hidden md:flex items-center gap-3">
          <div class="flex items-center gap-1 rounded-2xl border border-slate-200 bg-white/80 p-1 shadow-sm">
          <router-link
            to="/"
            class="text-slate-600 hover:text-primary-700 hover:bg-primary-50 px-3 py-2 rounded-xl text-sm font-semibold transition-colors duration-200"
            active-class="text-primary-700 bg-primary-50"
            exact-active-class="text-primary-700 bg-primary-50"
          >
            HOME
          </router-link>
          <router-link
            to="/produktet"
            class="text-slate-600 hover:text-primary-700 hover:bg-primary-50 px-3 py-2 rounded-xl text-sm font-semibold transition-colors duration-200"
            active-class="text-primary-700 bg-primary-50"
          >
            PRODUKTET
          </router-link>
          <router-link
            to="/kontakt"
            class="text-slate-600 hover:text-primary-700 hover:bg-primary-50 px-3 py-2 rounded-xl text-sm font-semibold transition-colors duration-200"
            active-class="text-primary-700 bg-primary-50"
          >
            KONTAKT
          </router-link>
          </div>
          <template v-if="isClientIdentified">
            <div ref="clientMenuRef" class="flex items-center gap-2">
              <button
                type="button"
                @click.stop="toggleMobileMenu"
                class="flex items-center gap-2 text-slate-700 hover:text-primary-700 px-3 py-2 rounded-xl text-sm font-semibold transition-all duration-200 border border-primary-200 bg-gradient-to-b from-primary-50 to-white hover:from-primary-100 hover:to-primary-50 shadow-sm"
                aria-label="Hap menynë e llogarisë"
                aria-expanded="mobileMenuOpen"
              >
                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary-600 text-white text-xs font-bold">✓</span>
                <span>{{ clientDisplayNameForMenu }}</span>
                <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': mobileMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
            </div>
          </template>
          <template v-else>
            <router-link
              to="/kycu"
              class="text-slate-700 hover:text-primary-700 px-3 py-2 rounded-lg text-sm font-semibold transition-colors duration-200"
              active-class="text-primary-600"
            >
              KYÇU
            </router-link>
            <router-link
              to="/shporta"
              class="relative inline-flex items-center gap-2 bg-primary-600 text-white px-3.5 py-2 rounded-xl text-sm font-semibold hover:bg-primary-700 shadow-sm transition-colors duration-200"
              active-class="text-primary-600"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              SHPORTA
              <span
                v-if="cartItemCount > 0"
                class="absolute -top-1 -right-1 bg-white text-primary-700 text-xs font-bold rounded-full h-5 min-w-5 px-1 flex items-center justify-center"
              >
                {{ cartItemCount }}
              </span>
            </router-link>
          </template>
        </div>

        <div class="md:hidden flex items-center gap-2 flex-shrink-0 min-w-0">
          <router-link
            to="/shporta"
            class="relative p-2.5 rounded-xl text-gray-700 hover:text-primary-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors border border-transparent hover:border-slate-200"
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
            class="p-2.5 rounded-xl text-gray-700 hover:text-primary-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors border border-transparent hover:border-slate-200"
            :aria-expanded="mobileMenuOpen"
            aria-label="Hap menynë"
          >
            <span class="relative flex h-6 w-6 items-center justify-center">
              <span
                class="block h-0.5 w-5 rounded-full bg-current transition-all duration-300"
                :class="mobileMenuOpen ? 'rotate-45 translate-y-1' : '-translate-y-1.5'"
              />
              <span class="block h-0.5 w-5 rounded-full bg-current transition-all duration-300" :class="mobileMenuOpen ? 'opacity-0' : ''" />
              <span
                class="block h-0.5 w-5 rounded-full bg-current transition-all duration-300"
                :class="mobileMenuOpen ? '-rotate-45 -translate-y-1' : 'translate-y-1.5'"
              />
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Backdrop: në desktop nuk mbulon navbar-in që butoni "✓ Llogaria" të mbetet i klikueshëm -->
    <Transition name="backdrop">
      <div
        v-show="mobileMenuOpen"
        class="fixed inset-0 z-[1000] bg-black/20 backdrop-blur-sm md:bg-black/10 md:top-16"
        aria-hidden="true"
        @click="closeMobileMenu"
      />
    </Transition>

    <!-- Menyja e burgerit: në mobile – navigacion + llogari; në desktop – vetëm llogari -->
    <Transition name="menu">
      <div
        v-show="mobileMenuOpen"
        class="fixed md:absolute top-16 md:top-full left-0 right-0 z-[1001] md:left-auto md:right-4 md:mt-2 md:max-w-sm md:rounded-2xl md:shadow-2xl bg-white border-t md:border border-gray-200 shadow-lg max-h-[85vh] overflow-y-auto"
        role="dialog"
        aria-label="Menyja kryesore"
        @click.stop
      >
        <div class="px-3 py-4 md:py-3">
          <!-- Navigacion: vetëm në mobile (në desktop lidhjet janë në navbar) -->
          <div class="md:hidden space-y-0.5 pb-4 border-b border-gray-100">
            <router-link
              to="/"
              class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-primary-50 hover:text-primary-700 text-sm font-medium transition-colors"
              @click="closeMobileMenu"
            >
              <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
              </span>
              HOME
            </router-link>
            <router-link
              to="/produktet"
              class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-primary-50 hover:text-primary-700 text-sm font-medium transition-colors"
              @click="closeMobileMenu"
            >
              <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8 4-8-4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
              </span>
              PRODUKTET
            </router-link>
            <router-link
              to="/kontakt"
              class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-primary-50 hover:text-primary-700 text-sm font-medium transition-colors"
              @click="closeMobileMenu"
            >
              <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
              </span>
              KONTAKT
            </router-link>
          </div>

          <template v-if="isClientIdentified">
            <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary-50 border border-primary-100 mb-3">
              <span class="flex items-center justify-center w-9 h-9 rounded-full bg-primary-600 text-white text-sm font-bold shrink-0">✓</span>
              <div class="min-w-0">
                <p class="text-sm font-semibold text-primary-800">I identifikuar</p>
                <p class="text-xs text-primary-600 truncate">{{ clientDisplayNameForMenu }}</p>
              </div>
            </div>
            <router-link
              to="/shporta"
              class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors mb-1"
              @click="closeMobileMenu"
            >
              <span class="flex items-center gap-3">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </span>
                <span class="text-sm font-medium">Shporta</span>
              </span>
              <span v-if="cartItemCount > 0" class="bg-primary-600 text-white text-xs font-bold rounded-full min-w-[1.25rem] h-5 px-1.5 flex items-center justify-center">{{ cartItemCount > 99 ? '99+' : cartItemCount }}</span>
            </router-link>
            <router-link
              to="/shporta"
              class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors mb-1"
              @click="openChangePasswordThenGoToCart"
            >
              <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
              </span>
              <span class="text-sm font-medium">Ndrysho fjalëkalimin</span>
            </router-link>
            <button
              type="button"
              @click="logoutClientMobile"
              class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-red-600 hover:bg-red-50 transition-colors mt-2 border-t border-gray-100 pt-3"
            >
              <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
              </span>
              <span class="text-sm font-medium">Dil</span>
            </button>
          </template>
          <template v-else>
            <div class="space-y-0.5">
              <router-link
                to="/kycu"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-primary-50 hover:text-primary-700 text-sm font-medium transition-colors"
                @click="closeMobileMenu"
              >
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                </span>
                KYÇU
              </router-link>
              <router-link
                to="/shporta"
                class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                @click="closeMobileMenu"
              >
                <span class="flex items-center gap-3">
                  <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                  </span>
                  <span class="text-sm font-medium">SHPORTA</span>
                </span>
                <span v-if="cartItemCount > 0" class="bg-primary-600 text-white text-xs font-bold rounded-full min-w-[1.25rem] h-5 px-1.5 flex items-center justify-center">{{ cartItemCount > 99 ? '99+' : cartItemCount }}</span>
              </router-link>
            </div>
          </template>
        </div>
      </div>
    </Transition>
  </nav>
</template>

<script>
import cartStore from '../store/cart'

export default {
  name: 'Navbar',
  data() {
    return {
      mobileMenuOpen: false,
      clientMenuOpen: false,
      cartStore: cartStore
    }
  },
  computed: {
    cartItemCount() {
      return this.cartStore.totalItems
    },
    isClientIdentified() {
      return !!this.cartStore.client
    },
    clientDisplayName() {
      if (!this.cartStore.client) return ''
      const c = this.cartStore.client
      return c.name || c.store_name || c.email || 'Klient'
    },
    // Për menynë: nuk shfaqim kurrë email (vetëm emër ose emër biznesi)
    clientDisplayNameForMenu() {
      if (!this.cartStore.client) return ''
      const c = this.cartStore.client
      if (c.name && c.name.trim()) return c.name.trim()
      if (c.store_name && c.store_name.trim()) return c.store_name.trim()
      return 'Llogaria'
    }
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutsideClientMenu)
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutsideClientMenu)
  },
  methods: {
    handleClickOutsideClientMenu(e) {
      if (!this.mobileMenuOpen) return
      if (!this.$refs.navRef || this.$refs.navRef.contains(e.target)) return
      this.mobileMenuOpen = false
      this.clientMenuOpen = false
    },
    toggleMobileMenu() {
      this.mobileMenuOpen = !this.mobileMenuOpen
    },
    closeMobileMenu() {
      this.mobileMenuOpen = false
      this.clientMenuOpen = false
    },
    openChangePasswordThenGoToCart() {
      this.clientMenuOpen = false
      this.mobileMenuOpen = false
      this.$router.push({ path: '/shporta', query: { ndrysho: '1' } })
    },
    logoutClient() {
      this.clientMenuOpen = false
      this.cartStore.logoutSession()
    },
    logoutClientMobile() {
      this.logoutClient()
      this.closeMobileMenu()
    }
  }
}
</script>

<style scoped>
.backdrop-enter-active,
.backdrop-leave-active {
  transition: opacity 0.2s ease;
}
.backdrop-enter-from,
.backdrop-leave-to {
  opacity: 0;
}

.menu-enter-active,
.menu-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.menu-enter-from,
.menu-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
@media (min-width: 768px) {
  .menu-enter-from,
  .menu-leave-to {
    transform: translateY(-4px) scale(0.98);
  }
}
</style>
