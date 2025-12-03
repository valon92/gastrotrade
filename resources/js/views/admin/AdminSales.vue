<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div class="flex-1">
          <h1 class="text-2xl sm:text-4xl font-bold text-gray-900 mb-4">Shitjet</h1>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
          <router-link
            to="/admin/clients"
            class="btn-secondary text-center"
          >
            👥 Klientët
          </router-link>
          <router-link
            to="/admin/stock"
            class="btn-secondary text-center"
          >
            📊 Stoku
          </router-link>
          <router-link
            to="/admin/trash"
            class="btn-secondary text-center"
          >
            🗑️ Historia e Fshirjeve
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
      <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 border border-green-200 shadow-sm">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-green-800">Të Paguara</h3>
            <span class="text-2xl">✅</span>
          </div>
          <p class="text-3xl font-bold text-green-900">{{ stats.paidCount }}</p>
          <p class="text-sm text-green-700 mt-1">{{ formatPrice(stats.paidTotal) }}</p>
        </div>
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-5 border border-red-200 shadow-sm">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-red-800">Të Papaguara</h3>
            <span class="text-2xl">⚠️</span>
          </div>
          <p class="text-3xl font-bold text-red-900">{{ stats.unpaidCount }}</p>
          <p class="text-sm text-red-700 mt-1">{{ formatPrice(stats.unpaidTotal) }}</p>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200 shadow-sm">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-blue-800">Total Shitje</h3>
            <span class="text-2xl">💰</span>
          </div>
          <p class="text-3xl font-bold text-blue-900">{{ stats.totalCount }}</p>
          <p class="text-sm text-blue-700 mt-1">{{ formatPrice(stats.totalAmount) }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 border border-purple-200 shadow-sm">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-purple-800">Vlera Mesatare</h3>
            <span class="text-2xl">📊</span>
          </div>
          <p class="text-3xl font-bold text-purple-900">{{ stats.totalCount > 0 ? formatPrice(stats.totalAmount / stats.totalCount) : '€0.00' }}</p>
          <p class="text-sm text-purple-700 mt-1">Për porosi</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Kërko</label>
          <input 
            v-model.trim="filters.search"
            type="text"
            placeholder="Nr. porosie, klient..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            @input="debounceSearch"
          >
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Statusi</label>
          <select 
            v-model="filters.status"
            @change="loadSales"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option value="">Të Gjitha</option>
            <option value="ruajtur">Ruajtur</option>
            <option value="konfirmuar">Konfirmuar</option>
            <option value="dërguar">Dërguar</option>
            <option value="kompletuar">Kompletuar</option>
            <option value="anuluar">Anuluar</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Statusi i Pagesës</label>
          <select 
            v-model="filters.payment"
            @change="loadSales"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option value="">Të Gjitha</option>
            <option value="paid">Të Paguara</option>
            <option value="unpaid">Të Papaguara</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Data</label>
          <input 
            v-model="filters.date_from"
            type="date"
            @change="loadSales"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>
      </div>

      <!-- Sales Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nr. Porosie</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klienti</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produkte</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statusi</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pagesa</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Totali</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Veprime</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="loading" class="text-center">
                <td colspan="8" class="px-4 py-8 text-gray-500">Duke ngarkuar...</td>
              </tr>
              <tr v-else-if="sales.length === 0" class="text-center">
                <td colspan="8" class="px-4 py-8 text-gray-500">Nuk u gjetën shitje</td>
              </tr>
              <tr v-else v-for="sale in sales" :key="sale.id" class="hover:bg-gray-50">
                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ sale.order_number }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                  <div>
                    <div class="font-medium">{{ sale.customer_name || sale.client?.name || '-' }}</div>
                    <div class="text-xs text-gray-500">{{ sale.business_name || sale.client?.store_name || '' }}</div>
                  </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                  {{ formatDate(sale.created_at) }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                  {{ sale.total_items || (sale.items ? sale.items.length : 0) }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <span 
                    :class="[
                      'px-2 py-1 text-xs font-semibold rounded-full',
                      sale.status === 'kompletuar' ? 'bg-green-100 text-green-800' :
                      sale.status === 'dërguar' ? 'bg-blue-100 text-blue-800' :
                      sale.status === 'konfirmuar' ? 'bg-yellow-100 text-yellow-800' :
                      sale.status === 'anuluar' ? 'bg-red-100 text-red-800' :
                      'bg-gray-100 text-gray-800'
                    ]"
                  >
                    {{ getStatusLabel(sale.status) }}
                  </span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <span 
                    :class="[
                      'px-2 py-1 text-xs font-semibold rounded-full',
                      sale.is_paid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                    ]"
                  >
                    {{ sale.is_paid ? '✅ E Paguar' : '⚠️ Jo e Paguar' }}
                  </span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                  {{ formatPrice(sale.total_amount) }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click="viewSale(sale)"
                    class="text-primary-600 hover:text-primary-900 mr-3"
                    title="Shiko Detajet"
                  >
                    👁️
                  </button>
                  <button
                    @click="printSale(sale)"
                    class="text-gray-600 hover:text-gray-900"
                    title="Printo"
                  >
                    🖨️
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="!loading && sales.length > 0" class="mt-6 flex justify-center">
        <div class="flex gap-2">
          <button
            @click="loadSales(page - 1)"
            :disabled="page === 1"
            class="px-4 py-2 border border-gray-300 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
          >
            Para
          </button>
          <span class="px-4 py-2 text-sm text-gray-700">
            Faqja {{ page }} nga {{ totalPages }}
          </span>
          <button
            @click="loadSales(page + 1)"
            :disabled="page >= totalPages"
            class="px-4 py-2 border border-gray-300 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
          >
            Pas
          </button>
        </div>
      </div>
    </div>

    <!-- Sale Details Modal -->
    <div v-if="showSaleModal && selectedSale" class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-0 sm:top-4 mx-auto max-w-4xl w-full p-3 sm:p-6">
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 sm:px-6 py-4 border-b border-gray-200 gap-3">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900">Detajet e Shitjes</h3>
            <button @click="closeSaleModal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none self-start sm:self-auto">
              &times;
            </button>
          </div>
          <div class="px-4 sm:px-6 py-4 sm:py-5 max-h-[90vh] sm:max-h-[80vh] overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
              <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Nr. Porosie</p>
                <p class="text-lg font-semibold text-gray-900">{{ selectedSale.order_number }}</p>
              </div>
              <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Data</p>
                <p class="text-lg font-semibold text-gray-900">{{ formatDate(selectedSale.created_at) }}</p>
              </div>
              <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Klienti</p>
                <p class="text-lg font-semibold text-gray-900">{{ selectedSale.customer_name || selectedSale.client?.name || '-' }}</p>
                <p class="text-sm text-gray-600">{{ selectedSale.business_name || selectedSale.client?.store_name || '' }}</p>
              </div>
              <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Statusi</p>
                <span 
                  :class="[
                    'px-2 py-1 text-xs font-semibold rounded-full',
                    selectedSale.status === 'kompletuar' ? 'bg-green-100 text-green-800' :
                    selectedSale.status === 'dërguar' ? 'bg-blue-100 text-blue-800' :
                    selectedSale.status === 'konfirmuar' ? 'bg-yellow-100 text-yellow-800' :
                    selectedSale.status === 'anuluar' ? 'bg-red-100 text-red-800' :
                    'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ getStatusLabel(selectedSale.status) }}
                </span>
              </div>
            </div>

            <!-- Items -->
            <div class="mb-6">
              <h4 class="text-lg font-semibold text-gray-900 mb-4">Produktet</h4>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produkti</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sasia</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Çmimi Njësi</th>
                      <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Totali</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="item in (selectedSale.items || [])" :key="item.id">
                      <td class="px-4 py-3 text-sm text-gray-900">
                        {{ item.product_name }}
                        <span v-if="item.sold_by_package && item.pieces_per_package" class="text-xs text-gray-500">
                          ({{ item.pieces_per_package }}cp/pako)
                        </span>
                      </td>
                      <td class="px-4 py-3 text-sm text-gray-700">
                        <template v-if="item.unit_type === 'package' && item.sold_by_package && item.pieces_per_package">
                          {{ parseFloat(item.quantity).toFixed(0) }} pako = {{ (item.quantity * item.pieces_per_package).toFixed(0) }} copa
                        </template>
                        <template v-else>
                          {{ parseFloat(item.quantity).toFixed(0) }} copa
                        </template>
                      </td>
                      <td class="px-4 py-3 text-sm text-gray-700">
                        {{ formatPrice(item.unit_price) }}
                      </td>
                      <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">
                        {{ formatPrice(item.total_price) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Summary -->
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-sm font-medium text-gray-700">Nëntotali:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ formatPrice(selectedSale.subtotal || selectedSale.total_amount) }}</span>
                </div>
                <div v-if="selectedSale.discount_amount > 0" class="flex justify-between">
                  <span class="text-sm font-medium text-gray-700">Zbritje:</span>
                  <span class="text-sm font-semibold text-red-600">-{{ formatPrice(selectedSale.discount_amount) }}</span>
                </div>
                <div v-if="selectedSale.has_vat" class="flex justify-between">
                  <span class="text-sm font-medium text-gray-700">TVSH:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ formatPrice(selectedSale.vat_amount || 0) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t-2 border-gray-300">
                  <span class="text-lg font-bold text-gray-900">Totali:</span>
                  <span class="text-lg font-bold text-primary-600">{{ formatPrice(selectedSale.total_amount) }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50 flex flex-col sm:flex-row justify-end gap-3">
            <button 
              @click="closeSaleModal"
              class="px-4 sm:px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
            >
              Mbyll
            </button>
            <button 
              @click="printSale(selectedSale)"
              class="px-4 sm:px-6 py-2.5 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 transition-colors"
            >
              🖨️ Printo
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'AdminSales',
  data() {
    return {
      sales: [],
      loading: false,
      stats: {
        paidCount: 0,
        unpaidCount: 0,
        totalCount: 0,
        paidTotal: 0,
        unpaidTotal: 0,
        totalAmount: 0
      },
      filters: {
        search: '',
        status: '',
        payment: '',
        date_from: ''
      },
      page: 1,
      perPage: 50,
      totalPages: 1,
      showSaleModal: false,
      selectedSale: null,
      searchTimeout: null
    }
  },
  mounted() {
    this.setupAxiosInterceptor()
    this.loadSales()
    this.loadStats()
  },
  methods: {
    setupAxiosInterceptor() {
      axios.interceptors.request.use(config => {
        const adminToken = localStorage.getItem('admin_token')
        if (adminToken) {
          config.headers.Authorization = `Bearer ${adminToken}`
        }
        return config
      })
    },
    async loadSales(page = 1) {
      this.loading = true
      this.page = page
      try {
        const params = {
          page: this.page,
          per_page: this.perPage
        }
        
        if (this.filters.search) {
          params.search = this.filters.search
        }
        if (this.filters.status) {
          params.status = this.filters.status
        }
        if (this.filters.payment) {
          params.is_paid = this.filters.payment === 'paid' ? 1 : 0
        }
        if (this.filters.date_from) {
          params.date_from = this.filters.date_from
        }

        const response = await axios.get('/api/sales', { params })
        this.sales = response.data.data || []
        this.totalPages = response.data.last_page || 1
      } catch (error) {
        console.error('Error loading sales:', error)
        alert('Gabim gjatë ngarkimit të shitjeve')
      } finally {
        this.loading = false
      }
    },
    async loadStats() {
      try {
        const response = await axios.get('/api/orders/payment-stats')
        if (response.data.success) {
          this.stats = response.data.data
        }
      } catch (error) {
        console.error('Error loading stats:', error)
      }
    },
    debounceSearch() {
      clearTimeout(this.searchTimeout)
      this.searchTimeout = setTimeout(() => {
        this.loadSales(1)
      }, 500)
    },
    viewSale(sale) {
      this.selectedSale = sale
      this.showSaleModal = true
    },
    closeSaleModal() {
      this.showSaleModal = false
      this.selectedSale = null
    },
    printSale(sale) {
      window.print()
    },
    getStatusLabel(status) {
      const labels = {
        'ruajtur': 'Ruajtur',
        'konfirmuar': 'Konfirmuar',
        'dërguar': 'Dërguar',
        'kompletuar': 'Kompletuar',
        'anuluar': 'Anuluar'
      }
      return labels[status] || status
    },
    formatPrice(price) {
      if (price === null || price === undefined) {
        return '€0.00'
      }
      return new Intl.NumberFormat('sq-AL', {
        style: 'currency',
        currency: 'EUR'
      }).format(price)
    },
    formatDate(date) {
      if (!date) return '-'
      return new Date(date).toLocaleString('sq-AL', {
        dateStyle: 'medium',
        timeStyle: 'short'
      })
    },
    logout() {
      localStorage.removeItem('admin_token')
      this.$router.push('/admin/login')
    }
  }
}
</script>

<style scoped>
.btn-primary {
  @apply px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors font-medium;
}

.btn-secondary {
  @apply px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors font-medium;
}
</style>

