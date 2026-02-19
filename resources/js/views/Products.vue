<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
          Produktet Tona
        </h1>
        <p class="text-xl text-gray-600 mb-4">
          Zgjidhni nga një gamë e gjerë produktesh të cilësisë së lartë
        </p>
        <div class="flex flex-col sm:flex-row gap-4 text-sm md:text-[10px] lg:text-[9px] text-gray-600">
          <div class="flex items-center space-x-2">
            <span>📞</span>
            <span>048 75 66 46</span>
          </div>
          <div class="flex items-center space-x-2">
            <span>📞</span>
            <span>044 82 43 14</span>
          </div>
          <div class="flex items-center space-x-2">
            <span>💬</span>
            <span>Viber: +383 48 75 66 46 / +383 44 82 43 14</span>
          </div>
        </div>
        <router-link 
          to="/shporta" 
          class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors"
        >
          Klientët e regjistruar → Plotësoni të dhënat për çmimet tuaja
        </router-link>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar -->
        <div class="lg:w-1/4">
          <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
              Kategoritë
            </h3>
            <div class="space-y-2">
              <button 
                @click="filterByCategory(null)"
                :class="[
                  'w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200',
                  selectedCategory === null 
                    ? 'bg-primary-100 text-primary-700' 
                    : 'text-gray-700 hover:bg-gray-100'
                ]"
              >
                Të gjitha produktet
              </button>
              <button 
                v-for="category in categories" 
                :key="category.id"
                @click="filterByCategory(category.slug)"
                :class="[
                  'w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200',
                  selectedCategory === category.slug 
                    ? 'bg-primary-100 text-primary-700' 
                    : 'text-gray-700 hover:bg-gray-100'
                ]"
              >
                {{ category.name }}
                <span class="text-gray-500 text-xs ml-2">({{ category.products_count }})</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Products Grid -->
        <div class="lg:w-3/4">
          <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="relative">
              <input
                v-model.trim="searchQuery"
                type="search"
                placeholder="Kërko produkt (p.sh. Kese, Letër Kuzhine...)"
                class="w-full px-4 py-3 pl-11 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
              <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                🔍
              </span>
              <button
                v-if="searchQuery"
                @click="searchQuery = ''"
                class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600"
                aria-label="Pastro kërkimin"
              >
                ✖
              </button>
            </div>
            <p class="text-xs text-gray-500 mt-2">
              Kërkimi aplikohet menjëherë mbi produktet e listuara.
            </p>
          </div>
          <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
            <p class="mt-4 text-gray-600">Duke ngarkuar produktet...</p>
          </div>
          
          <div v-else-if="filteredProducts.length === 0" class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">📦</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nuk u gjetën produkte</h3>
            <p class="text-gray-600">Provo një kërkim tjetër ose ndrysho kategorinë.</p>
          </div>
          
          <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <ProductCard 
              v-for="product in filteredProducts" 
              :key="product.id" 
              :product="product"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ProductCard from '../components/ProductCard.vue'
import axios from 'axios'

export default {
  name: 'Products',
  components: {
    ProductCard
  },
  data() {
    return {
      products: [],
      categories: [],
      loading: true,
      selectedCategory: null,
      searchQuery: ''
    }
  },
  computed: {
    filteredProducts() {
      if (!this.searchQuery) {
        return this.products
      }
      const query = this.searchQuery.toLowerCase()
      return this.products.filter(product => {
        return (
          product.name?.toLowerCase().includes(query) ||
          product.description?.toLowerCase().includes(query) ||
          product.category?.name?.toLowerCase().includes(query)
        )
      })
    }
  },
  async mounted() {
    await this.loadCategories()
    await this.loadProducts()
    
    // Check for category filter from URL
    const categoryFromUrl = this.$route.query.kategoria
    if (categoryFromUrl) {
      this.selectedCategory = categoryFromUrl
      await this.loadProducts()
    }
  },
  methods: {
    async loadProducts() {
      this.loading = true
      try {
        const params = {}
        if (this.selectedCategory) {
          params.category = this.selectedCategory
        }
        
        const response = await axios.get('/api/products', { params })
        this.products = response.data.data
      } catch (error) {
        console.error('Error loading products:', error)
      } finally {
        this.loading = false
      }
    },
    async loadCategories() {
      try {
        const response = await axios.get('/api/categories')
        this.categories = response.data.data
      } catch (error) {
        console.error('Error loading categories:', error)
      }
    },
    async filterByCategory(categorySlug) {
      this.selectedCategory = categorySlug
      await this.loadProducts()
      
      // Update URL
      if (categorySlug) {
        this.$router.push({ query: { kategoria: categorySlug } })
      } else {
        this.$router.push({ query: {} })
      }
    }
  }
}
</script>
