<template>
  <div class="min-h-screen bg-gray-50 py-2 sm:py-4 md:py-6 lg:py-8">
    <div class="max-w-7xl mx-auto px-2 sm:px-3 md:px-4 lg:px-6 xl:px-8">
      <div class="mb-4 sm:mb-6 md:mb-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-center sm:gap-3 md:gap-4 mb-3 sm:mb-4">
          <button @click="$router.push('/admin/clients')" class="btn-secondary w-full sm:w-auto text-center text-xs sm:text-sm md:text-base py-2 sm:py-2.5 md:py-2 px-3 sm:px-4">
            ← Kthehu te Klientët
          </button>
          <button 
            @click="logout"
            class="btn-secondary w-full sm:w-auto text-center text-xs sm:text-sm md:text-base py-2 sm:py-2.5 md:py-2 px-3 sm:px-4"
          >
            🚪 Dil
          </button>
        </div>
        <h1 class="text-sm sm:text-base md:text-lg lg:text-xl xl:text-2xl font-bold text-gray-900 mb-2 sm:mb-3 md:mb-4 leading-tight">
          <span class="block sm:inline">Çmimet për Klientin:</span>
          <span class="block sm:inline sm:ml-2 text-primary-600 break-words">{{ clientName }}</span>
        </h1>
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 md:gap-4 items-stretch sm:items-center mb-3 sm:mb-4">
          <button 
            @click="saveAllPrices"
            class="btn-primary w-full sm:w-auto text-xs sm:text-sm md:text-base py-2 sm:py-2.5 md:py-2 px-3 sm:px-4 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="loading"
          >
            {{ loading ? 'Duke ruajtur...' : 'Ruaj të gjitha çmimet' }}
          </button>
        </div>
      </div>

      <!-- Prices Table - Desktop -->
      <div class="hidden md:block bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 md:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produkti</th>
                <th class="px-3 md:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Çmimi Standard</th>
                <th class="px-3 md:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Çmimi për Klientin</th>
                <th class="px-3 md:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lejo shitjen me copa</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="product in products" :key="product.id">
                <td class="px-3 md:px-4 lg:px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                </td>
                <td class="px-3 md:px-4 lg:px-6 py-4 text-sm text-gray-500">
                  {{ product.price ? formatPrice(product.price) : 'Sipas kërkesës' }}
                </td>
                <td class="px-3 md:px-4 lg:px-6 py-4">
                  <input 
                    v-model="priceMap[product.id]" 
                    type="number" 
                    step="0.01" 
                    min="0"
                    placeholder="Çmimi"
                    class="w-full max-w-32 px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  />
                </td>
                <td class="px-3 md:px-4 lg:px-6 py-4">
                  <div class="flex flex-col">
                    <label class="flex items-start cursor-pointer">
                      <input 
                        type="checkbox" 
                        v-model="allowPieceSalesMap[product.id]"
                        class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 mt-0.5 flex-shrink-0"
                      />
                      <div class="ml-2 flex-1 min-w-0">
                        <span class="block text-xs md:text-sm font-medium text-gray-700 mb-1">
                          Lejo shitjen me copa (jo vetëm komplete)
                        </span>
                        <p class="text-xs text-gray-500 hidden md:block">
                          Nëse aktivizohet, ky klient mund të blejë produkte me copa individuale, jo vetëm komplete
                        </p>
                      </div>
                    </label>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Prices Cards - Mobile -->
      <div class="md:hidden space-y-3">
        <div 
          v-for="product in products" 
          :key="product.id"
          class="bg-white rounded-lg shadow-md p-3 border border-gray-200"
        >
          <h3 class="text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-200 break-words">{{ product.name }}</h3>
          
          <div class="space-y-3">
            <div>
              <p class="text-xs text-gray-500 mb-1">Çmimi Standard</p>
              <p class="text-sm font-medium text-gray-700">
                {{ product.price ? formatPrice(product.price) : 'Sipas kërkesës' }}
              </p>
            </div>
            
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1.5">Çmimi për Klientin</label>
              <input 
                v-model="priceMap[product.id]" 
                type="number" 
                step="0.01" 
                min="0"
                placeholder="Shkruani çmimin"
                class="w-full px-3 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              />
            </div>
            
            <div class="pt-3 border-t border-gray-200">
              <label class="flex items-start cursor-pointer">
                <input 
                  type="checkbox" 
                  v-model="allowPieceSalesMap[product.id]"
                  class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 mt-0.5 flex-shrink-0"
                />
                <div class="ml-2.5 flex-1 min-w-0">
                  <span class="block text-sm font-medium text-gray-700 mb-1.5 leading-tight">
                    Lejo shitjen me copa (jo vetëm komplete)
                  </span>
                  <p class="text-xs text-gray-500 leading-relaxed">
                    Nëse aktivizohet, ky klient mund të blejë produkte me copa individuale, jo vetëm komplete
                  </p>
                </div>
              </label>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-if="products.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
        <p class="text-gray-500">Nuk ka produkte për të shfaqur.</p>
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
      allowPieceSalesMap: {},
      loading: false
    }
  },
  async mounted() {
    await this.checkAuth()
    this.clientId = this.$route.params.clientId
    await this.loadClient()
    await this.loadProducts() // Load products first to initialize maps
    await this.loadPrices() // Then load prices to populate maps
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
      } catch (error) {
        console.error('Error loading client:', error)
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
        
        // Initialize maps for all products
        this.products.forEach(product => {
          if (!(product.id in this.priceMap)) {
            this.priceMap[product.id] = null
          }
          if (!(product.id in this.notesMap)) {
            this.notesMap[product.id] = ''
          }
          if (!(product.id in this.allowPieceSalesMap)) {
            this.allowPieceSalesMap[product.id] = false
          }
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
        
        // Initialize maps for all products
        this.products.forEach(product => {
          // Initialize with empty values if not set
          if (!(product.id in this.priceMap)) {
            this.priceMap[product.id] = null
          }
          if (!(product.id in this.notesMap)) {
            this.notesMap[product.id] = ''
          }
          if (!(product.id in this.allowPieceSalesMap)) {
            this.allowPieceSalesMap[product.id] = false
          }
        })
        
        // Populate price map from loaded prices
        this.prices.forEach(price => {
          this.priceMap[price.product_id] = price.price
          this.notesMap[price.product_id] = price.notes || ''
          this.allowPieceSalesMap[price.product_id] = price.allow_piece_sales || false
        })
      } catch (error) {
        console.error('Error loading prices:', error)
      }
    },
    async saveAllPrices() {
      this.loading = true
      try {
        // Save all products that have either a price or allow_piece_sales setting
        const pricesToSave = this.products
          .map(product => {
            const hasPrice = this.priceMap[product.id] && this.priceMap[product.id] > 0
            const hasPieceSales = this.allowPieceSalesMap[product.id] === true
            const hasNotes = this.notesMap[product.id] && this.notesMap[product.id].trim() !== ''
            
            // Include if has price, piece sales setting, or notes
            if (hasPrice || hasPieceSales || hasNotes) {
              return {
                product_id: product.id,
                price: hasPrice ? parseFloat(this.priceMap[product.id]) : null,
                notes: hasNotes ? this.notesMap[product.id].trim() : null,
                allow_piece_sales: this.allowPieceSalesMap[product.id] || false
              }
            }
            return null
          })
          .filter(price => price !== null) // Remove null entries

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

