<template>
  <div>
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary-600 to-primary-800 text-white py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">
          Zgjidhja Juaj për Furnizimet Profesionale
        </h1>
        <p class="text-xl md:text-2xl mb-8 text-primary-100">
          Cilësi e lartë, shërbim i besueshëm dhe produkte të zgjedhura me kujdes për biznesin tuaj
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
          <router-link 
            to="/produktet" 
            class="inline-block bg-white text-primary-600 font-semibold py-3 px-8 rounded-lg hover:bg-gray-100 transition-colors duration-200 text-lg"
          >
            Shiko Produktet
          </router-link>
          <router-link 
            to="/kycu" 
            class="inline-block bg-primary-500/90 text-white font-semibold py-3 px-8 rounded-lg hover:bg-primary-400 border-2 border-white/50 transition-colors duration-200 text-lg"
          >
            Kyçu – Të dhënat e biznesit
          </router-link>
          <router-link 
            to="/shporta" 
            class="inline-block border-2 border-white/70 text-white font-semibold py-3 px-8 rounded-lg hover:bg-white/10 transition-colors duration-200 text-lg"
          >
            Shporta
          </router-link>
        </div>
        <p class="mt-4 text-primary-100 text-sm">
          Keni çmime të caktuara? <router-link to="/kycu" class="underline hover:no-underline">Kyçuni</router-link> me të dhënat e biznesit për të parë çmimet tuaja.
        </p>
        <p class="mt-2 text-primary-200/90 text-xs max-w-xl mx-auto">
          Çmimet për klientët e regjistruar vendosen nga kompania GastroTrade, jo nga klientët.
        </p>
      </div>
    </section>

    <!-- Featured Products Section -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Produktet Tona Kryesore
          </h2>
          <p class="text-xl text-gray-600">
            Zgjidhni nga një gamë e gjerë produktesh të cilësisë së lartë
          </p>
        </div>
        <div class="max-w-3xl mx-auto mb-10">
          <div class="relative">
            <input
              v-model.trim="searchQuery"
              type="search"
              placeholder="Kërko për produkt (p.sh. Pipëza, Letër Kuzhine...)"
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
          <p class="text-sm text-gray-500 mt-2 text-center">
            Kërko menjëherë brenda produkteve kryesore.
          </p>
        </div>
        
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
          <p class="mt-4 text-gray-600">Duke ngarkuar produktet...</p>
        </div>
        
        <div v-else-if="filteredFeaturedProducts.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <ProductCard 
            v-for="product in filteredFeaturedProducts" 
            :key="product.id" 
            :product="product"
          />
        </div>
        <div v-else class="bg-white rounded-xl shadow p-10 text-center text-gray-600">
          <p class="text-2xl mb-2">😕 Nuk u gjet asnjë produkt</p>
          <p>Provoni një term tjetër kërkimi ose hiqni filtrin.</p>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Kategoritë Tona
          </h2>
          <p class="text-xl text-gray-600">
            Organizuar sipas nevojave tuaja
          </p>
        </div>
        
        <div v-if="categoriesLoading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
          <p class="mt-4 text-gray-600">Duke ngarkuar kategoritë...</p>
        </div>
        
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div 
            v-for="category in categories" 
            :key="category.id"
            class="card p-6 text-center hover:scale-105 transition-transform duration-300 cursor-pointer"
            @click="goToCategory(category.slug)"
          >
            <div class="text-primary-600 text-4xl mb-4">
              {{ getCategoryIcon(category.slug) }}
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">
              {{ category.name }}
            </h3>
            <p class="text-gray-600 text-sm">
              {{ category.products_count }} produkte
            </p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import ProductCard from '../components/ProductCard.vue'
import axios from 'axios'

export default {
  name: 'Home',
  components: {
    ProductCard
  },
  data() {
    return {
      featuredProducts: [],
      categories: [],
      loading: true,
      categoriesLoading: true,
      searchQuery: ''
    }
  },
  computed: {
    filteredFeaturedProducts() {
      if (!this.searchQuery) {
        return this.featuredProducts
      }
      const query = this.searchQuery.toLowerCase()
      return this.featuredProducts.filter(product => {
        return (
          product.name?.toLowerCase().includes(query) ||
          product.description?.toLowerCase().includes(query) ||
          product.category?.name?.toLowerCase().includes(query)
        )
      })
    }
  },
  async mounted() {
    await this.loadFeaturedProducts()
    await this.loadCategories()
  },
  methods: {
    async loadFeaturedProducts() {
      try {
        const response = await axios.get('/api/products/featured')
        this.featuredProducts = response.data.data
      } catch (error) {
        console.error('Error loading featured products:', error)
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
      } finally {
        this.categoriesLoading = false
      }
    },
    goToCategory(slug) {
      this.$router.push(`/produktet?kategoria=${slug}`)
    },
    getCategoryIcon(slug) {
      const icons = {
        'pipeza': '🥤',
        'perzieres': '🥄',
        'alumin-flete': '📄',
        'peceta-leter': '🧻',
        'mbeturina': '🗑️',
        'te-lagura': '💧',
        'te-tjera': '📦'
      }
      return icons[slug] || '📦'
    }
  }
}
</script>

