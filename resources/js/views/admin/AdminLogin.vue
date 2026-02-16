<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-4xl font-extrabold text-gray-900">
          Admin Login
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Hyni në panelin e administrimit
        </p>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="login">
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="email" class="sr-only">Email</label>
            <input
              id="email"
              v-model="form.email"
              name="email"
              type="email"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
              placeholder="Email adresa"
            />
          </div>
          <div>
            <label for="password" class="sr-only">Fjalëkalimi</label>
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
              placeholder="Fjalëkalimi"
            />
          </div>
        </div>

        <div v-if="error" class="rounded-md bg-red-50 p-4">
          <div class="text-sm text-red-800">
            {{ error }}
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
          >
            <span v-if="loading">Duke u kyçur...</span>
            <span v-else>Hyr</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'AdminLogin',
  data() {
    return {
      form: {
        email: '',
        password: ''
      },
      loading: false,
      error: null
    }
  },
  async mounted() {
    // Check if already logged in
    await this.checkAuth()
  },
  methods: {
    async checkAuth() {
      try {
        const token = localStorage.getItem('admin_token')
        if (token) {
          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
          const response = await axios.get('/api/admin/check')
          if (response.data.success) {
            // Load user data with role
            const { adminStore } = await import('../../stores/adminStore')
            adminStore.setUser(response.data.data.user)
            
            // Redirect based on role
            if (response.data.data.user.role === 'admin') {
              this.$router.push('/admin/clients')
            } else {
              this.$router.push('/admin/sales')
            }
          }
        }
      } catch (error) {
        // Not authenticated, stay on login page
        localStorage.removeItem('admin_token')
        delete axios.defaults.headers.common['Authorization']
        const { adminStore } = await import('../../stores/adminStore')
        adminStore.clearUser()
      }
    },
    async login() {
      this.loading = true
      this.error = null

      try {
        const response = await axios.post('/api/admin/login', this.form)
        
        if (response.data.success) {
          const token = response.data.data.token
          const userData = response.data.data.user
          localStorage.setItem('admin_token', token)
          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
          
          // Store user data with role
          const { adminStore } = await import('../../stores/adminStore')
          adminStore.setUser(userData)
          
          // Redirect based on role
          if (userData.role === 'admin') {
            this.$router.push('/admin/clients')
          } else {
            // Order managers can only access sales/orders
            this.$router.push('/admin/sales')
          }
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Gabim në kyçje. Ju lutem provoni përsëri.'
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

