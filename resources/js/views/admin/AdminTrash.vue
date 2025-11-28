<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div class="flex-1">
          <h1 class="text-2xl sm:text-4xl font-bold text-gray-900 mb-2">Historia e Fshirjeve</h1>
          <p class="text-sm text-gray-600">Shiko dhe rikthe artikujt e fshirë</p>
        </div>
        <div class="flex gap-3">
          <router-link
            to="/admin/clients"
            class="btn-secondary text-center"
          >
            ← Kthehu
          </router-link>
          <button 
            @click="logout"
            class="btn-secondary w-full sm:w-auto"
          >
            🚪 Dil
          </button>
        </div>
      </div>

      <!-- Statistics -->
      <div class="mb-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
          <div class="text-center">
            <p class="text-2xl font-bold text-gray-900">{{ counts.orders || 0 }}</p>
            <p class="text-xs text-gray-600 mt-1">Porosi</p>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
          <div class="text-center">
            <p class="text-2xl font-bold text-gray-900">{{ counts.clients || 0 }}</p>
            <p class="text-xs text-gray-600 mt-1">Klientë</p>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
          <div class="text-center">
            <p class="text-2xl font-bold text-gray-900">{{ counts.products || 0 }}</p>
            <p class="text-xs text-gray-600 mt-1">Produkte</p>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
          <div class="text-center">
            <p class="text-2xl font-bold text-gray-900">{{ counts.suppliers || 0 }}</p>
            <p class="text-xs text-gray-600 mt-1">Prodhues</p>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
          <div class="text-center">
            <p class="text-2xl font-bold text-gray-900">{{ counts.supplier_invoices || 0 }}</p>
            <p class="text-xs text-gray-600 mt-1">Fatura</p>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
          <div class="text-center">
            <p class="text-2xl font-bold text-gray-900">{{ counts.stock_receipts || 0 }}</p>
            <p class="text-xs text-gray-600 mt-1">Pranime</p>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex flex-wrap gap-2">
          <button 
            @click="filterType = 'all'"
            :class="filterType === 'all' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200"
          >
            Të Gjitha
          </button>
          <button 
            @click="filterType = 'orders'"
            :class="filterType === 'orders' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200"
          >
            Porosi ({{ counts.orders || 0 }})
          </button>
          <button 
            @click="filterType = 'clients'"
            :class="filterType === 'clients' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200"
          >
            Klientë ({{ counts.clients || 0 }})
          </button>
          <button 
            @click="filterType = 'products'"
            :class="filterType === 'products' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200"
          >
            Produkte ({{ counts.products || 0 }})
          </button>
          <button 
            @click="filterType = 'suppliers'"
            :class="filterType === 'suppliers' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200"
          >
            Prodhues ({{ counts.suppliers || 0 }})
          </button>
          <button 
            @click="filterType = 'supplier_invoices'"
            :class="filterType === 'supplier_invoices' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200"
          >
            Fatura ({{ counts.supplier_invoices || 0 }})
          </button>
          <button 
            @click="filterType = 'stock_receipts'"
            :class="filterType === 'stock_receipts' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200"
          >
            Pranime ({{ counts.stock_receipts || 0 }})
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block h-8 w-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin"></div>
        <p class="mt-4 text-gray-600">Duke ngarkuar...</p>
      </div>

      <!-- Trash Items - Desktop -->
      <div v-else-if="filteredTrash.length > 0" class="hidden md:block bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lloji</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emri</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Përshkrimi</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vlera</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data e Fshirjes</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Veprime</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in filteredTrash" :key="`${item.type}-${item.id}`">
                <td class="px-4 py-4 whitespace-nowrap">
                  <span :class="getTypeBadgeClass(item.type)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getTypeLabel(item.type) }}
                  </span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ item.name }}
                </td>
                <td class="px-4 py-4 text-sm text-gray-500">
                  {{ item.description }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ item.amount ? formatPrice(item.amount) : '-' }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(item.deleted_at) }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm">
                  <button 
                    @click="restoreItem(item)"
                    class="text-green-600 hover:text-green-800 mr-3"
                    title="Rikthe"
                  >
                    ♻️ Rikthe
                  </button>
                  <button 
                    @click="forceDeleteItem(item)"
                    class="text-red-600 hover:text-red-800"
                    title="Fshi Përfundimisht"
                  >
                    🗑️ Fshi
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Trash Items - Mobile -->
      <div v-else-if="filteredTrash.length > 0" class="md:hidden space-y-4">
        <div 
          v-for="item in filteredTrash" 
          :key="`${item.type}-${item.id}`"
          class="bg-white rounded-lg shadow-lg p-4 border border-gray-200"
        >
          <div class="flex justify-between items-start mb-3">
            <div>
              <span :class="getTypeBadgeClass(item.type)" class="px-2 py-1 text-xs font-semibold rounded-full mb-2 inline-block">
                {{ getTypeLabel(item.type) }}
              </span>
              <h3 class="text-base font-semibold text-gray-900">{{ item.name }}</h3>
              <p class="text-xs text-gray-500 mt-1">{{ item.description }}</p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3 mb-3 text-sm">
            <div>
              <p class="text-xs text-gray-500 mb-1">Vlera</p>
              <p class="font-medium text-gray-900">{{ item.amount ? formatPrice(item.amount) : '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 mb-1">Data e Fshirjes</p>
              <p class="font-medium text-gray-900">{{ formatDate(item.deleted_at) }}</p>
            </div>
          </div>
          <div class="flex gap-2 pt-3 border-t border-gray-200">
            <button 
              @click="restoreItem(item)"
              class="flex-1 px-3 py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700"
            >
              ♻️ Rikthe
            </button>
            <button 
              @click="forceDeleteItem(item)"
              class="flex-1 px-3 py-2 text-sm bg-red-600 text-white rounded-md hover:bg-red-700"
            >
              🗑️ Fshi
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12 bg-white rounded-lg shadow">
        <p class="text-gray-500">Nuk ka artikuj të fshirë.</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'AdminTrash',
  data() {
    return {
      trash: [],
      counts: {},
      filterType: 'all',
      loading: false
    }
  },
  computed: {
    filteredTrash() {
      if (this.filterType === 'all') {
        return this.trash
      }
      return this.trash.filter(item => item.type === this.filterType)
    }
  },
  async mounted() {
    await this.checkAuth()
    await this.loadTrash()
  },
  methods: {
    async checkAuth() {
      try {
        const token = localStorage.getItem('admin_token')
        if (!token) {
          this.$router.push('/admin/login')
          return
        }
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
        await axios.get('/api/admin/check')
      } catch (error) {
        localStorage.removeItem('admin_token')
        delete axios.defaults.headers.common['Authorization']
        this.$router.push('/admin/login')
      }
    },
    async logout() {
      try {
        await axios.post('/api/admin/logout')
      } catch (error) {
        // Continue with logout even if API call fails
      }
      localStorage.removeItem('admin_token')
      delete axios.defaults.headers.common['Authorization']
      this.$router.push('/admin/login')
    },
    async loadTrash() {
      this.loading = true
      try {
        const response = await axios.get('/api/trash', {
          params: { type: this.filterType }
        })
        if (response.data.success) {
          this.trash = response.data.data
          this.counts = response.data.counts || {}
        }
      } catch (error) {
        console.error('Error loading trash:', error)
        alert('Gabim në ngarkimin e historisë së fshirjeve')
      } finally {
        this.loading = false
      }
    },
    async restoreItem(item) {
      if (!confirm(`A jeni të sigurt që dëshironi të riktheni "${item.name}"?`)) {
        return
      }

      try {
        const response = await axios.post(`/api/trash/${item.type}/${item.id}/restore`)
        if (response.data.success) {
          alert('Artikulli u rikthye me sukses!')
          await this.loadTrash()
        } else {
          alert(response.data.message || 'Gabim në rikthimin e artikullit')
        }
      } catch (error) {
        console.error('Error restoring item:', error)
        const errorMessage = error.response?.data?.message || 'Gabim në rikthimin e artikullit'
        alert(errorMessage)
      }
    },
    async forceDeleteItem(item) {
      if (!confirm(`A jeni të sigurt që dëshironi të fshini përfundimisht "${item.name}"? Kjo veprim nuk mund të zhbëhet!`)) {
        return
      }

      try {
        const response = await axios.delete(`/api/trash/${item.type}/${item.id}/force`)
        if (response.data.success) {
          alert('Artikulli u fshi përfundimisht!')
          await this.loadTrash()
        } else {
          alert(response.data.message || 'Gabim në fshirjen e artikullit')
        }
      } catch (error) {
        console.error('Error force deleting item:', error)
        const errorMessage = error.response?.data?.message || 'Gabim në fshirjen e artikullit'
        alert(errorMessage)
      }
    },
    getTypeLabel(type) {
      const labels = {
        'order': 'Porosi',
        'client': 'Klient',
        'product': 'Produkt',
        'supplier': 'Prodhues',
        'supplier_invoice': 'Faturë',
        'stock_receipt': 'Pranim'
      }
      return labels[type] || type
    },
    getTypeBadgeClass(type) {
      const classes = {
        'order': 'bg-blue-100 text-blue-800',
        'client': 'bg-purple-100 text-purple-800',
        'product': 'bg-green-100 text-green-800',
        'supplier': 'bg-yellow-100 text-yellow-800',
        'supplier_invoice': 'bg-orange-100 text-orange-800',
        'stock_receipt': 'bg-indigo-100 text-indigo-800'
      }
      return classes[type] || 'bg-gray-100 text-gray-800'
    },
    formatPrice(price) {
      if (!price) return '€0.00'
      return new Intl.NumberFormat('sq-AL', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
      }).format(price)
    },
    formatDate(date) {
      if (!date) return '-'
      return new Date(date).toLocaleDateString('sq-AL', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
  },
  watch: {
    filterType() {
      this.loadTrash()
    }
  }
}
</script>

<style scoped>
.btn-primary {
  @apply bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors duration-200 font-medium;
}

.btn-secondary {
  @apply bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors duration-200 font-medium;
}
</style>

