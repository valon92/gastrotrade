<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2 text-center">
        Kyçu
      </h1>
      <p class="text-center text-gray-600 mb-8">
        Identifikohu me email dhe fjalëkalim për të parë çmimet tuaja në shportë.
      </p>

      <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
        <div class="text-sm text-gray-600 bg-blue-50 border border-blue-100 rounded-lg p-3 mb-6">
          <p class="mb-3">
            <strong>Nuk keni llogari?</strong> <router-link to="/regjistrohu" class="font-medium text-primary-600 hover:underline">Regjistrohuni</router-link> vetëm me email dhe fjalëkalim.
          </p>
          <router-link
            to="/regjistrohu"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors"
          >
            Regjistrohu
          </router-link>
        </div>

        <div v-if="isClientIdentified" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex flex-wrap items-center justify-between gap-3">
          <p class="text-sm text-green-800">
            ✅ I identifikuar: <strong>{{ displayName }}</strong>.<span v-if="hasClientPrices"> Çmimet tuaja aplikohen në shportë.</span>
          </p>
          <button
            type="button"
            @click="logoutClient"
            class="shrink-0 px-3 py-1.5 text-sm font-medium text-green-800 bg-white border border-green-300 rounded-lg hover:bg-green-100 transition-colors"
          >
            Dil
          </button>
        </div>
        <div v-else-if="identifyingClient" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <p class="text-sm text-blue-800">Duke u identifikuar...</p>
        </div>
        <div v-else-if="loginError" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-sm text-red-800">{{ loginError }}</p>
        </div>

        <form v-if="!isClientIdentified" @submit.prevent="loginWithEmail" class="space-y-4">
          <div>
            <label for="kycuEmail" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
            <input
              id="kycuEmail"
              v-model="email"
              type="email"
              required
              autocomplete="email"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              placeholder="email@shembull.com"
            />
          </div>
          <div>
            <label for="kycuPassword" class="block text-sm font-medium text-gray-700 mb-1">Fjalëkalimi <span class="text-red-500">*</span></label>
            <div class="relative">
              <input
                id="kycuPassword"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                required
                autocomplete="current-password"
                class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Fjalëkalimi juaj"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-500 hover:text-gray-700 rounded"
                :aria-label="showPassword ? 'Fshih fjalëkalimin' : 'Shfaq fjalëkalimin'"
              >
                <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </div>
          </div>
          <button
            type="submit"
            :disabled="identifyingClient"
            class="w-full px-6 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 disabled:opacity-50 transition-colors"
          >
            Kyçu
          </button>
        </form>

        <div class="mt-8 flex flex-wrap gap-3">
          <router-link to="/shporta" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-800 font-medium rounded-lg hover:bg-gray-200 transition-colors">
            Shiko shportën
          </router-link>
          <router-link to="/produktet" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors">
            Shiko produktet
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import cartStore from '../store/cart'
import axios from 'axios'

export default {
  name: 'Kycu',
  data() {
    return {
      cartStore,
      email: '',
      password: '',
      showPassword: false,
      identifyingClient: false,
      loginError: null
    }
  },
  computed: {
    isClientIdentified() {
      return !!this.cartStore.client
    },
    hasClientPrices() {
      return this.cartStore.client && Object.keys(this.cartStore.clientPrices).length > 0
    },
    displayName() {
      if (!this.cartStore.client) return ''
      const c = this.cartStore.client
      return c.name || c.store_name || c.email || 'Klient'
    }
  },
  mounted() {
    window.scrollTo({ top: 0, behavior: 'smooth' })
  },
  methods: {
    async loginWithEmail() {
      if (!this.email?.trim() || !this.password) {
        this.loginError = 'Vendosni email dhe fjalëkalim.'
        return
      }
      this.identifyingClient = true
      this.loginError = null
      try {
        const res = await axios.post('/api/client/login', {
          email: this.email.trim(),
          password: (this.password || '').trim()
        })
        if (res.data.success && res.data.data?.client) {
          await this.cartStore.setClient(res.data.data.client)
          localStorage.setItem('client_token', res.data.data.token)
          if (window.axios?.defaults?.headers?.common) {
            window.axios.defaults.headers.common['Authorization'] = `Bearer ${res.data.data.token}`
          }
          this.$router.push('/shporta')
        }
      } catch (e) {
        this.loginError = e.response?.data?.message || 'Emaili ose fjalëkalimi është i gabuar.'
        this.cartStore.clearClient()
      } finally {
        this.identifyingClient = false
      }
    },
    logoutClient() {
      this.cartStore.logoutSession()
      this.loginError = null
      this.email = ''
      this.password = ''
    }
  }
}
</script>
