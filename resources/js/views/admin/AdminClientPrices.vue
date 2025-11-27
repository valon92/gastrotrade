<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
          <button @click="$router.push('/admin/clients')" class="btn-secondary">
            ← Kthehu te Klientët
          </button>
          <button 
            @click="logout"
            class="btn-secondary"
          >
            🚪 Dil
          </button>
        </div>
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Çmimet për Klientin: {{ clientName }}</h1>
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
          <div class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <label class="flex items-center cursor-pointer">
              <input 
                type="checkbox" 
                v-model="allowPieceSales"
                @change="saveAllowPieceSales"
                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              />
              <span class="ml-2 text-sm font-medium text-gray-700">
                Lejo shitjen me copa (jo vetëm komplete)
              </span>
            </label>
            <span class="text-xs text-gray-500">
              Nëse aktivizohet, ky klient mund të blejë produkte me copa individuale, jo vetëm komplete
            </span>
          </div>
          <button 
            @click="saveAllPrices"
            class="btn-primary"
            :disabled="loading"
          >
            {{ loading ? 'Duke ruajtur...' : 'Ruaj të gjitha çmimet' }}
          </button>
        </div>
      </div>

      <!-- Prices Table -->
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produkti</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Çmimi Standard</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Çmimi për Klientin</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shënime</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="product in products" :key="product.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ product.price ? formatPrice(product.price) : 'Sipas kërkesës' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <input 
                  v-model="priceMap[product.id]" 
                  type="number" 
                  step="0.01" 
                  min="0"
                  placeholder="Shkruani çmimin"
                  class="w-32 px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm"
                />
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <input 
                  v-model="notesMap[product.id]" 
                  type="text" 
                  placeholder="Shënime"
                  class="w-48 px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'AdminClientPrices',
  data() {
    return {
      clientId: null,
      clientName: '',
      products: [],
      prices: [],
      priceMap: {},
      notesMap: {},
      loading: false,
      allowPieceSales: false,
      savingPieceSales: false
    }
  },
  async mounted() {
    await this.checkAuth()
    this.clientId = this.$route.params.clientId
    await Promise.all([
      this.loadClient(),
      this.loadProducts(),
      this.loadPrices()
    ])
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
    async loadClient() {
      try {
        const response = await axios.get(`/api/clients/${this.clientId}`)
        const client = response.data.data
        this.clientName = client.name
        this.allowPieceSales = client.allow_piece_sales || false
      } catch (error) {
        console.error('Error loading client:', error)
      }
    },
    async saveAllowPieceSales() {
      this.savingPieceSales = true
      try {
        await axios.put(`/api/clients/${this.clientId}`, {
          allow_piece_sales: this.allowPieceSales
        })
      } catch (error) {
        console.error('Error saving allow_piece_sales:', error)
        alert('Gabim në ruajtjen e opsionit')
        // Revert on error
        this.allowPieceSales = !this.allowPieceSales
      } finally {
        this.savingPieceSales = false
      }
    },
    async loadProducts() {
      try {
        const response = await axios.get('/api/products')
        const customOrder = {
          // Kese Mbeturinash (pa lidhëse)
          'kese-mbeturinash-40l': 1,
          'kese-mbeturinash-70l': 2,
          'kese-mbeturinash-120l': 3,
          'kese-mbeturinash-150l': 4,
          'kese-mbeturinash-170l': 5,
          'kese-mbeturinash-200l': 6,
          'kese-mbeturinash-240l': 7,
          'kese-mbeturinash-270l': 8,
          'kese-mbeturinash-300l': 9,
          // Kese Mbeturinash me lidhëse
          'kese-mbeturinash-70l-me-lidhse': 10,
          'kese-mbeturinash-120l-me-lidhse': 11,
          'kese-mbeturinash-200l-me-lidhse': 12,
          'kese-mbeturinash-220l-me-lidhse': 13,
          // Pipëza 100Cp
          'pipeza-transparente-per-pije-100cp-kompleti-20cp': 14,
          'pipeza-te-zi-per-pije-100cp-kompleti-20cp': 15,
          'pipeza-color-per-pije-100cp-kompleti-20cp': 16,
          // Pipëza 200Cp
          'pipeza-transparente-per-pije-200cp-kompleti-20cp': 17,
          'pipeza-te-zi-per-pije-200cp-kompleti-20cp': 18,
          'pipeza-color-per-pije-200cp-kompleti-20cp': 19,
          // Pipëza 500Cp
          'pipeza-te-zi-per-pije-500cp-kompleti-10cp': 20,
          'pipeza-color-per-pije-500cp-kompleti-10cp': 21,
          'pipeza-te-zi-per-pije-300cp-kompleti-10cp': 22, // fallback in case legacy slugs remain
          'pipeza-color-per-pije-300cp-kompleti-10cp': 23
        }
        this.products = (response.data.data || []).slice().sort((a, b) => {
          const orderA = customOrder[a.slug] ?? Number.MAX_SAFE_INTEGER
          const orderB = customOrder[b.slug] ?? Number.MAX_SAFE_INTEGER

          if (orderA !== orderB) {
            return orderA - orderB
          }

          const nameA = a?.name ?? ''
          const nameB = b?.name ?? ''
          return nameA.localeCompare(nameB, 'sq', { sensitivity: 'base' })
        })
      } catch (error) {
        console.error('Error loading products:', error)
        alert('Gabim në ngarkimin e produkteve')
      }
    },
    async loadPrices() {
      try {
        const response = await axios.get('/api/client-prices', {
          params: { client_id: this.clientId }
        })
        this.prices = response.data.data
        
        // Populate price map
        this.prices.forEach(price => {
          this.priceMap[price.product_id] = price.price
          this.notesMap[price.product_id] = price.notes || ''
        })
      } catch (error) {
        console.error('Error loading prices:', error)
      }
    },
    async saveAllPrices() {
      this.loading = true
      try {
        const pricesToSave = this.products
          .filter(product => this.priceMap[product.id] && this.priceMap[product.id] > 0)
          .map(product => ({
            product_id: product.id,
            price: parseFloat(this.priceMap[product.id]),
            notes: this.notesMap[product.id] || null
          }))

        await axios.post('/api/client-prices/bulk-update', {
          client_id: this.clientId,
          prices: pricesToSave
        })

        alert('Çmimet u ruajtën me sukses!')
        await this.loadPrices()
      } catch (error) {
        console.error('Error saving prices:', error)
        alert('Gabim në ruajtjen e çmimeve')
      } finally {
        this.loading = false
      }
    },
    formatPrice(price) {
      return new Intl.NumberFormat('sq-AL', {
        style: 'currency',
        currency: 'EUR'
      }).format(price)
    }
  }
}
</script>

