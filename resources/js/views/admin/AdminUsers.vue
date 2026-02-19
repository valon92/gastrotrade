<template>
  <AdminLayout>
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div class="flex-1">
          <h1 class="text-2xl sm:text-4xl font-bold text-gray-900 mb-4">Menaxhimi i Adminave</h1>
          <button 
            @click="showAddModal = true"
            class="btn-primary w-full sm:w-auto"
          >
            + Shto Admin të Ri
          </button>
        </div>
      </div>

      <!-- Search and Filters -->
      <div class="mb-6 bg-white p-4 rounded-lg shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Kërko</label>
            <input
              v-model="filters.search"
              @input="debounceSearch"
              type="text"
              placeholder="Kërko sipas emrit ose email..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Filtro sipas rolit</label>
            <select
              v-model="filters.role"
              @change="loadUsers"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            >
              <option value="">Të gjithë</option>
              <option value="admin">Admin (Menaxhim i plotë)</option>
              <option value="order_manager">Order Manager (Vetëm porosi)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        <p class="mt-2 text-gray-600">Duke ngarkuar...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <p class="text-red-800">{{ error }}</p>
      </div>

      <!-- Users List -->
      <div v-else class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emri</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roli</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data e Krijimit</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Veprime</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ user.id }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ user.email }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    :class="[
                      'px-2 py-1 text-xs font-semibold rounded-full',
                      user.role === 'admin' 
                        ? 'bg-purple-100 text-purple-800' 
                        : 'bg-blue-100 text-blue-800'
                    ]"
                  >
                    {{ user.role === 'admin' ? 'Admin (Menaxhim i plotë)' : 'Order Manager (Vetëm porosi)' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(user.created_at) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click="editUser(user)"
                    class="text-indigo-600 hover:text-indigo-900 mr-4"
                  >
                    ✏️ Ndrysho
                  </button>
                  <button
                    @click="confirmDelete(user)"
                    :disabled="user.id === currentUserId"
                    :class="[
                      'text-red-600 hover:text-red-900',
                      user.id === currentUserId ? 'opacity-50 cursor-not-allowed' : ''
                    ]"
                  >
                    🗑️ Fshi
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="users.length === 0" class="text-center py-12">
          <p class="text-gray-500">Nuk ka admina të regjistruar</p>
        </div>
      </div>

      <!-- Add/Edit Modal -->
      <div v-if="showAddModal || editingUser" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-4 sm:top-20 mx-auto p-4 sm:p-5 border w-full sm:w-96 max-w-2xl shadow-lg rounded-md bg-white m-4 max-h-[90vh] overflow-y-auto">
          <h3 class="text-lg font-bold text-gray-900 mb-4">
            {{ editingUser ? 'Ndrysho Admin' : 'Shto Admin të Ri' }}
          </h3>
          <form @submit.prevent="saveUser">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Emri *</label>
                <input 
                  v-model="userForm.name" 
                  type="text" 
                  required 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  placeholder="Shkruani emrin"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input 
                  v-model="userForm.email" 
                  type="email" 
                  required 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  placeholder="email@example.com"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Fjalëkalimi {{ editingUser ? '(Lëreni bosh për të mos e ndryshuar)' : '*' }}
                </label>
                <input 
                  v-model="userForm.password" 
                  type="password" 
                  :required="!editingUser"
                  :minlength="editingUser ? 0 : 8"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  placeholder="Minimum 8 karaktere"
                />
                <p v-if="editingUser" class="mt-1 text-xs text-gray-500">
                  Lëreni bosh për të mos e ndryshuar fjalëkalimin
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Roli *</label>
                <select
                  v-model="userForm.role"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
                  <option value="order_manager">Order Manager (Vetëm porosi)</option>
                  <option value="admin">Admin (Menaxhim i plotë)</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">
                  <strong>Order Manager:</strong> Mund të shohë dhe printojë faktura<br>
                  <strong>Admin:</strong> Ka të drejtë për menaxhimin e plotë
                </p>
              </div>
            </div>
            <div class="mt-6 flex gap-3">
              <button
                type="submit"
                :disabled="saving"
                class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50"
              >
                {{ saving ? 'Duke ruajtur...' : (editingUser ? 'Përditëso' : 'Krijo') }}
              </button>
              <button
                type="button"
                @click="closeModal"
                class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300"
              >
                Anulo
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="userToDelete" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
          <h3 class="text-lg font-bold text-gray-900 mb-4">Konfirmo Fshirjen</h3>
          <p class="text-gray-700 mb-6">
            Jeni të sigurt që dëshironi të fshini adminin <strong>{{ userToDelete.name }}</strong> ({{ userToDelete.email }})?
          </p>
          <div class="flex gap-3">
            <button
              @click="deleteUser"
              :disabled="deleting"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
            >
              {{ deleting ? 'Duke fshirë...' : 'Fshi' }}
            </button>
            <button
              @click="userToDelete = null"
              class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300"
            >
              Anulo
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import axios from 'axios'
import { adminStore } from '../../stores/adminStore'
import AdminLayout from '../../components/admin/AdminLayout.vue'

export default {
  name: 'AdminUsers',
  components: {
    AdminLayout
  },
  data() {
    return {
      users: [],
      loading: false,
      error: null,
      showAddModal: false,
      editingUser: null,
      userToDelete: null,
      saving: false,
      deleting: false,
      filters: {
        search: '',
        role: ''
      },
      userForm: {
        name: '',
        email: '',
        password: '',
        role: 'order_manager'
      },
      searchTimeout: null,
      currentUserId: null
    }
  },
  async mounted() {
    // Scroll to top when component mounts
    window.scrollTo({ top: 0, behavior: 'smooth' })
    
    console.log('[AdminUsers] mounted start')
    // Load current user ID
    adminStore.loadUser()
    if (adminStore.user) {
      this.currentUserId = adminStore.user.id
    } else {
      try {
        const response = await axios.get('/api/admin/check')
        if (response.data.success) {
          adminStore.setUser(response.data.data.user)
          this.currentUserId = response.data.data.user.id
        } else {
          // Not authenticated
          this.$router.push('/admin/login')
          return
        }
      } catch (error) {
        console.error('Error loading user data:', error)
        this.$router.push('/admin/login')
        return
      }
    }

    // Check if user has permission
    if (!adminStore.canManage()) {
      console.warn('[AdminUsers] Access denied - user is not admin. Redirecting to dashboard')
      this.$router.push('/admin/dashboard')
      return
    }

    await this.loadUsers()
    console.log('[AdminUsers] mounted done, users:', this.users.length)
  },
  methods: {
    setupAxiosInterceptor() {
      axios.interceptors.request.use(config => {
        const adminToken = localStorage.getItem('admin_token')
        if (adminToken) {
          config.headers.Authorization = `Bearer ${adminToken}`
        }
        console.debug('AdminUsers request', config.url, config.params || '')
        return config
      })
    },
    async loadUsers() {
      this.loading = true
      this.error = null
      this.setupAxiosInterceptor()

      try {
        const params = {}
        if (this.filters.search) {
          params.search = this.filters.search
        }
        if (this.filters.role) {
          params.role = this.filters.role
        }

        const response = await axios.get('/api/admin/users', { params })
        if (response.data.success) {
          this.users = response.data.data
        } else {
          console.warn('Admin users load returned success=false', response.data)
          this.error = response.data.message || 'Nuk u kthyen të dhënat e adminëve'
        }
      } catch (error) {
        console.error('Error loading users:', error)
        this.error = error.response?.data?.message || error.message || 'Gabim gjatë ngarkimit të adminave'
        if (error.response?.status === 403) {
          this.$router.push('/admin/sales')
          return
        }
        if (error.response?.status === 401) {
          adminStore.clearUser()
          localStorage.removeItem('admin_token')
          delete axios.defaults.headers.common['Authorization']
          this.$router.push('/admin/login')
          return
        } else {
          console.warn('Admin users load non-401/403 error', error.response?.data || error)
        }
      } finally {
        this.loading = false
      }
    },
    debounceSearch() {
      clearTimeout(this.searchTimeout)
      this.searchTimeout = setTimeout(() => {
        this.loadUsers()
      }, 500)
    },
    editUser(user) {
      this.editingUser = user
      this.userForm = {
        name: user.name,
        email: user.email,
        password: '',
        role: user.role || 'order_manager'
      }
      this.showAddModal = true
    },
    closeModal() {
      this.showAddModal = false
      this.editingUser = null
      this.userForm = {
        name: '',
        email: '',
        password: '',
        role: 'order_manager'
      }
    },
    async saveUser() {
      this.saving = true
      this.error = null

      try {
        const payload = {
          name: this.userForm.name,
          email: this.userForm.email,
          role: this.userForm.role
        }

        if (this.userForm.password) {
          payload.password = this.userForm.password
        }

        if (this.editingUser) {
          await axios.put(`/api/admin/users/${this.editingUser.id}`, payload)
        } else {
          await axios.post('/api/admin/users', payload)
        }

        this.closeModal()
        await this.loadUsers()
      } catch (error) {
        console.error('Error saving user:', error)
        this.error = error.response?.data?.message || 'Gabim gjatë ruajtjes së adminit'
        if (error.response?.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat()
          this.error = errors.join(', ')
        }
      } finally {
        this.saving = false
      }
    },
    confirmDelete(user) {
      this.userToDelete = user
    },
    async deleteUser() {
      if (!this.userToDelete) return

      this.deleting = true
      this.error = null

      try {
        await axios.delete(`/api/admin/users/${this.userToDelete.id}`)
        this.userToDelete = null
        await this.loadUsers()
      } catch (error) {
        console.error('Error deleting user:', error)
        this.error = error.response?.data?.message || 'Gabim gjatë fshirjes së adminit'
      } finally {
        this.deleting = false
      }
    },
    formatDate(date) {
      if (!date) return '-'
      return new Date(date).toLocaleString('sq-AL', {
        dateStyle: 'medium',
        timeStyle: 'short'
      })
    },
    // Logout is now handled by AdminLayout
  }
}
</script>

<style scoped>
.btn-primary {
  padding: 0.5rem 1rem;
  background-color: #3b82f6;
  color: white;
  border-radius: 0.375rem;
  transition: background-color 0.2s;
  font-weight: 500;
}

.btn-primary:hover {
  background-color: #2563eb;
}

.btn-secondary {
  padding: 0.5rem 1rem;
  background-color: #e5e7eb;
  color: #1f2937;
  border-radius: 0.375rem;
  transition: background-color 0.2s;
  font-weight: 500;
}

.btn-secondary:hover {
  background-color: #d1d5db;
}
</style>

