<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2 text-center">
        Regjistrohu
      </h1>
      <p class="text-center text-gray-600 mb-8">
        Vetëm email dhe fjalëkalim. Pas regjistrimit mund të identifikoheni për të bërë porosi.
      </p>

      <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
        <div v-if="success" class="p-4 bg-green-50 border border-green-200 rounded-lg">
          <p class="text-green-800 font-medium">{{ success }}</p>
          <p class="text-sm text-green-700 mt-1">Tani mund të <router-link to="/kycu" class="underline font-medium">identifikoheni</router-link> me këtë email dhe fjalëkalim.</p>
          <router-link to="/shporta" class="inline-block mt-3 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
            Shko te shporta
          </router-link>
        </div>

        <form v-else @submit.prevent="register" class="space-y-4">
          <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
            {{ error }}
          </div>
          <div v-if="Object.keys(errors).length" class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
            <ul class="list-disc list-inside space-y-1">
              <li v-for="(msgs, field) in errors" :key="field">
                {{ Array.isArray(msgs) ? msgs[0] : msgs }}
              </li>
            </ul>
          </div>

          <div>
            <label for="reg-email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
            <input
              id="reg-email"
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              placeholder="email@shembull.com"
            />
          </div>
          <div>
            <label for="reg-password" class="block text-sm font-medium text-gray-700 mb-1">Fjalëkalimi <span class="text-red-500">*</span></label>
            <input
              id="reg-password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              minlength="6"
              autocomplete="new-password"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              placeholder="Të paktën 6 karaktere"
            />
          </div>
          <div>
            <label for="reg-password-confirm" class="block text-sm font-medium text-gray-700 mb-1">Konfirmo fjalëkalimin <span class="text-red-500">*</span></label>
            <input
              id="reg-password-confirm"
              v-model="form.password_confirmation"
              :type="showPassword ? 'text' : 'password'"
              required
              autocomplete="new-password"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              placeholder="Përsëritni fjalëkalimin"
            />
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="text-sm text-gray-600 hover:text-gray-800"
            >
              {{ showPassword ? 'Fshih' : 'Shfaq' }} fjalëkalimin
            </button>
          </div>

          <div class="pt-2 flex flex-col gap-3">
            <button
              type="submit"
              :disabled="loading"
              class="w-full px-6 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ loading ? 'Duke u regjistruar...' : 'Regjistrohu' }}
            </button>
            <router-link
              to="/kycu"
              class="block text-center text-sm text-primary-600 hover:underline"
            >
              Keni llogari? Identifikohu
            </router-link>
          </div>
        </form>
      </div>

      <p class="text-center text-sm text-gray-500 mt-6">
        <router-link to="/" class="text-primary-600 hover:underline">Kthehu në faqen kryesore</router-link>
      </p>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import cartStore from '../store/cart'

export default {
  name: 'Regjistrohu',
  data() {
    return {
      form: {
        email: '',
        password: '',
        password_confirmation: ''
      },
      loading: false,
      error: '',
      errors: {},
      success: '',
      showPassword: false
    }
  },
  methods: {
    async register() {
      this.loading = true
      this.error = ''
      this.errors = {}
      try {
        const res = await axios.post('/api/client/register', this.form)
        if (res.data.success && res.data.data?.token) {
          const token = res.data.data.token
          const client = res.data.data.client
          localStorage.setItem('client_token', token)
          if (window.axios?.defaults?.headers?.common) {
            window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
          }
          if (client) await cartStore.setClient(client)
          this.success = res.data.message || 'U regjistruat me sukses.'
          await this.$nextTick()
          await this.$router.push('/shporta')
        }
      } catch (e) {
        if (e.response?.data?.errors) {
          this.errors = e.response.data.errors
        }
        if (e.response?.data?.message) {
          this.error = e.response.data.message
        }
        if (!this.error && Object.keys(this.errors).length === 0 && e.message) {
          this.error = 'Regjistrimi dështoi. Provoni përsëri.'
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
